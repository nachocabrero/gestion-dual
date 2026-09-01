<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Familia;
use App\Models\Ciclo;
use App\Models\Linea;
use App\Models\Grupo;
use App\Models\CursoAcademico;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de gestión de alumnos.
 * Acceso: Admin, Coordinador Dual, Profesor (solo su grupo).
 */
class AlumnoController extends Controller
{
    /**
     * Listar todos los alumnos con filtros.
     */
    public function index(Request $request): View
    {
        $cursoSeleccionadoId = $request->filled('curso_academico_id') 
            ? (int) $request->curso_academico_id 
            : CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->value('id');

        $query = Alumno::with([
            'user', 
            'grupos' => function($q) use ($cursoSeleccionadoId) {
                if ($cursoSeleccionadoId) {
                    $q->where('alumno_grupo.curso_academico_id', $cursoSeleccionadoId);
                }
            },
            'grupos.linea.ciclo',
            'tutorPracticas'
        ]);

        // Filtros
        if ($request->filled('familia')) {
            $query->whereHas('grupos.linea.ciclo.familia', fn($q) => $q->where('codigo', $request->familia));
        }
        if ($request->filled('ciclo')) {
            $query->whereHas('grupos.linea.ciclo', fn($q) => $q->where('codigo', $request->ciclo));
        }
        if ($request->filled('linea')) {
            $query->whereHas('grupos.linea', fn($q) => $q->where('turno', $request->linea));
        }
        if ($request->filled('curso_academico_id')) {
            // Alumnos pertenecientes a algún grupo de ese curso (pivot versionado por curso)
            $cursoId = (int) $request->curso_academico_id;
            $query->whereHas('grupos', fn($q) => $q->where('alumno_grupo.curso_academico_id', $cursoId));
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%'));
        }

        // Si es profesor, solo ver su grupo
        if (auth()->user()->hasRole(User::ROLE_PROFESOR) && !auth()->user()->hasRole(User::ROLE_ADMIN)) {
            $tutorGrupos = Grupo::where('tutor_id', auth()->id())->pluck('id');
            $query->whereHas('grupos', fn($q) => $q->whereIn('grupos.id', $tutorGrupos));
        }

        $alumnos = $query->paginate(50);
        $alumnos->getCollection()->sortBy('user.name');

        // Datos para filtros
        $familias = Familia::active()->get();
        $ciclos = Ciclo::active()->get();
        $lineas = Linea::active()->get();
        $cursos = CursoAcademico::orderBy('fecha_inicio', 'desc')->get();

        return view('alumnos.index', compact('alumnos', 'familias', 'ciclos', 'lineas', 'cursos'));
    }

    /**
     * Mostrar perfil completo de un alumno.
     */
    public function show(Alumno $alumno): View
    {
        $alumno->load(['user', 'grupos.linea.ciclo.familia', 'grupos.cursoAcademico', 'tutorPracticas', 'ciclosMatriculados']);

        return view('alumnos.show', compact('alumno'));
    }

    /**
     * Formulario para crear alumno.
     */
    public function create(): View
    {
        $grupos = Grupo::where('is_active', true)->with('linea.ciclo.familia')->get();
        return view('alumnos.create', compact('grupos'));
    }

