<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Services\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function __construct(protected NotificacionService $notificacionService) {}

    /**
     * Lista de notificaciones del usuario actual.
     */
    public function index(Request $request): View
    {
        $usuario = $request->user();

        // Opcional: si se pasa ?marcar_todas=1 se marcan todas
        if ($request->boolean('marcar_todas')) {
            $this->notificacionService->marcarTodasLeidas($usuario->id);
        }

        $notificaciones = Notificacion::where('usuario_id', $usuario->id)
            ->where(function ($q) {
                $q->whereNull('expira_en')
                  ->orWhere('expira_en', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('notificaciones.index', compact('notificaciones'));
    }

    /**
     * Abrir una notificación, marcarla como leída automáticamente y redirigir a su enlace.
     */
    public function abrir(Request $request, Notificacion $notificacion): RedirectResponse
    {
        // Control de acceso: solo el propio usuario destinatario
        if ($notificacion->usuario_id !== $request->user()->id) {
            abort(403, 'No autorizado para acceder a esta notificación.');
        }

        if (!$notificacion->es_leida) {
            $notificacion->marcarLeida();

            // Si es un comunicado oficial de mensajería, marcar también como leído en mensaje_destinatarios
            if ($notificacion->tipo === 'mensaje_recibido') {
                $mensajeId = $notificacion->datos['mensaje_id'] ?? null;
                if ($mensajeId) {
                    $mensaje = \App\Models\Mensaje::find($mensajeId);
                    $mensaje?->marcarLeidoPor($request->user()->id);
                }
            }
        }

        if ($notificacion->enlace) {
            return redirect($notificacion->enlace);
        }

        return redirect()->route('notificaciones.index');
    }

    /**
     * Marcar una notificación individual como leída.
     */
    public function marcarLeida(Request $request, Notificacion $notificacion): RedirectResponse|JsonResponse
    {
        // Control de acceso: solo el propio usuario destinatario
        if ($notificacion->usuario_id !== $request->user()->id) {
            abort(403, 'No autorizado para modificar esta notificación.');
        }

        $notificacion->marcarLeida();

        // Si es un comunicado oficial de mensajería, marcar también como leído en mensaje_destinatarios
        if ($notificacion->tipo === 'mensaje_recibido') {
            $mensajeId = $notificacion->datos['mensaje_id'] ?? null;
            if ($mensajeId) {
                $mensaje = \App\Models\Mensaje::find($mensajeId);
                $mensaje?->marcarLeidoPor($request->user()->id);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'no_leidas' => $this->notificacionService->contarNoLeidas($request->user()->id),
            ]);
        }

        return back()->with('success', 'Aviso marcado como leído.');
    }

    /**
     * Marcar todas las notificaciones del usuario como leídas.
     */
    public function marcarTodas(Request $request): RedirectResponse|JsonResponse
    {
        $this->notificacionService->marcarTodasLeidas($request->user()->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'no_leidas' => 0,
            ]);
        }

        return back()->with('success', 'Todas las notificaciones se han marcado como leídas.');
    }

    /**
     * API: obtener contador de no leídas.
     */
    public function contador(Request $request): JsonResponse
    {
        $count = $this->notificacionService->contarNoLeidas($request->user()->id);
        return response()->json(['count' => $count]);
    }
}