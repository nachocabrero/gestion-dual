<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RgpdController extends Controller
{
    /**
     * Aceptar el aviso de privacidad.
     * RGPD: Art. 7 - Condiciones del consentimiento.
     */
    public function accept(): RedirectResponse
    {
        $user = Auth::user();

        if ($user && ! $user->hasConsentedRgpd()) {
            $user->update([
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ]);
        }

        return redirect()->route('dashboard');
    }
}