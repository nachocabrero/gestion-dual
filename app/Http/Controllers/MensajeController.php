<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Mensaje;
use App\Models\Notificacion;
use App\Models\Profesor;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MensajeController extends Controller
{
    public function __construct(protected NotificacionService $notificacionService) {}

    /**
     * Historial de comunicados enviados (solo admin y coordinador dual).
     */
    public function index(Request $request): View
    {
        Gate::authorize('enviar-mensajes');

        $mensajes = Mensaje::with(['remitente'])
            ->withCount([
                'destinatarios as total_destinatarios_count',
                'destinatarios as leidos_count' => function ($query) {
                    $query->whereNotNull('mensaje_destinatarios.leido_at');
                },
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('mensajes.index', compact('mensajes'));
    }

    /**
     * Formulario de redacción de nuevo mensaje / comunicado.
     */
    public function create(): View
    {
        Gate::authorize('enviar-mensajes');

        // Grupos activos con recuento de alumnos
        $grupos = Grupo::with(['alumnos.user' => function ($q) {
                $q->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('nombre')
            ->get();

        // Ciclos formativos activos
        $ciclos = Ciclo::with(['familia'])
            ->orderBy('nombre')
            ->get();

        // Profesores que tienen empresas asignadas para seguimiento
        $profesoresConEmpresas = Profesor::with(['user', 'empresas' => function ($q) {
                $q->where('is_active', true)->orderBy('nombre');
            }])
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->whereHas('empresas', fn($q) => $q->where('is_active', true))
            ->get()
            ->sortBy('user.name')
            ->values();

        // Empresas sin profesor asignado
        $empresasSinProfesor = Empresa::whereNull('profesor_id')
            ->where('is_active', true)
            ->orderBy('nombre')
            ->get();

        // Preparar datos JSON para selección interactiva de alumnos por grupo en Alpine.js
        $gruposData = $grupos->map(function ($grupo) {
            return [
                'id' => $grupo->id,
                'nombre' => $grupo->nombre,
                'alumnos' => $grupo->alumnos->filter(function ($a) {
                    return $a->user && $a->user->is_active;
                })->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'name' => $a->user->name,
                        'email' => $a->user->email,
                    ];
                })->values(),
            ];
        });

        // Preparar datos JSON de empresas para selección en Alpine.js
        $profesoresEmpresasData = $profesoresConEmpresas->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->user->name ?? ('Profesor #' . $p->id),
                'email' => $p->user->email ?? '',
                'empresas' => $p->empresas->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'nombre' => $e->nombre,
                        'cif' => $e->cif,
                        'email' => $e->email,
                    ];
                })->values(),
            ];
        })->values();

        $empresasSinProfesorData = $empresasSinProfesor->map(function ($e) {
            return [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'cif' => $e->cif,
                'email' => $e->email,
            ];
        })->values();

        return view('mensajes.create', compact(
            'grupos',
            'ciclos',
            'gruposData',
            'profesoresConEmpresas',
            'empresasSinProfesor',
            'profesoresEmpresasData',
            'empresasSinProfesorData'
        ));
    }

    /**
     * Almacenar y enviar el mensaje a los destinatarios.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('enviar-mensajes');

        $request->validate([
            'asunto' => 'required|string|max:255',
            'cuerpo' => 'required|string|min:5',
            'tipo_destinatario' => 'required|in:grupos,alumnos_grupo,profesores_ciclos,empresas',
            'grupo_ids' => 'required_if:tipo_destinatario,grupos|array',
            'grupo_ids.*' => 'exists:grupos,id',
            'grupo_id' => 'required_if:tipo_destinatario,alumnos_grupo|nullable|exists:grupos,id',
            'alumno_ids' => 'required_if:tipo_destinatario,alumnos_grupo|array',
            'alumno_ids.*' => 'exists:alumnos,id',
            'ciclo_ids' => 'required_if:tipo_destinatario,profesores_ciclos|array',
            'ciclo_ids.*' => 'exists:ciclos,id',
            'profesor_ids' => 'nullable|array',
            'profesor_ids.*' => 'exists:profesores,id',
            'sin_profesor' => 'nullable|boolean',
            'empresa_ids' => 'nullable|array',
            'empresa_ids.*' => 'exists:empresas,id',
        ], [
            'asunto.required' => 'El asunto del mensaje es obligatorio.',
            'cuerpo.required' => 'El cuerpo del mensaje no puede estar vacío.',
            'grupo_ids.required_if' => 'Debes seleccionar al menos un grupo.',
            'grupo_id.required_if' => 'Debes indicar el grupo para seleccionar a los alumnos.',
            'alumno_ids.required_if' => 'Debes seleccionar al menos un alumno del grupo.',
            'ciclo_ids.required_if' => 'Debes seleccionar al menos un ciclo formativo.',
        ]);

        if ($request->tipo_destinatario === 'empresas') {
            $hasProf = !empty($request->profesor_ids);
            $hasSinProf = $request->boolean('sin_profesor');
            $hasEmpresas = !empty($request->empresa_ids);

            if (!$hasProf && !$hasSinProf && !$hasEmpresas) {
                return back()->withInput()->withErrors([
                    'tipo_destinatario' => 'Debes seleccionar al menos un profesor de seguimiento, marcar las empresas sin profesor asignado o seleccionar empresas.',
                ]);
            }
        }

        $recipientUsers = collect();
        $resumen = '';

        if ($request->tipo_destinatario === 'grupos') {
            $grupoIds = (array) $request->grupo_ids;
            $alumnos = Alumno::whereHas('grupos', fn($q) => $q->whereIn('grupos.id', $grupoIds))
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->with('user')
                ->get();

            $recipientUsers = $alumnos->pluck('user')->filter()->unique('id');
            $nombresGrupos = Grupo::whereIn('id', $grupoIds)->pluck('nombre')->implode(', ');
            $resumen = 'Grupos completos: ' . $nombresGrupos;
        } elseif ($request->tipo_destinatario === 'alumnos_grupo') {
            $alumnoIds = (array) $request->alumno_ids;
            $alumnos = Alumno::whereIn('id', $alumnoIds)
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->with('user')
                ->get();

            $recipientUsers = $alumnos->pluck('user')->filter()->unique('id');
            $grupo = Grupo::find($request->grupo_id);
            $resumen = $recipientUsers->count() . ' alumno(s) de ' . ($grupo?->nombre ?? 'grupo');
        } elseif ($request->tipo_destinatario === 'profesores_ciclos') {
            $cicloIds = (array) $request->ciclo_ids;
            $familiaIds = Ciclo::whereIn('id', $cicloIds)->pluck('familia_id')->filter();

            $profesores = Profesor::query()
                ->where(function ($query) use ($cicloIds, $familiaIds) {
                    $query->whereHas('asignaturas', fn($q) => $q->whereIn('ciclo_id', $cicloIds))
                        ->orWhereHas('gruposImpartidos.linea', fn($q) => $q->whereIn('ciclo_id', $cicloIds))
                        ->orWhereHas('gruposTutor.linea', fn($q) => $q->whereIn('ciclo_id', $cicloIds));
                    if ($familiaIds->isNotEmpty()) {
                        $query->orWhereIn('familia_id', $familiaIds);
                    }
                })
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->with('user')
                ->get();

            $recipientUsers = $profesores->pluck('user')->filter()->unique('id');
            $nombresCiclos = Ciclo::whereIn('id', $cicloIds)->pluck('nombre')->implode(', ');
            $resumen = 'Profesorado de ciclos: ' . $nombresCiclos;
        } elseif ($request->tipo_destinatario === 'empresas') {
            $empresaIds = (array) $request->empresa_ids;
            $profesorIds = (array) $request->profesor_ids;
            $incluirSinProfesor = $request->boolean('sin_profesor');

            if (!empty($empresaIds)) {
                $empresas = Empresa::whereIn('id', $empresaIds)
                    ->where('is_active', true)
                    ->with(['profesor.user', 'tutoresLaborales'])
                    ->get();
            } else {
                $query = Empresa::where('is_active', true);
                $query->where(function ($q) use ($profesorIds, $incluirSinProfesor) {
                    if (!empty($profesorIds)) {
                        $q->whereIn('profesor_id', $profesorIds);
                    }
                    if ($incluirSinProfesor) {
                        $q->orWhereNull('profesor_id');
                    }
                });
                $empresas = $query->with(['profesor.user', 'tutoresLaborales'])->get();
            }

            foreach ($empresas as $empresa) {
                $email = $empresa->email ?: $empresa->tutoresLaborales->firstWhere('email', '!=', null)?->email;
                if ($email) {
                    $user = User::firstOrCreate(
                        ['email' => strtolower(trim($email))],
                        [
                            'name' => $empresa->nombre,
                            'password' => bcrypt(\Illuminate\Support\Str::random(32)),
                            'roles' => [User::ROLE_EMPRESA],
                            'is_active' => true,
                            'registration_status' => User::STATUS_APPROVED,
                            'consent_rgpd' => true,
                            'consent_rgpd_at' => now(),
                        ]
                    );
                    $recipientUsers->push($user);
                }
            }
            $recipientUsers = $recipientUsers->unique('id');

            $partesResumen = [];
            if (!empty($profesorIds)) {
                $nombresProfs = Profesor::whereIn('id', $profesorIds)->with('user')->get()->pluck('user.name')->filter()->implode(', ');
                if ($nombresProfs) {
                    $partesResumen[] = 'Seguimiento: ' . $nombresProfs;
                }
            }
            if ($incluirSinProfesor) {
                $partesResumen[] = 'Sin profesor asignado';
            }
            if (empty($partesResumen) && $empresas->isNotEmpty()) {
                $partesResumen[] = $empresas->count() . ' empresa(s)';
            }
            $resumen = 'Empresas (' . implode(' + ', $partesResumen) . '): ' . $empresas->count() . ' entidad(es)';
        }

        if ($recipientUsers->isEmpty()) {
            if ($request->tipo_destinatario === 'empresas') {
                return back()->withInput()->with('error', 'Ninguna de las empresas seleccionadas dispone de un correo electrónico válido de contacto para recibir el comunicado.');
            }
            return back()->withInput()->with('error', 'No se encontraron destinatarios activos para la selección especificada.');
        }

        // Crear el mensaje en la base de datos
        $mensaje = Mensaje::create([
            'remitente_id' => auth()->id(),
            'asunto' => $request->asunto,
            'cuerpo' => $request->cuerpo,
            'tipo_destinatario' => $request->tipo_destinatario,
            'resumen_destinatarios' => $resumen,
            'total_destinatarios' => $recipientUsers->count(),
        ]);

        // Asociar a la tabla pivote de destinatarios
        $syncData = [];
        foreach ($recipientUsers as $user) {
            $syncData[$user->id] = [
                'leido_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $mensaje->destinatarios()->sync($syncData);

        // Notificar a cada destinatario in-app y por correo electrónico SMTP
        $enlace = route('mensajes.show', $mensaje);
        $remitenteNombre = auth()->user()->name;
        $asuntoEmail = 'Nueva notificación de Gestión DUAL H.Lanz: ' . $mensaje->asunto;

        foreach ($recipientUsers as $user) {
            $this->notificacionService->enviar(
                $user->id,
                'mensaje_recibido',
                $mensaje->asunto,
                $mensaje->cuerpo,
                $enlace,
                [
                    'mensaje_id' => $mensaje->id,
                    'remitente' => $remitenteNombre,
                ],
                60,
                $remitenteNombre,
                $asuntoEmail
            );
        }

        return redirect()->route('mensajes.show', $mensaje)->with('success', "Mensaje enviado correctamente a {$recipientUsers->count()} destinatario(s).");
    }

    /**
     * Ver un mensaje concreto.
     * Si el usuario es destinatario, se marca como leído.
     */
    public function show(Mensaje $mensaje): View
    {
        Gate::authorize('ver-mensaje', $mensaje);

        $currentUser = auth()->user();

        // Si el usuario actual es uno de los destinatarios y no lo ha leído, marcar como leído
        $pivot = $mensaje->destinatarios()->where('users.id', $currentUser->id)->first();
        if ($pivot && $pivot->pivot->leido_at === null) {
            $mensaje->marcarLeidoPor($currentUser->id);

            // Marcar también la notificación in-app correspondiente como leída
            Notificacion::where('usuario_id', $currentUser->id)
                ->where('tipo', 'mensaje_recibido')
                ->where(function ($q) use ($mensaje) {
                    $q->where('enlace', route('mensajes.show', $mensaje))
                      ->orWhereJsonContains('datos->mensaje_id', $mensaje->id);
                })
                ->where('es_leida', false)
                ->update(['es_leida' => true]);
        }

        $mensaje->load(['remitente', 'destinatarios' => function ($q) {
            $q->orderBy('name');
        }]);

        $esAdminOCoordinador = $currentUser->hasRole(User::ROLE_ADMIN)
            || $currentUser->hasRole(User::ROLE_COORDINADOR_DUAL)
            || (bool) $currentUser->profesor?->es_coordinador_dual;

        return view('mensajes.show', compact('mensaje', 'esAdminOCoordinador'));
    }
}
