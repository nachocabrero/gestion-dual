<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar consentimiento RGPD.
 * RGPD: Art. 6.1.a - Consentimiento para tratamiento de datos.
 * Los usuarios deben aceptar la política de privacidad antes de usar la app.
 */
class CheckRgpdConsent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Si no ha consentido, redirigir a página de aceptación
        if (! $user->hasConsentedRgpd()) {
            return redirect()->route('rgpd.consent');
        }

        return $next($request);
    }
}