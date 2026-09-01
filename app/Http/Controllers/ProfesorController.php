<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
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
        $query = Profesor::with(['user', 'gruposTutor']);

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%'));
        }

        if ($request->filled('es_tutor')) {
            $query->tutores();
        }

        if ($request->filled('es_coordinador')) {
            $query->coordinadores();
        }

        $profesores = $query->paginate(30);
        $profesores->getCollection()->sortBy('user.name');

        return view('profesores.index', compact('profesores'));
    }

    /**
     * Mostrar perfil de un profesor.
     */
    public function show(Profesor $profesor): View
    {
        $profesor->load(['user', 'familia', 'gruposTutor.linea.ciclo', 'gruposImpartidos', 'asignaturas', 'sustituciones']);

        return view('profesores.show', compact('profesor'));
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
            // Crear usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'roles' => [User::ROLE_PROFESOR],
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
        $profesor->load(['asignaturas', 'gruposImpartidos', 'familia']);
        $familias = Familia::active()->orderBy('nombre')->get();
        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        // Familia a usar para la lista de grupos: la del profesor, o la elegida en el select (preview).
        $familiaId = $request->filled('familia') ? (int) $request->integer('familia') : $profesor->familia_id;
        $familia = $familiaId ? Familia::find($familiaId) : null;

        // Grupos del curso actual SOLO de la familia profesional del profesor (editables).
        $gruposCursoActual = collect();
        if ($familia && $cursoActual) {
            $gruposCursoActual = Grupo::query()
                ->where('curso_academico_id', $cursoActual->id)
                ->whereHas('linea.ciclo', fn($q) => $q->where('familia_id', $familia->id))
                ->with(['linea.ciclo', 'linea.ciclo.asignaturas', 'cursoAcademico'])
                ->orderBy('nombre')
                ->get()
                ->groupBy('linea.ciclo_id');
        }

        // Historial: grupos a los que ha dado clase en cursos anteriores (solo lectura).
        $historial = collect();
        if ($profesor->gruposImpartidos->isNotEmpty()) {
            $historial = Grupo::query()
                ->whereIn('id', $profesor->gruposImpartidos->pluck('id'))
                ->where(fn($q) => $q->where('curso_academico_id', '!=', $cursoActual?->id)->orWhereNull('curso_academico_id'))
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
            $profesor->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
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
                $idsCursoActual = Grupo::where('curso_academico_id', $cursoActual->id)->pluck('id')->all();

                // Quitar imparticiones de grupos del curso actual que ya no están marcadas
                foreach ($profesor->gruposImpartidos as $grupo) {
                    if (in_array($grupo->id, $idsCursoActual, true) && !isset($pivot[$grupo->id])) {
                        $profesor->gruposImpartidos()->detach($grupo->id);
                    }
                }

                // Añadir/actualizar imparticiones marcadas del curso actual
                foreach ($pivot as $grupoId => $asignaturaId) {
                    if (in_array($grupoId, $idsCursoActual, true)) {
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