    /**
     * Guardar nuevo alumno.
     * RGPD: Verificar consentimiento antes de crear.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed'],
            'grupos_ids' => ['nullable', 'array'],
            'grupos_ids.*' => ['exists:grupos,id'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'tutor_practicas_id' => ['nullable', 'exists:users,id'],
            'matriculas' => ['nullable', 'array'],
            'matriculas.*.ciclo_id' => ['required_with:matriculas', 'exists:ciclos,id'],
            'matriculas.*.curso_academico' => ['required_with:matriculas', 'string', 'max:7'],
        ]);

        return DB::transaction(function () use ($validated) {
            // Crear usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'roles' => [User::ROLE_ALUMNO],
                'is_active' => true,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]);

            // Crear alumno
            $alumno = Alumno::create([
                'user_id' => $user->id,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'domicilio' => $validated['domicilio'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'tutor_practicas_id' => $validated['tutor_practicas_id'] ?? null,
            ]);

            if (!empty($validated['grupos_ids'])) {
                $alumno->grupos()->sync($validated['grupos_ids']);
            }

            // Matricular en ciclos
            if (!empty($validated['matriculas'])) {
                foreach ($validated['matriculas'] as $m) {
                    $alumno->ciclosMatriculados()->attach(
                        $m['ciclo_id'],
                        ['curso_academico' => $m['curso_academico'], 'matriculado_at' => now()]
                    );
                }
            }

            return redirect()->route('alumnos.index')
                ->with('success', 'Alumno creado correctamente.');
        });
    }

    /**
     * Formulario para editar alumno.
     */
    public function edit(Alumno $alumno): View
    {
        $alumno->load(['grupos', 'ciclosMatriculados']);
        $tutores = User::whereJsonContains('roles', 'profesor')
            ->orWhereJsonContains('roles', 'coordinador_dual')
            ->get();

        $grupos = Grupo::where('is_active', true)
            ->with(['linea.ciclo.familia', 'cursoAcademico'])
            ->get()
            ->sortByDesc(fn($g) => $g->cursoAcademico?->fecha_inicio ?? now()->subYears(10)->toDateString())
            ->values();

        return view('alumnos.edit', compact('alumno', 'grupos', 'tutores'));
    }

    /**
     * Actualizar alumno.
     */
    public function update(Request $request, Alumno $alumno): RedirectResponse
    {
            $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $alumno->user_id],
            'grupos_ids' => ['nullable', 'array'],
            'grupos_ids.*' => ['exists:grupos,id'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'tutor_practicas_id' => ['nullable', 'exists:users,id'],
        ]);

        return DB::transaction(function () use ($validated, $alumno) {
            // Actualizar usuario
            $alumno->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // Actualizar alumno
            $alumno->update([
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'domicilio' => $validated['domicilio'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'tutor_practicas_id' => $validated['tutor_practicas_id'] ?? null,
            ]);

            $gruposIds = $validated['grupos_ids'] ?? [];
            $pivotData = [];
            if (!empty($gruposIds)) {
                $pivotData = Grupo::whereIn('id', $gruposIds)
                    ->pluck('curso_academico_id', 'id')
                    ->map(fn($cursoId) => ['curso_academico_id' => $cursoId])
                    ->toArray();
            }
            $alumno->grupos()->sync($pivotData);

            return redirect()->route('alumnos.show', $alumno)
                ->with('success', 'Alumno actualizado correctamente.');
        });
    }

    /**
     * Desactivar alumno (soft).
     */
    public function deactivate(Alumno $alumno): RedirectResponse
    {
        $alumno->user->update(['is_active' => false]);
        return back()->with('success', 'Alumno desactivado.');
    }

    /**
     * Eliminar alumno (hard delete - solo admin).
     * RGPD: Art. 17 - Supresión definitiva.
     */
    public function destroy(Alumno $alumno): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN), 403);

        $email = $alumno->user->email;

        // Log de eliminación
        \Illuminate\Support\Facades\Log::info('Alumno eliminado', [
            'admin_id' => auth()->id(),
            'alumno_id' => $alumno->id,
            'email' => $email,
        ]);

        $alumno->user->forceDelete();
        $alumno->forceDelete();

        return back()->with('success', 'Alumno eliminado definitivamente.');
    }

    /**
     * Eliminar una matrícula individual.
     */
    public function destroyMatricula(Request $request, Alumno $alumno): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_COORDINADOR_DUAL), 403);

        $request->validate([
            'matricula_id' => ['required', 'exists:alumno_ciclo_matricula,id'],
        ]);

        $alumno->ciclosMatriculados()->detach($request->matricula_id);

        return back()->with('success', 'Matrícula eliminada.');
    }
}