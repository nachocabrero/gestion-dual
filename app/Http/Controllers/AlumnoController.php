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

        // Ordenar alfabéticamente por nombre de usuario en base de datos
        $query->join('users', 'alumnos.user_id', '=', 'users.id')
            ->select('alumnos.*')
            ->orderBy('users.name', 'asc');

        // Filtro por grupo
        if ($request->filled('grupo_id')) {
            $grupoId = (int) $request->grupo_id;
            $query->whereHas('grupos', fn($q) => $q->where('grupos.id', $grupoId));
        }

        // Otros Filtros
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
            $query->where(function ($sub) use ($request) {
                $sub->where('users.name', 'like', '%' . $request->search . '%')
                    ->orWhere('users.email', 'like', '%' . $request->search . '%');
            });
        }

        // Si es profesor (y no admin ni coordinador dual), solo ver sus grupos
        $allTeacherGroups = collect();
        if (auth()->user()->hasRole(User::ROLE_PROFESOR) && !auth()->user()->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
            $tutorGrupos = Grupo::where('tutor_id', auth()->id())->pluck('id');
            $impartidosGroupIds = auth()->user()->profesor ? auth()->user()->profesor->gruposImpartidos()->pluck('grupos.id') : collect();
            $allTeacherGroups = $tutorGrupos->merge($impartidosGroupIds)->unique()->filter()->values();
            $query->whereHas('grupos', fn($q) => $q->whereIn('grupos.id', $allTeacherGroups));
        }

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50])) {
            $perPage = 15;
        }

        $alumnos = $query->paginate($perPage)->withQueryString();

        // Datos para filtros
        $familias = Familia::active()->get();
        $ciclos = Ciclo::active()->get();
        $lineas = Linea::active()->get();
        $cursos = CursoAcademico::orderBy('fecha_inicio', 'desc')->get();

        $gruposQuery = Grupo::query()->where('is_active', true);
        if (auth()->user()->hasRole(User::ROLE_PROFESOR) && !auth()->user()->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL])) {
            $gruposQuery->whereIn('id', $allTeacherGroups);
        }
        $grupos = $gruposQuery->with(['linea.ciclo', 'cursoAcademico'])->get()->sortBy('nombre')->values();

        return view('alumnos.index', compact('alumnos', 'familias', 'ciclos', 'lineas', 'cursos', 'grupos'));
    }

    /**
     * Mostrar perfil completo de un alumno.
     */
    public function show(Alumno $alumno): View
    {
        $alumno->load([
            'user',
            'grupos.linea.ciclo.familia',
            'grupos.cursoAcademico',
            'grupos.tutor',
            'tutorPracticas',
            'ciclosMatriculados',
            'practicas.empresa',
            'practicas.oferta',
            'practicas.cursoAcademico',
            'practicas.tutorLaboral',
        ]);

        return view('alumnos.show', compact('alumno'));
    }

    /**
     * Formulario para crear alumno.
     */
    public function create(): View
    {
        $cursoActivo = \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
        $grupos = Grupo::where('is_active', true)
            ->when($cursoActivo, fn($q) => $q->where('curso_academico_id', $cursoActivo->id))
            ->with('linea.ciclo.familia')
            ->get();
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
            'portfolio_url' => ['nullable', 'url', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'tutor_practicas_id' => ['nullable', 'exists:users,id'],
            'empresa_practicas_primero' => ['nullable', 'string', 'max:255'],
            'horas_practicas_primero' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'matriculas' => ['nullable', 'array'],
            'matriculas.*.ciclo_id' => ['required_with:matriculas', 'exists:ciclos,id'],
            'matriculas.*.curso_academico' => ['required_with:matriculas', 'string', 'max:7'],
        ]);

        $linkedin = trim($validated['linkedin_url'] ?? '');
        $portfolio = trim($validated['portfolio_url'] ?? '');
        $validated['linkedin_url'] = ($linkedin === 'https://' || $linkedin === 'http://' || $linkedin === '') ? null : $linkedin;
        $validated['portfolio_url'] = ($portfolio === 'https://' || $portfolio === 'http://' || $portfolio === '') ? null : $portfolio;

        return DB::transaction(function () use ($validated) {
            // Crear usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'roles' => [User::ROLE_ALUMNO],
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'is_active' => true,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]);

            // Crear alumno
            $alumno = Alumno::create([
                'user_id' => $user->id,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'domicilio' => $validated['domicilio'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'tutor_practicas_id' => $validated['tutor_practicas_id'] ?? null,
                'empresa_practicas_primero' => $validated['empresa_practicas_primero'] ?? null,
                'horas_practicas_primero' => isset($validated['horas_practicas_primero']) && $validated['horas_practicas_primero'] !== ''
                    ? (int) $validated['horas_practicas_primero']
                    : null,
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
            'portfolio_url' => ['nullable', 'url', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'tutor_practicas_id' => ['nullable', 'exists:users,id'],
            'empresa_practicas_primero' => ['nullable', 'string', 'max:255'],
            'horas_practicas_primero' => ['nullable', 'integer', 'min:0', 'max:2000'],
        ]);

        $linkedin = trim($validated['linkedin_url'] ?? '');
        $portfolio = trim($validated['portfolio_url'] ?? '');
        $validated['linkedin_url'] = ($linkedin === 'https://' || $linkedin === 'http://' || $linkedin === '') ? null : $linkedin;
        $validated['portfolio_url'] = ($portfolio === 'https://' || $portfolio === 'http://' || $portfolio === '') ? null : $portfolio;

        return DB::transaction(function () use ($validated, $alumno) {
            // Actualizar usuario
            $alumno->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
            ]);

            // Actualizar alumno
            $alumno->update([
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'domicilio' => $validated['domicilio'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'tutor_practicas_id' => $validated['tutor_practicas_id'] ?? null,
                'empresa_practicas_primero' => $validated['empresa_practicas_primero'] ?? null,
                'horas_practicas_primero' => isset($validated['horas_practicas_primero']) && $validated['horas_practicas_primero'] !== ''
                    ? (int) $validated['horas_practicas_primero']
                    : null,
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