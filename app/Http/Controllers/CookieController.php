<?php

namespace App\Http\Controllers;

class CookieController extends Controller
{
    /**
     * Mostrar la política de cookies.
     */
    public function index()
    {
        return view('cookies');
    }

    /**
     * Registrar el consentimiento de cookies.
     */
    public function accept()
    {
        if (auth()->check()) {
            auth()->user()->update([
                'consent_cookies_at' => now(),
            ]);
        }
        session(['cookie_consent' => true]);

        // Cookie accesible tanto por JavaScript como por el servidor (httpOnly = false) por 1 año
        $cookie = cookie('cookie_consent', 'true', 365 * 24 * 60, null, null, false, false);

        if (request()->expectsJson()) {
            return response()->json(['success' => true])->withCookie($cookie);
        }

        return back()->with('success', 'Preferencias de cookies guardadas.')->withCookie($cookie);
    }

    /**
     * Rechazar cookies no esenciales.
     */
    public function reject()
    {
        if (auth()->check()) {
            auth()->user()->update([
                'consent_cookies_at' => now(),
            ]);
        }
        session(['cookie_consent' => true]);

        // Las necesarias también constituyen consentimiento para no volver a mostrar el aviso
        $cookie = cookie('cookie_consent', 'true', 365 * 24 * 60, null, null, false, false);

        if (request()->expectsJson()) {
            return response()->json(['success' => true])->withCookie($cookie);
        }

        return back()->with('success', 'Preferencias de cookies guardadas.')->withCookie($cookie);
    }
}