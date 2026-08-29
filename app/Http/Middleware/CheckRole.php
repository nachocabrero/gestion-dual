<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar roles de usuario.
 * Ejemplo de uso: Route::get('/admin', ...)->middleware('role:admin');
 * Route::get('/dashboard', ...)->middleware('role:profesor,coordinador_dual');
 */
class CheckRole
{
    /**
     * Manejar una solicitud entrante.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si no se especifican roles, cualquier usuario autenticado pasa
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar que el usuario tiene al menos uno de los roles requeridos
        $hasRole = collect($roles)->contains(fn($role) => $user->hasRole($role));

        if (! $hasRole) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}