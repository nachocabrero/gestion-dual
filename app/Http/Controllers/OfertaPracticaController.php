<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\OfertaPractica;
use App\Models\SolicitudPractica;
use App\Models\User;
use App\Services\EvaluacionGrupoService;
use App\Services\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

/**
 * Controlador de ofertas y solicitudes de prácticas.
 */
class OfertaPracticaController extends Controller
{
    public function __construct(protected NotificacionService $notificacionService) {}
    /**
     * Listar ofertas (según rol).
     */
    public function index(Request $request): View
    {
        $query = OfertaPractica::with(['empresa', 'creador', 'grupos'])
            ->withCount('candidatos');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('especialidad_requerida', 'like', "%{$search}%")
                  ->orWhereHas('empresa', function ($eq) use ($search) {
                      $eq->where('nombre', 'like', "%{$search}%")
                         ->orWhere('cif', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('especialidad')) {
            $query->porEspecialidad($request->especialidad);
        }

        if ($request->filled('grupo_id')) {
            $grupoId = (int) $request->grupo_id;
            $query->whereHas('grupos', fn($q) => $q->where('grupos.id', $grupoId));
        }

        $user = auth()->user();
        $isProfesor = $user->hasRole(\App\Models\User::ROLE_PROFESOR);
        $isEmpresa = $user->hasRole(\App\Models\User::ROLE_EMPRESA);
        $isAdminOrCoord = $user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);
        $teacherGroupIds = collect();

        if ($user->hasRole(\App\Models\User::ROLE_ALUMNO)) {
            $alumno = $user->alumno;
            if ($alumno) {
                $alumnoGrupoIds = $alumno->grupos()->pluck('grupos.id');
                $query->where(function ($q) use ($alumno, $alumnoGrupoIds) {
                    $q->where(function ($sub) use ($alumnoGrupoIds) {
                        $sub->whereIn('estado', ['abierta', 'activa'])
                            ->whereHas('grupos', fn($gq) => $gq->whereIn('grupos.id', $alumnoGrupoIds));
                    })
                    ->orWhere(function ($sub) use ($alumno) {
                        $sub->whereIn('estado', ['solicitada', 'cerrada'])
                            ->whereHas('solicitudes', fn($sq) => $sq->where('alumno_id', $alumno->id));
                    });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($isEmpresa && ! $isAdminOrCoord && ! $isProfesor) {
            $empresa = $user->empresa;
            if ($empresa) {
                $query->where('empresa_id', $empresa->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($isProfesor && ! $isAdminOrCoord) {
            $tutorGrupos = Grupo::where('tutor_id', $user->id)->pluck('id');
            $impartidosGroupIds = $user->profesor ? $user->profesor->gruposImpartidos()->pluck('grupos.id') : collect();
            $teacherGroupIds = $tutorGrupos->merge($impartidosGroupIds)->unique()->filter()->values();

            if ($teacherGroupIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('grupos', fn($gq) => $gq->whereIn('grupos.id', $teacherGroupIds));
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50])) {
            $perPage = 15;
        }

        $ofertas = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        $especialidadesQuery = OfertaPractica::query();
        if ($isEmpresa && ! $isAdminOrCoord && ! $isProfesor) {
            $empresa = $user->empresa;
            if ($empresa) {
                $especialidadesQuery->where('empresa_id', $empresa->id);
            } else {
                $especialidadesQuery->whereRaw('1 = 0');
            }
        }
        $especialidades = $especialidadesQuery->distinct()->pluck('especialidad_requerida')->toArray();

        // Grupos para el selector de filtro
        if ($isProfesor && ! $isAdminOrCoord) {
            $gruposFiltro = Grupo::whereIn('id', $teacherGroupIds)
                ->with('linea.ciclo')
                ->get()
                ->sortBy(fn($g) => \App\Support\StrSort::normalize($g->nombre))
                ->values();
        } elseif ($isAdminOrCoord) {
            $gruposFiltro = Grupo::active()
                ->with('linea.ciclo')
                ->get()
                ->sortBy(fn($g) => \App\Support\StrSort::normalize($g->nombre))
                ->values();
        } else {
            $gruposFiltro = collect();
        }

        return view('ofertas.index', compact('ofertas', 'especialidades', 'gruposFiltro'));
    }

    /**
     * Formulario crear oferta.
     */
    public function create(): View
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_EMPRESA, \App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]), 403);

        $isAdminOrCoord = $user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);

        if ($user->hasRole(\App\Models\User::ROLE_EMPRESA) && ! $isAdminOrCoord && ! $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $empresa = $user->empresa;
            abort_unless($empresa, 403, 'No tienes ninguna empresa asignada para crear ofertas.');
            $empresas = collect([$empresa]);
        } else {
            $empresas = Empresa::active()
                ->admiteAlumnosCursoActual()
                ->get()
                ->sortBy(fn($e) => \App\Support\StrSort::normalize($e->nombre))
                ->values();
        }

        $grupos = $this->gruposCursoActual();

        return view('ofertas.create', compact('empresas', 'grupos'));
    }

    /**
     * Guardar oferta.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_EMPRESA, \App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]), 403);

        $validated = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'especialidad_requerida' => ['required', 'string', 'max:100'],
            'num_alumnos' => ['required', 'integer', 'min:1', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'grupo_ids' => ['nullable', 'array'],
            'grupo_ids.*' => ['integer', 'exists:grupos,id'],
            'estado' => ['nullable', 'in:pendiente,abierta,solicitada,cerrada,activa'],
        ]);

        $isAdminOrCoord = $user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);

        if ($user->hasRole(\App\Models\User::ROLE_EMPRESA) && ! $isAdminOrCoord && ! $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $empresa = $user->empresa;
            if ($empresa) {
                abort_unless((int) $validated['empresa_id'] === (int) $empresa->id, 403, 'Solo puedes crear ofertas para tu propia empresa.');
            } else {
                $user->update(['empresa_id' => $validated['empresa_id']]);
            }
        }

        $validated['creador_id'] = $user->id;
        $validated['creador_type'] = User::class;
        $validated['estado'] = $validated['estado'] ?? 'pendiente';

        $oferta = OfertaPractica::create($validated);

        if (! empty($request->grupo_ids)) {
            $oferta->grupos()->sync($request->grupo_ids);
        }

        // Si se crea una oferta de prácticas para una empresa, marcarle automáticamente que sí cogen este curso alumnado
        if ($oferta->empresa_id) {
            Empresa::where('id', $oferta->empresa_id)->update(['admite_alumnos_curso_actual' => true]);
        }

        return redirect()->route('ofertas.show', $oferta)
            ->with('success', 'Oferta creada correctamente.');
    }

    /**
     * Ver oferta.
     */
    public function show(OfertaPractica $oferta): View
    {
        $user = auth()->user();
        $isAdminOrCoord = $user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL]);

        if ($user->hasRole(\App\Models\User::ROLE_ALUMNO)) {
            $alumno = $user->alumno;
            if (!$alumno) {
                abort(403, 'No tienes perfil de alumno asociado.');
            }

            $alumnoGrupoIds = $alumno->grupos()->pluck('grupos.id');
            $esDeGrupoAsignado = $oferta->grupos()->whereIn('grupos.id', $alumnoGrupoIds)->exists();
            $estaAsignadoAOferta = $oferta->solicitudes()->where('alumno_id', $alumno->id)->exists()
                || $oferta->practicas()->where('alumno_id', $alumno->id)->exists();

            if (in_array($oferta->estado, ['abierta', 'activa'])) {
                abort_unless($esDeGrupoAsignado || $estaAsignadoAOferta, 403, 'No perteneces a ningún grupo destinatario de esta oferta.');
            } elseif (in_array($oferta->estado, ['solicitada', 'cerrada'])) {
                abort_unless($estaAsignadoAOferta, 403, 'Esta oferta sólo está disponible para alumnos asignados por el profesor.');
            } else {
                abort(403, 'Esta oferta está pendiente y sólo es visible para el profesorado.');
            }
        } elseif ($user->hasRole(\App\Models\User::ROLE_EMPRESA) && ! $isAdminOrCoord && ! $user->hasRole(\App\Models\User::ROLE_PROFESOR)) {
            $empresa = $user->empresa;
            abort_unless($empresa && (int) $oferta->empresa_id === (int) $empresa->id, 403, 'No tienes permiso para acceder a ofertas de otra empresa.');
        }

        $oferta->load(['empresa', 'creador', 'solicitudes.alumno.user', 'solicitudes.alumno.practicas', 'grupos', 'practicas']);

        $thisCanEdit = $this->canEditOferta($oferta);
        $thisCanEnviar = $this->canEnviarOferta($oferta);
        $thisCanDescargarCvs = $this->canDescargarCvs($oferta);

        $totalCvsDisponibles = $oferta->solicitudes->filter(function ($solicitud) {
            $user = $solicitud->alumno?->user;
            return $user && $user->cv_url && Storage::disk('public')->exists($user->cv_url);
        })->count();

        $alumno = auth()->user()->alumno;
        $miSolicitud = ($alumno && auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO))
            ? $oferta->solicitudes->firstWhere('alumno_id', $alumno->id)
            : null;

