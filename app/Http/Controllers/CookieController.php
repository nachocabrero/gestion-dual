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
        } else {
            session(['cookie_consent' => true]);
        }

        $cookie = cookie('cookie_consent', 'true', 90 * 24 * 60);

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
        } else {
            session(['cookie_consent' => true]);
        }

        $cookie = cookie('cookie_consent', 'true', 90 * 24 * 60);

        if (request()->expectsJson()) {
            return response()->json(['success' => true])->withCookie($cookie);
        }

        return back()->with('success', 'Preferencias de cookies guardadas.')->withCookie($cookie);
    }
}