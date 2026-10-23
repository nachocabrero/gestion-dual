<?php

namespace App\Http\Controllers;

use App\Models\Anotacion;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de anotaciones/tutorías.
 * Acceso: Tutor del grupo, Coordinador Dual, Admin.
 */
class AnotacionController extends Controller
{
    /**
     * Listar anotaciones (según rol).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Solo roles autorizados
        abort_unless($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL, User::ROLE_PROFESOR]), 403);

        $query = Anotacion::with(['alumno.user', 'alumno.grupos', 'profesor.user']);

        if ($user->hasRole(User::ROLE_PROFESOR)) {
            // Profesor ve sus anotaciones + las de alumnos de los grupos que imparte
            $query->visiblesPara($user->profesor?->id);
        } elseif ($user->hasRole(User::ROLE_COORDINADOR_DUAL)) {
            // Coordinador ve todas
        } else {
            // Admin ve todas
        }

        if ($request->filled('alumno')) {
            $query->whereHas('alumno.user', fn($q) => $q->where('name', 'like', '%' . $request->alumno . '%'));
        }

        $anotaciones = $query->paginate(50);

        return view('anotaciones.index', compact('anotaciones'));
    }

    /**
     * Obtener grupos disponibles para el usuario según su rol.
     */
    protected function getGruposPermitidos(User $user)
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
            return Grupo::with(['linea.ciclo', 'cursoAcademico'])
                ->where('is_active', true)
                ->orderBy('nombre')
                ->get();
        }

        $profesorId = $user->profesor?->id;
        if (!$profesorId && !$user->hasRole(User::ROLE_PROFESOR)) {
            return collect();
        }

        return Grupo::with(['linea.ciclo', 'cursoAcademico'])
            ->where('is_active', true)
            ->where(function ($q) use ($profesorId, $user) {
                if ($profesorId) {
                    $q->whereHas('profesores', fn($p) => $p->where('profesores.id', $profesorId));
                }
                $q->orWhere('tutor_id', $user->id);
            })
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Formulario crear anotación / tabla de grupo.
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user->can('create-anotacion'), 403);

        $grupos = $this->getGruposPermitidos($user);
        $selectedGrupoId = null;

        if ($request->filled('grupo_id')) {
            $grupoCandidate = $grupos->firstWhere('id', (int) $request->grupo_id);
            if ($grupoCandidate) {
                $selectedGrupoId = $grupoCandidate->id;
            }
        }

        if (!$selectedGrupoId && $request->filled('alumno')) {
            $alumnoReq = Alumno::with('grupos')->find($request->alumno);
            if ($alumnoReq && $alumnoReq->grupos->isNotEmpty()) {
                $grupoAlumno = $grupos->firstWhere('id', $alumnoReq->grupos->first()->id);
                if ($grupoAlumno) {
                    $selectedGrupoId = $grupoAlumno->id;
                }
            }
        }

        if (!$selectedGrupoId && $grupos->isNotEmpty()) {
            $selectedGrupoId = $grupos->first()->id;
        }

        $selectedGrupo = $selectedGrupoId ? $grupos->firstWhere('id', $selectedGrupoId) : null;
        $alumnos = collect();
        $profesorId = $user->profesor?->id;

        if ($selectedGrupo) {
            $alumnos = $selectedGrupo->alumnos()
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->with(['user'])
                ->get()
                ->sortBy('user.name')
                ->values();
        }

        // Cargar las anotaciones más recientes de este profesor para los alumnos del grupo
        $anotacionesExistentes = collect();
        if ($profesorId && $alumnos->isNotEmpty()) {
            $anotacionesExistentes = Anotacion::where('profesor_id', $profesorId)
                ->whereIn('alumno_id', $alumnos->pluck('id'))
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('alumno_id')
                ->map(fn($group) => $group->first());
        }

        // Estructura de datos para Alpine.js con ordenación dinámica según puesto
        $alumnosData = $alumnos->map(function ($alumno, $index) use ($anotacionesExistentes) {
            $prev = $anotacionesExistentes->get($alumno->id);
            return [
                'id' => $alumno->id,
                'name' => $alumno->user->name,
                'email' => $alumno->user->email,
                'avatar_url' => $alumno->user->avatar_url ? asset('storage/' . $alumno->user->avatar_url) : null,
                'initial_index' => $index,
                'anotacion_id' => $prev?->id ?? null,
                'puesto' => $prev?->puesto ?? '',
                'titulo' => $prev?->titulo ?? '',
                'contenido' => $prev?->contenido ?? '',
            ];
        })->values();

        $alumnoId = $request->query('alumno');

        return view('anotaciones.create', compact(
            'grupos',
            'selectedGrupo',
            'alumnos',
            'alumnosData',
            'alumnoId'
        ));
    }

    /**
     * Guardar anotación (individual o en lote por tabla de grupo).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->can('create-anotacion'), 403);

        $profesor = $user->profesor;
        $gruposPermitidos = $this->getGruposPermitidos($user);
        $alumnoIdsPermitidos = Alumno::whereHas('grupos', function ($q) use ($gruposPermitidos) {
            $q->whereIn('grupos.id', $gruposPermitidos->pluck('id'));
        })->pluck('id')->toArray();

        // 1. Guardado en lote desde la tabla del grupo
        if ($request->has('anotaciones') && is_array($request->anotaciones)) {
            $request->validate([
                'grupo_id' => ['required', 'exists:grupos,id'],
                'anotaciones' => ['required', 'array'],
                'anotaciones.*.alumno_id' => ['required', 'exists:alumnos,id'],
                'anotaciones.*.titulo' => ['nullable', 'string', 'max:255'],
                'anotaciones.*.contenido' => ['nullable', 'string', 'max:2000'],
                'anotaciones.*.puesto' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            if (!$user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
                abort_unless($gruposPermitidos->contains('id', (int) $request->grupo_id), 403, 'No tienes permiso para anotar en este grupo.');
            }

            $guardadas = 0;
            foreach ($request->anotaciones as $data) {
                $alumnoId = (int) $data['alumno_id'];

                if (!$user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
                    if (!in_array($alumnoId, $alumnoIdsPermitidos)) {
                        continue;
                    }
                }

                $titulo = trim($data['titulo'] ?? '');
                $contenido = trim($data['contenido'] ?? '');
                $puesto = !empty($data['puesto']) ? (int) $data['puesto'] : null;

                if ($titulo !== '' || $contenido !== '' || !is_null($puesto)) {
                    if ($titulo === '') {
                        $titulo = 'Observación de clase';
                    }
                    if ($contenido === '') {
                        $contenido = !is_null($puesto) ? "Puesto asignado: #{$puesto}" : 'Sin observaciones adicionales';
                    }

                    if (!empty($data['anotacion_id']) && $profesor) {
                        $anotacion = Anotacion::where('id', $data['anotacion_id'])
                            ->where('profesor_id', $profesor->id)
                            ->first();
                        if ($anotacion) {
                            $anotacion->update([
                                'titulo' => $titulo,
                                'contenido' => $contenido,
                                'puesto' => $puesto,
                            ]);
                            $guardadas++;
                            continue;
                        }
                    }

                    Anotacion::create([
                        'alumno_id' => $alumnoId,
                        'profesor_id' => $profesor ? $profesor->id : null,
                        'titulo' => $titulo,
                        'contenido' => $contenido,
                        'puesto' => $puesto,
                    ]);
                    $guardadas++;
                }
            }

            return redirect()->route('anotaciones.create', ['grupo_id' => $request->grupo_id])
                ->with('success', $guardadas > 0
                    ? "Se han guardado {$guardadas} anotación(es) para el grupo correctamente."
                    : "No se han introducido datos para guardar.");
        }

        // 2. Guardado individual (compatibilidad y llamadas puntuales)
        $validated = $request->validate([
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'contenido' => ['required', 'string', 'max:2000'],
            'puesto' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if (!$user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
            abort_unless(in_array((int) $validated['alumno_id'], $alumnoIdsPermitidos), 403, 'No tienes permiso para anotar a este alumno.');
        }

        Anotacion::create([
            'alumno_id' => $validated['alumno_id'],
            'profesor_id' => $profesor ? $profesor->id : null,
            'titulo' => $validated['titulo'],
            'contenido' => $validated['contenido'],
            'puesto' => $validated['puesto'] ?? null,
        ]);

        return redirect()->route('anotaciones.index')
            ->with('success', 'Anotación creada correctamente.');
    }

    /**
     * Formulario editar.
     */
    public function edit(Anotacion $anotacion): View
    {
        return view('anotaciones.edit', compact('anotacion'));
    }

    /**
     * Actualizar anotación.
     */
    public function update(Request $request, Anotacion $anotacion): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'contenido' => ['required', 'string', 'max:2000'],
            'puesto' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $anotacion->update($validated);

        return redirect()->route('anotaciones.show', $anotacion->alumno_id)
            ->with('success', 'Anotación actualizada.');
    }

    /**
     * Eliminar anotación.
     */
    public function destroy(Anotacion $anotacion): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $anotacion->delete();
        return back()->with('success', 'Anotación eliminada.');
    }

    /**
     * Ver anotaciones de un alumno.
     */
    public function show(int $alumnoId): View
    {
        $alumno = Alumno::with('user', 'grupos')->findOrFail($alumnoId);
        $user = auth()->user();

        // Verificar permisos (Admin, Coordinador Dual o equipo educativo del alumno: tutores y profesores que imparten a sus grupos)
        if ($user->hasRole(User::ROLE_PROFESOR) && !$user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
            $alumnoGrupoIds = $alumno->grupos->pluck('id');
            $tutorGrupos = \App\Models\Grupo::where('tutor_id', $user->id)->pluck('id');
            $impartidosGroupIds = $user->profesor ? $user->profesor->gruposImpartidos()->pluck('grupos.id') : collect();
            $allTeacherGroups = $tutorGrupos->merge($impartidosGroupIds)->unique();
            $esEquipoEducativo = $alumnoGrupoIds->intersect($allTeacherGroups)->isNotEmpty();
            abort_unless($esEquipoEducativo, 403);
        } elseif (!$user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
            abort(403);
        }

        $anotaciones = Anotacion::paraAlumno($alumnoId)->get();

        return view('anotaciones.show', compact('alumno', 'anotaciones'));
    }
}