        return view('ofertas.show', compact('oferta', 'thisCanEdit', 'thisCanEnviar', 'thisCanDescargarCvs', 'totalCvsDisponibles', 'miSolicitud'));
    }

    /**
     * Formulario editar.
     */
    public function edit(OfertaPractica $oferta): View
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $empresas = Empresa::active()
            ->where(function ($q) use ($oferta) {
                $q->where('admite_alumnos_curso_actual', true)
                  ->orWhere('id', $oferta->empresa_id);
            })
            ->get()
            ->sortBy(fn($e) => \App\Support\StrSort::normalize($e->nombre))
            ->values();
        $grupos = $this->gruposCursoActual();

        return view('ofertas.edit', compact('oferta', 'empresas', 'grupos'));
    }

    /**
     * Actualizar oferta.
     */
    public function update(Request $request, OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $validated = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'especialidad_requerida' => ['required', 'string', 'max:100'],
            'num_alumnos' => ['required', 'integer', 'min:1', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'estado' => ['required', 'in:pendiente,abierta,solicitada,cerrada,activa'],
            'grupo_ids' => ['nullable', 'array'],
            'grupo_ids.*' => ['integer', 'exists:grupos,id'],
        ]);

        $oferta->update($validated);

        if ($request->has('grupo_ids')) {
            $oferta->grupos()->sync($request->grupo_ids);
        }

        return redirect()->route('ofertas.show', $oferta)
            ->with('success', 'Oferta actualizada.');
    }

    /**
     * Eliminar oferta.
     */
    public function destroy(OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $oferta->delete();
        return redirect()->route('ofertas.index')
            ->with('success', 'Oferta eliminada.');
    }

    /**
     * Formulario para asignar y enviar la oferta a alumnos:
     * muestra los alumnos de los grupos a los que está dirigida la oferta,
     * preseleccionando aquellos que ya tienen plaza asignada (solicitud aceptada).
     */
    public function enviarForm(Request $request, OfertaPractica $oferta, EvaluacionGrupoService $evaluacionService): View
    {
        abort_unless($this->canEnviarOferta($oferta), 403);

        $oferta->load(['grupos', 'solicitudes']);
        $cursoActual = \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        // Si se pasa grupo_id y no está vinculado a la oferta, vincularlo automáticamente
        if ($request->filled('grupo_id')) {
            $grupoId = (int) $request->grupo_id;
            if (!$oferta->grupos->contains('id', $grupoId)) {
                $oferta->grupos()->attach($grupoId);
                $oferta->load('grupos');
            }
        }

        // Si la oferta no tiene grupos asignados, la lista de grupos debe ser vacía
        $gruposModel = $oferta->grupos->isEmpty()
            ? collect()
            : Grupo::whereIn('id', $oferta->grupos->pluck('id'))
                ->orderBy('nombre')
                ->with('alumnos.user')
                ->get();

        $grupos = $gruposModel->map(function ($g) use ($evaluacionService) {
            $alumnosEligibles = $g->alumnos
                ->filter(fn($a) => $a->user && $a->user->hasRole(\App\Models\User::ROLE_ALUMNO) && $a->puedeHacerPracticas())
                ->values();

            $evalData = $evaluacionService->calcularEvaluacionesGrupo($g, $alumnosEligibles);
            $ranking = $evalData['ranking_alumnos'];
            $tieneEvaluaciones = $evalData['tiene_evaluaciones'];

            $alumnosList = $alumnosEligibles->map(function ($a) use ($ranking) {
                $rankItem = $ranking->get($a->id);
                return [
                    'id' => $a->id,
                    'nombre' => $a->user->name,
                    'email' => $a->user->email,
                    'tiene_vehiculo_propio' => (bool) $a->tiene_vehiculo_propio,
                    'posicion' => $rankItem['posicion'] ?? null,
                    'media_puesto' => $rankItem['media_puesto'] ?? null,
                    'total_observaciones' => $rankItem['total_observaciones'] ?? 0,
                    'observaciones' => $rankItem['observaciones'] ?? [],
                ];
            });

            // Si tiene evaluaciones, pre-ordenar por evaluación (menor posición primero, luego alfabético)
            // Si no tiene evaluaciones, pre-ordenar alfabéticamente
            if ($tieneEvaluaciones) {
                $alumnosList = collect($evaluacionService->ordenarLista($alumnosList, 'evaluacion'));
            } else {
                $alumnosList = $alumnosList->sortBy(fn($item) => \App\Support\StrSort::normalize($item['nombre']))->values();
            }

            return [
                'id' => $g->id,
                'nombre' => $g->nombre ?: ('Grupo ' . $g->numero),
                'tiene_evaluaciones' => $tieneEvaluaciones,
                'total_evaluaciones' => $evalData['total_evaluaciones'],
                'alumnos' => $alumnosList->values(),
            ];
        })->values();

        // Alumnos ya asignados o con solicitud previa (solicitud con estado 'aceptado' o 'pendiente')
        $alumnosAsignadosIds = $oferta->solicitudes
            ->whereIn('estado', ['aceptado', 'pendiente'])
            ->pluck('alumno_id')
            ->values()
            ->toArray();

        $grupoSeleccionadoId = $request->input('grupo_id');

        return view('ofertas.enviar', compact('oferta', 'grupos', 'alumnosAsignadosIds', 'grupoSeleccionadoId'));
    }

    /**
     * Guardar la asignación de la oferta a los alumnos seleccionados.
     * Persiste la asignación como SolicitudPractica aceptada, activa la oferta si estaba pendiente
     * y notifica a cada alumno asignado.
     */
    public function enviarAAlumnos(Request $request, OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEnviarOferta($oferta), 403);

        $validated = $request->validate([
            'alumno_ids' => ['required', 'array', 'min:1'],
            'alumno_ids.*' => ['integer', 'exists:alumnos,id'],
            'grupo_id' => ['nullable', 'integer'],
            'redirect_to' => ['nullable', 'string'],
        ], [
            'alumno_ids.required' => 'Selecciona al menos un alumno para asignar a la oferta.',
            'alumno_ids.min' => 'Selecciona al menos un alumno para asignar a la oferta.',
        ]);

        $selectedIds = $validated['alumno_ids'];

        $cursoActual = \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        // Alumnos de los grupos destinatarios que pueden hacer prácticas
        $alumnosGruposIds = Alumno::puedenHacerPracticas()->whereHas('grupos', function($q) use ($oferta, $cursoActual) {
            $q->whereIn('grupos.id', $oferta->grupos->pluck('id'));
            if ($cursoActual) {
                $q->where(function($sub) use ($cursoActual) {
                    $sub->where('alumno_grupo.curso_academico_id', $cursoActual->id)
                        ->orWhereNull('alumno_grupo.curso_academico_id');
                });
            }
        })->pluck('id')->toArray();

        // Alumnos previamente asignados o con solicitud (aceptado o pendiente) para evitar reenviarles correos
        $alumnosYaAsignadosIds = SolicitudPractica::where('oferta_id', $oferta->id)
            ->whereIn('estado', ['aceptado', 'pendiente'])
            ->pluck('alumno_id')
            ->toArray();

        // Retirar a los alumnos que antes estaban asignados pero han sido desmarcados
        if (!empty($alumnosGruposIds)) {
            SolicitudPractica::where('oferta_id', $oferta->id)
                ->whereIn('alumno_id', $alumnosGruposIds)
                ->whereNotIn('alumno_id', $selectedIds)
                ->whereIn('estado', ['aceptado', 'pendiente'])
                ->update(['estado' => 'retirado']);
        }

        $alumnos = Alumno::whereIn('id', $selectedIds)
            ->with('user')
            ->get()
            ->filter(fn($a) => $a->user && $a->user->hasRole(\App\Models\User::ROLE_ALUMNO));

        if ($alumnos->isEmpty()) {
            return back()->withErrors(['alumno_ids' => 'Selecciona al menos un alumno válido.']);
        }

        foreach ($alumnos as $alumno) {
            $esNuevaAsignacion = !in_array($alumno->id, $alumnosYaAsignadosIds);

            SolicitudPractica::updateOrCreate(
                [
                    'oferta_id' => $oferta->id,
                    'alumno_id' => $alumno->id,
                ],
                [
                    'estado' => 'aceptado',
                    'motivo_rechazo' => null,
                ]
            );

            // Notificación de candidatura en proceso de selección solo si es nueva asignación
            if ($esNuevaAsignacion) {
                $this->notificacionService->candidaturaOferta($alumno, $oferta);
            }
        }

        if ($oferta->estado === 'pendiente' || $oferta->estado === 'abierta' || $oferta->estado === 'activa') {
            $oferta->update(['estado' => 'solicitada']);
        }

        $msg = "Se ha asignado correctamente a {$alumnos->count()} alumno(s) a la oferta.";

        if ($request->filled('grupo_id')) {
            return redirect()->route('practicas.grupos.show', $request->grupo_id)->with('success', $msg);
        }

        if ($request->filled('redirect_to') && str_starts_with($request->redirect_to, '/')) {
            return redirect($request->redirect_to)->with('success', $msg);
        }

        return redirect()->route('ofertas.show', $oferta)->with('success', $msg);
    }

    /**
     * Postularse a una oferta (alumno).
     */
    public function postularse(Request $request, OfertaPractica $oferta): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        $alumno = auth()->user()->alumno;
        abort_unless($alumno, 404);

        if (!in_array($oferta->estado, ['abierta', 'activa'])) {
            return back()->withErrors(['error' => 'Esta oferta no está abierta para solicitudes.']);
        }

        $alumnoGrupoIds = $alumno->grupos()->pluck('grupos.id');
        $perteneceAGrupo = $oferta->grupos()->whereIn('grupos.id', $alumnoGrupoIds)->exists();
        if (!$perteneceAGrupo && $oferta->grupos()->exists()) {
            return back()->withErrors(['error' => 'No perteneces a los grupos asignados a esta oferta.']);
        }

        // Verificar si ya tiene una solicitud pendiente/aceptada
        $existing = SolicitudPractica::where('oferta_id', $oferta->id)
            ->where('alumno_id', $alumno->id)
            ->whereIn('estado', ['pendiente', 'aceptado'])
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'Ya tienes una solicitud para esta oferta.']);
        }

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud enviada correctamente.');
    }

    /**
     * Retirar solicitud (alumno).
     */
    public function retirar(Request $request, SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        if ($solicitud->alumno_id !== auth()->user()->alumno->id) {
            abort(403);
        }

        if ($solicitud->estado !== 'pendiente' && $solicitud->estado !== 'aceptado') {
            return back()->withErrors(['error' => 'No puedes retirar esta solicitud.']);
        }

        $solicitud->update(['estado' => 'retirado']);
        return back()->with('success', 'Solicitud retirada.');
    }

    /**
     * Aceptar solicitud (profesor/coordinador).
     */
    public function aceptar(SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_ADMIN]), 403);

        if ($solicitud->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La solicitud no está pendiente.']);
        }

        $solicitud->update(['estado' => 'aceptado']);

        // Notificar al alumno su preselección como candidato en la oferta
        $alumno = $solicitud->alumno;
        $oferta = $solicitud->oferta;
        if ($alumno && $oferta) {
            $this->notificacionService->candidaturaOferta($alumno, $oferta);
        }

        return back()->with('success', 'Solicitud aceptada como candidato preseleccionado.');
    }

    /**
     * Rechazar solicitud (profesor/coordinador).
     */
    public function rechazar(Request $request, SolicitudPractica $solicitud): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_COORDINADOR_DUAL, \App\Models\User::ROLE_ADMIN]), 403);

        if ($solicitud->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'La solicitud no está pendiente.']);
        }

        $solicitud->update([
            'estado' => 'rechazado',
            'motivo_rechazo' => $request->input('motivo_rechazo'),
        ]);
        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * Desasignar un alumno de una oferta de prácticas.
     * Solo permitido para profesorado/coordinador/admin si el estado de la oferta es 'pendiente' o 'solicitada'.
     */
    public function desasignar(Request $request, SolicitudPractica $solicitud): RedirectResponse
    {
        $oferta = $solicitud->oferta;
        abort_unless($this->canEnviarOferta($oferta), 403, 'No tienes permiso para gestionar esta oferta.');

        if (!in_array($oferta->estado, [OfertaPractica::ESTADO_PENDIENTE, OfertaPractica::ESTADO_SOLICITADA])) {
            return back()->withErrors([
                'error' => 'Solo se puede desasignar un alumno si la oferta está en estado pendiente o solicitada.'
            ]);
        }

        // Si el alumno ya tiene una práctica formalmente creada para esta oferta, no se puede desasignar directamente
        if ($oferta->practicas()->where('alumno_id', $solicitud->alumno_id)->exists()) {
            return back()->withErrors([
                'error' => 'No se puede desasignar al alumno porque ya tiene una práctica creada para esta oferta.'
            ]);
        }

        $alumnoNombre = $solicitud->alumno?->user?->name ?? 'el alumno';

        $solicitud->delete();

        // Si la oferta era 'solicitada' y ya no tiene más alumnos asignados/solicitudes activas, revertir a 'pendiente'
        if ($oferta->estado === OfertaPractica::ESTADO_SOLICITADA) {
            $quedanAsignados = $oferta->solicitudes()
                ->whereIn('estado', ['aceptado', 'pendiente'])
                ->exists();

            if (!$quedanAsignados) {
                $oferta->update(['estado' => OfertaPractica::ESTADO_PENDIENTE]);
            }
        }

        return back()->with('success', "Se ha desasignado a {$alumnoNombre} de la oferta correctamente.");
    }

    /**
     * Desasignar un alumno de una oferta de prácticas por modelo Alumno.
     */
    public function desasignarAlumno(Request $request, OfertaPractica $oferta, Alumno $alumno): RedirectResponse
    {
        abort_unless($this->canEnviarOferta($oferta), 403, 'No tienes permiso para gestionar esta oferta.');

        if (!in_array($oferta->estado, [OfertaPractica::ESTADO_PENDIENTE, OfertaPractica::ESTADO_SOLICITADA])) {
            return back()->withErrors([
                'error' => 'Solo se puede desasignar un alumno si la oferta está en estado pendiente o solicitada.'
            ]);
        }

        if ($oferta->practicas()->where('alumno_id', $alumno->id)->exists()) {
            return back()->withErrors([
                'error' => 'No se puede desasignar al alumno porque ya tiene una práctica creada para esta oferta.'
            ]);
        }

        $solicitud = $oferta->solicitudes()->where('alumno_id', $alumno->id)->first();
        if ($solicitud) {
            $solicitud->delete();
        }

        if ($oferta->estado === OfertaPractica::ESTADO_SOLICITADA) {
            $quedanAsignados = $oferta->solicitudes()
                ->whereIn('estado', ['aceptado', 'pendiente'])
                ->exists();

            if (!$quedanAsignados) {
                $oferta->update(['estado' => OfertaPractica::ESTADO_PENDIENTE]);
            }
        }

        $alumnoNombre = $alumno->user?->name ?? 'el alumno';

        return back()->with('success', "Se ha desasignado a {$alumnoNombre} de la oferta correctamente.");
    }

    /**
     * Ver solicitudes de una oferta (profesor/empresa).
     */
    public function solicitudes(OfertaPractica $oferta): View
    {
        abort_unless($this->canEditOferta($oferta), 403);

        $solicitudes = $oferta->solicitudes()->with('alumno.user')->get();

        return view('ofertas.solicitudes', compact('oferta', 'solicitudes'));
    }

    /**
     * Ver ofertas de un alumno.
     */
    public function misOfertas(Request $request): View
    {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ALUMNO), 403);

        $alumno = auth()->user()->alumno;
        abort_unless($alumno, 404);

        $solicitudes = $alumno->solicitudesPracticas()
            ->with('oferta.empresa')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('ofertas.mis-ofertas', compact('solicitudes'));
    }

    /**
     * Grupos activos del curso académico actual.
     */
    private function gruposCursoActual()
    {
        $cursoActual = \App\Models\CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        return Grupo::active()
            ->when($cursoActual, fn($q) => $q->where('curso_academico_id', $cursoActual->id))
            ->with('linea')
            ->orderBy('nombre')
            ->get();
    }

    private function canEditOferta(OfertaPractica $oferta): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // Admin puede editar todo
        if ($user->hasRole(\App\Models\User::ROLE_ADMIN)) {
            return true;
        }

        // Empresa: solo puede editar si la oferta pertenece a su empresa
        if ($user->hasRole(\App\Models\User::ROLE_EMPRESA) && !$user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COORDINADOR_DUAL])) {
            $empresa = $user->empresa;
            if (!$empresa || (int) $oferta->empresa_id !== (int) $empresa->id) {
                return false;
            }
            return $oferta->creador_id === $user->id || $oferta->creador_type === User::ROLE_EMPRESA || $oferta->creador_type === User::class;
        }

        // El creador puede editar
        if ($oferta->creador_id === $user->id && $oferta->creador_type === User::class) {
            return true;
        }

        return false;
    }

    private function canEnviarOferta(OfertaPractica $oferta): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // Usuarios de tipo empresa no asignan alumnos a ofertas
        if ($user->hasRole(User::ROLE_EMPRESA) && ! $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL, User::ROLE_PROFESOR])) {
            return false;
        }

        return $this->canEditOferta($oferta)
            || $user->hasAnyRole([\App\Models\User::ROLE_PROFESOR, \App\Models\User::ROLE_COORDINADOR_DUAL]);
    }

    /**
     * Comprobar si el usuario autenticado tiene permiso para descargar CVs de la oferta.
     */
    private function canDescargarCvs(OfertaPractica $oferta): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Administradores, Profesores y Coordinadores Duales
        if ($user->hasRole(User::ROLE_ADMIN) || $user->hasAnyRole([User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL])) {
            return true;
        }

        // Representante de Empresa: solo y exclusivamente para ofertas de su propia empresa
        if ($user->hasRole(User::ROLE_EMPRESA)) {
            $empresa = $user->empresa;
            return $empresa && (int) $oferta->empresa_id === (int) $empresa->id;
        }

        // Creador de la oferta
        if ($oferta->creador_id === $user->id && $oferta->creador_type === User::class) {
            return true;
        }

        return false;
    }

    /**
     * Descargar CV individual de un alumno en una oferta.
     */
    public function descargarCvAlumno(OfertaPractica $oferta, Alumno $alumno)
    {
        abort_unless($this->canDescargarCvs($oferta), 403);

        $solicitud = $oferta->solicitudes()->where('alumno_id', $alumno->id)->first();
        abort_unless($solicitud, 404);

        $user = $alumno->user;
        if (! $user || ! $user->cv_url || ! Storage::disk('public')->exists($user->cv_url)) {
            return back()->with('warning', 'El alumno no tiene ningún Currículum Vitae subido.');
        }

        $fileName = $alumno->cv_download_filename;

        return Storage::disk('public')->download($user->cv_url, $fileName);
    }

    /**
     * Regenerar el token único de acceso compartido para la empresa.
     */
    public function regenerarToken(OfertaPractica $oferta): RedirectResponse
    {
        abort_unless($this->canEnviarOferta($oferta), 403);

        $oferta->regenerarToken();

        return back()->with('success', 'Se ha generado un nuevo enlace seguro para la empresa. El enlace anterior ha quedado revocado.');
    }

    /**
     * Vista pública de oferta compartida con la empresa mediante token seguro.
     */
    public function compartida(string $token): View
    {
        $oferta = OfertaPractica::where('token', $token)
            ->with(['empresa', 'creador', 'solicitudes.alumno.user', 'grupos.linea.ciclo'])
            ->firstOrFail();

        $totalCvsDisponibles = $oferta->solicitudes->filter(function ($solicitud) {
            $user = $solicitud->alumno?->user;
            return $user && $user->cv_url && Storage::disk('public')->exists($user->cv_url);
        })->count();

        return view('ofertas.compartida', compact('oferta', 'totalCvsDisponibles'));
    }

    /**
     * Descarga de CV de un alumno desde el enlace compartido con token para la empresa.
     */
    public function descargarCvAlumnoCompartida(string $token, Alumno $alumno)
    {
        $oferta = OfertaPractica::where('token', $token)->firstOrFail();

        $solicitud = $oferta->solicitudes()->where('alumno_id', $alumno->id)->first();
        abort_unless($solicitud, 404);

        $user = $alumno->user;
        if (! $user || ! $user->cv_url || ! Storage::disk('public')->exists($user->cv_url)) {
            return back()->with('warning', 'El alumno no tiene ningún Currículum Vitae subido.');
        }

        $fileName = $alumno->cv_download_filename;

        return Storage::disk('public')->download($user->cv_url, $fileName);
    }

    /**
     * Descarga de todos los CVs en ZIP desde el enlace compartido con token para la empresa.
     */
    public function descargarCvsZipCompartida(string $token)
    {
        $oferta = OfertaPractica::where('token', $token)
            ->with(['solicitudes.alumno.user', 'empresa'])
            ->firstOrFail();

        return $this->generarZipCvs($oferta);
    }

    /**
     * Descargar todos los CVs de los alumnos de la oferta en un archivo ZIP (para usuarios autenticados).
     */
    public function descargarCvsZip(OfertaPractica $oferta)
    {
        abort_unless($this->canDescargarCvs($oferta), 403);

        $oferta->load(['solicitudes.alumno.user', 'empresa']);

        return $this->generarZipCvs($oferta);
    }

    /**
     * Helper privado para generar y servir el archivo ZIP con los CVs de la oferta.
     */
    private function generarZipCvs(OfertaPractica $oferta)
    {
        $archivosCv = [];
        foreach ($oferta->solicitudes as $solicitud) {
            $alumno = $solicitud->alumno;
            $user = $alumno?->user;
            if ($user && $user->cv_url && Storage::disk('public')->exists($user->cv_url)) {
                $realPath = Storage::disk('public')->path($user->cv_url);
                $extension = strtolower(pathinfo($user->cv_url, PATHINFO_EXTENSION)) ?: 'pdf';
                $nombreAlumno = trim(preg_replace('/\s+/', ' ', str_replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'], ' ', $user->name ?: ('alumno_' . $alumno->id))));
                $baseFileName = $alumno->cv_download_filename;

                // Evitar colisiones de nombres de archivo
                $fileName = $baseFileName;
                $counter = 1;
                while (isset($archivosCv[$fileName])) {
                    $fileName = "CV IESHLANZ - {$nombreAlumno} ({$counter}).{$extension}";
                    $counter++;
                }

                $archivosCv[$fileName] = $realPath;
            }
        }

        if (empty($archivosCv)) {
            return back()->with('warning', 'Ninguno de los alumnos asignados o postulados a esta oferta tiene un CV disponible para descargar.');
        }

        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'cvs_oferta_');
        $empresaNombre = Str::slug($oferta->empresa?->nombre ?: 'empresa', '_');
        $especialidad = Str::slug($oferta->especialidad_requerida ?: 'oferta', '_');
        $zipFileName = "CVs_{$empresaNombre}_{$especialidad}.zip";

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['error' => 'No se pudo generar el archivo comprimido ZIP.']);
        }

        foreach ($archivosCv as $localName => $filePath) {
            $zip->addFile($filePath, $localName);
        }

        $zip->close();

        return response()->download($tempFile, $zipFileName)->deleteFileAfterSend(true);
    }
}