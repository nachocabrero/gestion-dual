<?php

namespace App\Http\Controllers;

use App\Services\NotificacionService;
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

        // Marcar todas como leídas al ver la lista
        $this->notificacionService->marcarTodasLeidas($usuario->id);

        $notificaciones = \App\Models\Notificacion::where('usuario_id', $usuario->id)
            ->where(function ($q) {
                $q->whereNull('expira_en')
                  ->orWhere('expira_en', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notificaciones.index', compact('notificaciones'));
    }

    /**
     * API: obtener contador de no leídas.
     */
    public function contador(Request $request): \Illuminate\Http\JsonResponse
    {
        $count = $this->notificacionService->contarNoLeidas($request->user()->id);
        return response()->json(['count' => $count]);
    }
}