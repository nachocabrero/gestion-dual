<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar que el usuario está activo.
 * RGPD: Art. 18 - Derecho de limitación del tratamiento.
 * Un usuario desactivado no puede acceder a ningún dato.
 */
class CheckUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Si el usuario ha solicitado eliminación de datos, redirigir a explicación
        if ($user->isDeletionRequested()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu solicitud de eliminación de datos está en proceso.');
        }

        // Si el usuario está desactivado, cerrar sesión
        // Excepción: permitir acceso a rutas de admin para que el admin pueda gestionar usuarios
        if (! $user->isActive() && ! $request->is('admin/*')) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada. Contacta con el administrador.');
        }

        return $next($request);
    }
}