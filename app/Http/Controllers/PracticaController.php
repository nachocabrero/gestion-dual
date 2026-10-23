<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Practica;
use App\Models\TutorLaboral;
use App\Services\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador de gestión de prácticas.
 */
class PracticaController extends Controller
{
    public function __construct(protected NotificacionService $notificacionService) {}
    /**
     * Listar prácticas.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $query = Practica::with(['alumno.user', 'empresa', 'tutorLaboral', 'cursoAcademico']);

        $isAdminOrCoordinador = $user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);

        if (!$isAdminOrCoordinador && $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $tutorGroupIds = \App\Models\Grupo::where('tutor_id', $user->id)->pluck('id');
            $impartidosGroupIds = $user->profesor ? $user->profesor->gruposImpartidos()->pluck('grupos.id') : collect();
            $allGroupIds = $tutorGroupIds->merge($impartidosGroupIds)->unique()->filter()->values();

            $query->whereHas('alumno', function ($q) use ($user, $allGroupIds) {
                $q->where('tutor_practicas_id', $user->id)
                  ->orWhereHas('grupos', function ($qGrupos) use ($allGroupIds) {
                      $qGrupos->whereIn('grupos.id', $allGroupIds);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('alumno', function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'en_curso') {
                $query->enCurso();
            } elseif ($estado === 'finalizadas') {
                $query->finalizadas();
            } elseif ($estado === 'pendientes') {
                $query->pendientes();
            }
        }

        if ($request->filled('convenio')) {
            $query->where('convenio_firmado', $request->convenio === 'si');
        }

        if ($request->filled('curso_academico_id')) {
            $query->where('curso_academico_id', $request->curso_academico_id);
        }

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50])) {
            $perPage = 15;
        }

        $practicas = $query->orderBy('fecha_inicio', 'desc')->paginate($perPage)->withQueryString();
        $cursos = CursoAcademico::active()->get();

        return view('practicas.index', compact('practicas', 'cursos'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $isAdminOrCoordinador = $user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);

        if (!$isAdminOrCoordinador && $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $tutorGroupIds = \App\Models\Grupo::where('tutor_id', $user->id)->pluck('id');
            $impartidosGroupIds = $user->profesor ? $user->profesor->gruposImpartidos()->pluck('grupos.id') : collect();
            $allGroupIds = $tutorGroupIds->merge($impartidosGroupIds)->unique()->filter()->values();

            $alumnos = Alumno::puedenHacerPracticas()
                ->where(function ($q) use ($user, $allGroupIds) {
                    $q->where('tutor_practicas_id', $user->id)
                      ->orWhereHas('grupos', fn($gq) => $gq->whereIn('grupos.id', $allGroupIds));
                })
                ->with('user')
                ->get();
        } else {
            $alumnos = Alumno::puedenHacerPracticas()->with('user')->get();
        }

        $preselectedAlumnoId = $request->filled('alumno_id') ? (int) $request->input('alumno_id') : null;
        $preselectedEmpresaId = $request->filled('empresa_id') ? (int) $request->input('empresa_id') : null;
        $preselectedOfertaId = $request->filled('oferta_id') ? (int) $request->input('oferta_id') : null;

        if ($preselectedAlumnoId && !$alumnos->contains('id', $preselectedAlumnoId)) {
            $alumnoExtra = Alumno::with('user')->find($preselectedAlumnoId);
            if ($alumnoExtra && $alumnoExtra->puedeHacerPracticas()) {
                $alumnos->push($alumnoExtra);
            }
        }

        $alumnos = $alumnos->sortBy(fn($a) => \App\Support\StrSort::normalize($a->user?->name))->values();

        $alumnosData = $alumnos->map(fn($a) => [
            'id' => $a->id,
            'nombre' => $a->user?->name ?? ('Alumno #' . $a->id),
            'horas_primero' => (int) ($a->horas_practicas_primero ?? 0),
            'empresa_primero' => $a->empresa_practicas_primero ?? '',
        ])->values();

        $empresas = Empresa::active()->get()->sortBy(fn($e) => \App\Support\StrSort::normalize($e->nombre))->values();
        $tutores = TutorLaboral::with('empresa')->get()->sortBy(fn($t) => \App\Support\StrSort::normalize($t->nombre))->values();
        $cursos = CursoAcademico::active()->get();

        $tutoresData = $tutores->map(fn($t) => [
            'id' => $t->id,
            'nombre' => $t->nombre,
            'empresa_id' => $t->empresa_id,
        ])->values();

        return view('practicas.create', compact(
            'alumnos',
            'empresas',
            'tutores',
            'cursos',
            'alumnosData',
            'tutoresData',
            'preselectedAlumnoId',
            'preselectedEmpresaId',
            'preselectedOfertaId'
        ));
    }

    /**
     * Guardar práctica.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $errors = Practica::validarDatos($request->all());

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $validated = $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'empresa_id' => 'required|exists:empresas,id',
            'oferta_id' => 'nullable|exists:ofertas_practicas,id',
            'tutor_laboral_id' => 'nullable|exists:tutores_laborales,id',
            'curso_academico_id' => 'required|exists:cursos_academicos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'horas_acumuladas' => 'required|integer|min:0',
            'convenio_firmado' => 'boolean',
        ]);

        if (!empty($validated['tutor_laboral_id'])) {
            $tutorBelongs = TutorLaboral::where('id', $validated['tutor_laboral_id'])
                ->where('empresa_id', $validated['empresa_id'])
                ->exists();
            if (!$tutorBelongs) {
                return back()->withErrors(['tutor_laboral_id' => 'El tutor laboral seleccionado debe pertenecer a la empresa elegida.'])->withInput();
            }
        }

        if (!$user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]) && $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $this->authorizeProfesorAlumno($user, $validated['alumno_id']);
        }

        $practica = Practica::create($validated);

        if (!empty($validated['oferta_id'])) {
            $oferta = \App\Models\OfertaPractica::find($validated['oferta_id']);
            if ($oferta) {
                \App\Models\SolicitudPractica::where('oferta_id', $oferta->id)
                    ->where('alumno_id', $validated['alumno_id'])
                    ->update(['estado' => 'aceptado']);

                if ($oferta->practicas()->count() >= $oferta->num_alumnos) {
                    $oferta->update(['estado' => 'cerrada']);
                }
            }

            $total = $practica->totalHorasAlumno();
            $msg = 'Práctica registrada correctamente y vinculada a la oferta.';
            if ($total < 500) {
                return redirect()->route('ofertas.show', $validated['oferta_id'])
                    ->with('warning', "{$msg} Total acumulado del alumno: {$total}h. Aún no se alcanzan las 500h mínimas entre 1º y 2º.");
            }
            return redirect()->route('ofertas.show', $validated['oferta_id'])
                ->with('success', $msg);
        }

        return $this->redirigirTrasGuardar($practica, 'Práctica registrada correctamente.');
    }

    /**
     * Mostrar práctica.
     */
    public function show(Practica $practica): View
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        if (!$user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]) && $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $this->authorizeProfesorPractica($user, $practica);
        }

        $practica->load(['alumno.user', 'empresa', 'tutorLaboral', 'cursoAcademico']);

        return view('practicas.show', compact('practica'));
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Practica $practica): View
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        $isAdminOrCoordinador = $user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);

        if (!$isAdminOrCoordinador && $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $this->authorizeProfesorPractica($user, $practica);

            $tutorGroupIds = \App\Models\Grupo::where('tutor_id', $user->id)->pluck('id');
            $impartidosGroupIds = $user->profesor ? $user->profesor->gruposImpartidos()->pluck('grupos.id') : collect();
            $allGroupIds = $tutorGroupIds->merge($impartidosGroupIds)->unique()->filter()->values();

            $alumnos = Alumno::where('tutor_practicas_id', $user->id)
                ->orWhereHas('grupos', fn($q) => $q->whereIn('grupos.id', $allGroupIds))
                ->orWhere('id', $practica->alumno_id)
                ->with('user')
                ->get();
        } else {
            $alumnos = Alumno::with('user')->get();
        }

        $practica->load(['alumno', 'empresa', 'tutorLaboral', 'cursoAcademico']);
        $empresas = Empresa::active()->get();
        $tutores = TutorLaboral::with('empresa')->get();
        $cursos = CursoAcademico::active()->get();

        return view('practicas.edit', compact('practica', 'alumnos', 'empresas', 'tutores', 'cursos'));
    }

    /**
     * Actualizar práctica.
     */
    public function update(Request $request, Practica $practica): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        if (!$user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]) && $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $this->authorizeProfesorPractica($user, $practica);
        }

        $errors = Practica::validarDatos($request->all());

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $validated = $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'empresa_id' => 'required|exists:empresas,id',
            'tutor_laboral_id' => 'nullable|exists:tutores_laborales,id',
            'curso_academico_id' => 'required|exists:cursos_academicos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'horas_acumuladas' => 'required|integer|min:0',
            'convenio_firmado' => 'boolean',
        ]);

        $practica->update($validated);

        return $this->redirigirTrasGuardar($practica, 'Práctica actualizada correctamente.', 'show');
    }

    /**
     * Eliminar práctica.
     */
    public function destroy(Practica $practica): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN), 403);

        $practica->delete();

        return redirect()->route('practicas.index')->with('success', 'Práctica eliminada correctamente.');
    }

    /**
     * Ver prácticas de un alumno.
     */
    public function misPracticas(): View
    {
        $alumno = auth()->user()->alumno;

        abort_unless($alumno, 403);

        $practicas = Practica::where('alumno_id', $alumno->id)
            ->with(['empresa', 'tutorLaboral', 'cursoAcademico'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('practicas.mis-practicas', compact('practicas'));
    }

    /**
     * Actualizar horas (para profesor/coordinador/admin).
     */
    public function actualizarHoras(Request $request, Practica $practica): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_PROFESOR]), 403);

        if (!$user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]) && $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $this->authorizeProfesorPractica($user, $practica);
        }

        $validated = $request->validate([
            'horas_acumuladas' => 'required|integer|min:0',
            'convenio_firmado' => 'boolean',
        ]);

        $practica->update($validated);

        // Notificar si se firma el convenio por primera vez
        if (isset($validated['convenio_firmado']) && $validated['convenio_firmado'] && !$practica->getRawOriginal('convenio_firmado')) {
            $this->notificacionService->acuerdoCambiado(
                $practica->alumno_id,
                'firmado'
            );
        }

        return back()->with('success', 'Horas actualizadas correctamente.');
    }

    /**
     * Autorizar acceso de profesor a una práctica.
     */
    private function authorizeProfesorPractica(\App\Models\User $user, Practica $practica): void
    {
        $this->authorizeProfesorAlumno($user, $practica->alumno_id);
    }

    /**
     * Autorizar que un alumno pertenece al ámbito del profesor.
     */
    private function authorizeProfesorAlumno(\App\Models\User $user, int $alumnoId): void
    {
        $tutorGroupIds = \App\Models\Grupo::where('tutor_id', $user->id)->pluck('id');
        $impartidosGroupIds = $user->profesor ? $user->profesor->gruposImpartidos()->pluck('grupos.id') : collect();
        $allGroupIds = $tutorGroupIds->merge($impartidosGroupIds)->unique()->filter()->values();

        $alumno = Alumno::find($alumnoId);
        $esTutorPracticas = $alumno && $alumno->tutor_practicas_id === $user->id;
        $enGrupo = $alumno && $alumno->grupos()->whereIn('grupos.id', $allGroupIds)->exists();

        abort_unless($esTutorPracticas || $enGrupo, 403);
    }

    /**
     * Redirigir tras crear/actualizar una práctica, con aviso si el total
     * acumulado del alumno aún no alcanza las 500h mínimas entre 1º y 2º.
     */
    private function redirigirTrasGuardar(Practica $practica, string $mensajeExito, string $destino = 'index'): RedirectResponse
    {
        $total = $practica->totalHorasAlumno();

        if ($total < 500) {
            return redirect()->route('practicas.' . $destino, $destino === 'show' ? $practica : [])
                ->with('warning', "{$mensajeExito} Total acumulado del alumno: {$total}h. Aún no se alcanzan las 500h mínimas entre 1º y 2º.");
        }

        return redirect()->route('practicas.' . $destino, $destino === 'show' ? $practica : [])
            ->with('success', $mensajeExito);
    }
}