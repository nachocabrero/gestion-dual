<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Practica;
use App\Models\Profesor;
use App\Models\Sustitucion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de gestión de profesores.
 * Acceso: Admin, Coordinador Dual.
 */
class ProfesorController extends Controller
{
    /**
     * Listar todos los profesores.
     */
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL, User::ROLE_PROFESOR]), 403);

        $query = Profesor::with(['user', 'gruposTutor', 'familia'])
            ->join('users', 'profesores.user_id', '=', 'users.id')
            ->select('profesores.*')
            ->orderBy('users.name', 'asc');

        if ($request->filled('search')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('users.name', 'like', '%' . $request->search . '%')
                    ->orWhere('users.email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('es_tutor')) {
            $query->tutores();
        }

        if ($request->filled('es_coordinador')) {
            $query->coordinadores();
        }

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50])) {
            $perPage = 15;
        }

        $profesores = $query->paginate($perPage)->withQueryString();

        return view('profesores.index', compact('profesores'));
    }

    /**
     * Mostrar perfil de un profesor.
     */
    public function show(Profesor $profesor): View
    {
        $profesor->load([
            'user',
            'familia',
            'gruposTutor.linea.ciclo',
            'gruposImpartidos',
            'asignaturas',
            'sustituciones',
            'empresas',
        ]);

        $empresas = $profesor->empresas()->orderBy('nombre')->get();

        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
        if (!$cursoActual) {
            $cursoActual = CursoAcademico::orderBy('fecha_inicio', 'desc')->first();
        }

        // Prácticas donde el profesor es tutor docente (tutor de prácticas del alumno o tutor docente en convenio)
        $todasPracticas = Practica::query()
            ->where(function ($query) use ($profesor) {
                $query->whereHas('alumno', function ($q) use ($profesor) {
                    $q->where('tutor_practicas_id', $profesor->user_id);
                })->orWhereHas('alumno.convenios', function ($q) use ($profesor) {
                    $q->where('tutor_docente_id', $profesor->id);
                });
            })
            ->with(['alumno.user', 'empresa', 'tutorLaboral', 'cursoAcademico'])
            ->orderByDesc('fecha_inicio')
            ->get();

        // Prácticas del curso actual (o sin curso asignado)
        $practicasCursoActual = $todasPracticas->filter(function ($practica) use ($cursoActual) {
            if (!$cursoActual) {
                return true;
            }
            return is_null($practica->curso_academico_id) || (int) $practica->curso_academico_id === (int) $cursoActual->id;
        })->values();

        // Prácticas de cursos anteriores (exclusivamente aquellas que pertenezcan a un curso diferente al actual)
        $practicasCursosAnteriores = $todasPracticas->filter(function ($practica) use ($cursoActual) {
            if (!$cursoActual) {
                return false;
            }
            return !is_null($practica->curso_academico_id) && (int) $practica->curso_academico_id !== (int) $cursoActual->id;
        })->values();

        $cursosAnterioresConPracticas = $practicasCursosAnteriores
            ->groupBy('curso_academico_id')
            ->map(function ($practicasGrupo) {
                return [
                    'curso' => $practicasGrupo->first()->cursoAcademico,
                    'practicas' => $practicasGrupo,
                ];
            })
            ->sortByDesc(fn($item) => $item['curso']?->fecha_inicio ?? '2000-01-01');

        return view('profesores.show', compact(
            'profesor',
            'empresas',
            'cursoActual',
            'practicasCursoActual',
            'cursosAnterioresConPracticas'
        ));
    }

    /**
     * Formulario para crear profesor.
     */
    public function create(): View
    {
        $ciclos = Ciclo::active()->get();
        $familias = Familia::active()->orderBy('nombre')->get();
        return view('profesores.create', compact('ciclos', 'familias'));
    }

    /**
     * Guardar nuevo profesor.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed'],
            'familia_id' => ['nullable', 'exists:familias,id'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'es_coordinador_dual' => ['nullable', 'boolean'],
            'tutor_grupo_id' => ['nullable', 'exists:grupos,id'],
            'asignatura_ids' => ['nullable', 'array'],
            'asignatura_ids.*' => ['exists:asignaturas,id'],
            'grupos' => ['nullable', 'array', function ($attribute, $value, $fail) {
                foreach ($value ?? [] as $grupoId => $data) {
                    if (!empty($data['activo']) && empty($data['asignatura_id'])) {
                        $fail('Debes elegir una asignatura para cada grupo que marques.');
                        break;
                    }
                }
            }],
            'grupos.*.activo' => ['nullable', 'boolean'],
            'grupos.*.asignatura_id' => ['nullable', 'integer'],
        ]);

        return DB::transaction(function () use ($validated) {
            $roles = [User::ROLE_PROFESOR];
            if (!empty($validated['es_coordinador_dual'])) {
                $roles[] = User::ROLE_COORDINADOR_DUAL;
            }

            // Crear usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'roles' => $roles,
                'is_active' => true,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]);

            // Crear profesor
            $profesor = Profesor::create([
                'user_id' => $user->id,
                'familia_id' => $validated['familia_id'] ?? null,
                'especialidad' => $validated['especialidad'] ?? null,
                'es_tutor' => !empty($validated['tutor_grupo_id']),
                'es_coordinador_dual' => $validated['es_coordinador_dual'] ?? false,
            ]);

            // Asignar tutor a grupo
            if (!empty($validated['tutor_grupo_id'])) {
                Grupo::where('id', $validated['tutor_grupo_id'])->update(['tutor_id' => $user->id]);
            }

            // Asignar asignaturas
            if (!empty($validated['asignatura_ids'])) {
                $profesor->asignaturas()->attach($validated['asignatura_ids']);
            }

            // Asignar a equipos educativos (grupo + asignatura impartida)
            $pivot = $this->pivotGruposAsignaturas($validated['grupos'] ?? []);
            foreach ($pivot as $grupoId => $asignaturaId) {
                $profesor->gruposImpartidos()->attach($grupoId, ['asignatura_id' => $asignaturaId]);
            }

            return redirect()->route('profesores.index')
                ->with('success', 'Profesor creado correctamente.');
        });
    }

    /**
     * Formulario para editar profesor.
     */
    public function edit(Request $request, Profesor $profesor): View
    {
        $profesor->load(['asignaturas', 'gruposImpartidos.linea.ciclo', 'familia']);
        $familias = Familia::active()->orderBy('nombre')->get();
        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        // Si no hay curso activo pero existen cursos, activar el más reciente; si no hay ninguno, crear el curso actual por defecto (2026-2027)
        if (!$cursoActual) {
            $cursoActual = CursoAcademico::orderBy('fecha_inicio', 'desc')->first();
            if ($cursoActual) {
                $cursoActual->update(['is_active' => true]);
            } else {
                $cursoActual = CursoAcademico::create([
                    'nombre' => '2026-2027',
                    'fecha_inicio' => '2026-09-01',
                    'fecha_fin' => '2027-06-30',
                    'is_active' => true,
                ]);
            }
        }

        // Familia a usar para la lista de grupos: la del profesor, la del select (preview), o la inferida de sus grupos
        $familiaId = $request->filled('familia')
            ? (int) $request->integer('familia')
            : ($profesor->familia_id ?: $profesor->gruposImpartidos->first()?->linea?->ciclo?->familia_id ?: $profesor->gruposTutor->first()?->linea?->ciclo?->familia_id);
        $familia = $familiaId ? Familia::find($familiaId) : null;

        // Grupos del curso actual SOLO de la familia profesional del profesor (editables).
        // Se muestran los grupos activos de la familia asignados al curso actual o sin curso asignado aún.
        $gruposCursoActual = collect();
        if ($familia) {
            $gruposCursoActual = Grupo::query()
                ->where('is_active', true)
                ->when($cursoActual, function ($q) use ($cursoActual) {
                    $q->where(function ($sub) use ($cursoActual) {
                        $sub->where('curso_academico_id', $cursoActual->id)
                            ->orWhereNull('curso_academico_id');
                    });
                })
                ->whereHas('linea.ciclo', fn($q) => $q->where('familia_id', $familia->id))
                ->with(['linea.ciclo', 'linea.ciclo.asignaturas', 'cursoAcademico'])
                ->orderBy('nombre')
                ->get()
                ->groupBy('linea.ciclo_id');
        }

        // Historial: grupos a los que ha dado clase en cursos anteriores (solo lectura).
        // Solo son historial los grupos que pertenezcan explícitamente a un curso anterior diferente del actual.
        $historial = collect();
        if ($profesor->gruposImpartidos->isNotEmpty() && $cursoActual) {
            $historial = Grupo::query()
                ->whereIn('id', $profesor->gruposImpartidos->pluck('id'))
                ->where('curso_academico_id', '!=', $cursoActual->id)
                ->whereNotNull('curso_academico_id')
                ->with(['cursoAcademico', 'linea.ciclo'])
                ->get()
                ->sortByDesc(fn($g) => $g->cursoAcademico?->fecha_inicio ?? now()->subYears(20)->toDateString());

            $asigPorGrupo = DB::table('grupo_profesor')
                ->where('profesor_id', $profesor->id)
                ->pluck('asignatura_id', 'grupo_id');
            $asignaturaIds = $asigPorGrupo->filter()->unique();
            $asignaturas = $asignaturaIds->isNotEmpty()
                ? Asignatura::whereIn('id', $asignaturaIds)->get()->keyBy('id')
                : collect();
            foreach ($historial as $grupo) {
                $grupo->setRelation('asignaturaHistorial', $asignaturas->get((int) ($asigPorGrupo[$grupo->id] ?? 0)));
            }
        }

        return view('profesores.edit', compact('profesor', 'familias', 'familia', 'cursoActual', 'gruposCursoActual', 'historial'));
    }

    /**
     * Actualizar profesor.
     */
    public function update(Request $request, Profesor $profesor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $profesor->user_id],
            'familia_id' => ['nullable', 'exists:familias,id'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'es_coordinador_dual' => ['nullable', 'boolean'],
            'tutor_grupo_id' => ['nullable', 'exists:grupos,id'],
            'asignatura_ids' => ['nullable', 'array'],
            'asignatura_ids.*' => ['exists:asignaturas,id'],
            'grupos' => ['nullable', 'array', function ($attribute, $value, $fail) {
                foreach ($value ?? [] as $grupoId => $data) {
                    if (!empty($data['activo']) && empty($data['asignatura_id'])) {
                        $fail('Debes elegir una asignatura para cada grupo que marques.');
                        break;
                    }
                }
            }],
            'grupos.*.activo' => ['nullable', 'boolean'],
            'grupos.*.asignatura_id' => ['nullable', 'integer'],
        ]);

        return DB::transaction(function () use ($validated, $profesor) {
            // Actualizar usuario
            $userRoles = $profesor->user->roles ?? [];
            $esCoord = !empty($validated['es_coordinador_dual']);
            if ($esCoord && !in_array(User::ROLE_COORDINADOR_DUAL, $userRoles, true)) {
                $userRoles[] = User::ROLE_COORDINADOR_DUAL;
            } elseif (!$esCoord && in_array(User::ROLE_COORDINADOR_DUAL, $userRoles, true)) {
                $userRoles = array_values(array_filter($userRoles, fn($r) => $r !== User::ROLE_COORDINADOR_DUAL));
            }

            $profesor->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'roles' => $userRoles,
            ]);

            // Actualizar profesor
            $profesor->update([
                'familia_id' => $validated['familia_id'] ?? null,
                'especialidad' => $validated['especialidad'] ?? null,
                'es_tutor' => !empty($validated['tutor_grupo_id']),
                'es_coordinador_dual' => $validated['es_coordinador_dual'] ?? false,
            ]);

            // Asignar tutor a grupo
            // Primero quitamos a este usuario como tutor de cualquier grupo que tuviera
            Grupo::where('tutor_id', $profesor->user->id)->update(['tutor_id' => null]);
            // Luego lo asignamos al nuevo
            if (!empty($validated['tutor_grupo_id'])) {
                Grupo::where('id', $validated['tutor_grupo_id'])->update(['tutor_id' => $profesor->user->id]);
            }

            // Actualizar equipos educativos (grupo + asignatura impartida).
            // Solo se editan los grupos del curso actual; las imparticiones de
            // cursos anteriores (historial) se conservan.
            $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
            $pivot = $this->pivotGruposAsignaturas($validated['grupos'] ?? []);

            if ($cursoActual) {
                $idsCursoActual = Grupo::where('curso_academico_id', $cursoActual->id)
                    ->orWhereNull('curso_academico_id')
                    ->pluck('id')
                    ->all();

                // Quitar imparticiones de grupos del curso actual que ya no están marcadas
                foreach ($profesor->gruposImpartidos as $grupo) {
                    if (in_array($grupo->id, $idsCursoActual, true) && !isset($pivot[$grupo->id])) {
                        $profesor->gruposImpartidos()->detach($grupo->id);
                    }
                }

                // Añadir/actualizar imparticiones marcadas del curso actual
                foreach ($pivot as $grupoId => $asignaturaId) {
                    if (in_array($grupoId, $idsCursoActual, true)) {
                        Grupo::where('id', $grupoId)->whereNull('curso_academico_id')->update(['curso_academico_id' => $cursoActual->id]);
                        if ($profesor->gruposImpartidos()->wherePivot('grupo_id', $grupoId)->exists()) {
                            $profesor->gruposImpartidos()->updateExistingPivot($grupoId, ['asignatura_id' => $asignaturaId]);
                        } else {
                            $profesor->gruposImpartidos()->attach($grupoId, ['asignatura_id' => $asignaturaId]);
                        }
                    }
                }
            }

            // Recalcular asignaturas a partir de TODOS los grupos impartidos (actuales + historial)
            $asignaturasId = $profesor->gruposImpartidos()
                ->get(['grupo_profesor.asignatura_id'])
                ->pluck('pivot.asignatura_id')
                ->filter()
                ->unique()
                ->values()
                ->all();
            $profesor->asignaturas()->sync($asignaturasId);

            return redirect()->route('profesores.show', $profesor)
                ->with('success', 'Profesor actualizado correctamente.');
        });
    }

    /**
     * Construye el array [grupo_id => asignatura_id] a partir del input "grupos".
     * Cada entrada: grupos[{grupoId}][activo] y grupos[{grupoId}][asignatura_id].
     * Solo se incluyen grupos activados con asignatura seleccionada.
     */
    private function pivotGruposAsignaturas(array $grupos): array
    {
        $pivot = [];

        foreach ($grupos as $grupoId => $data) {
            $activo = !empty($data['activo']);
            $asignaturaId = $data['asignatura_id'] ?? null;

            if ($activo && $asignaturaId) {
                $pivot[(int) $grupoId] = (int) $asignaturaId;
            }
        }

        return $pivot;
    }

    /**
     * Desactivar profesor.
     */
    public function deactivate(Profesor $profesor): RedirectResponse
    {
        $profesor->user->update(['is_active' => false]);
        return back()->with('success', 'Profesor desactivado.');
    }

    /**
     * Eliminar profesor.
     */
    public function destroy(Profesor $profesor): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $profesor->user->forceDelete();
        $profesor->forceDelete();

        return back()->with('success', 'Profesor eliminado definitivamente.');
    }

    /**
     * Crear sustitución.
     */
    public function storeSustitucion(Request $request, Profesor $profesor): RedirectResponse
    {
        $validated = $request->validate([
            'profesor_sustituto_id' => ['required', 'exists:profesores,id'],
            'asignatura_id' => ['nullable', 'exists:asignaturas,id'],
            'grupo_id' => ['nullable', 'exists:grupos,id'],
            'fecha_inicio' => ['required', 'date', 'after_or_equal:today'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        Sustitucion::create([
            'profesor_titular_id' => $profesor->id,
            'profesor_sustituto_id' => $validated['profesor_sustituto_id'],
            'asignatura_id' => $validated['asignatura_id'] ?? null,
            'grupo_id' => $validated['grupo_id'] ?? null,
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Sustitución creada correctamente.');
    }

    /**
     * Eliminar sustitución.
     */
    public function destroySustitucion(Sustitucion $sustitucion): RedirectResponse
    {
        $sustitucion->delete();
        return back()->with('success', 'Sustitución eliminada.');
    }
}