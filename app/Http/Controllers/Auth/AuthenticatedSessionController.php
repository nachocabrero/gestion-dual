<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        if ($user) {
            // Si el usuario ya aceptó cookies previamente, tiene RGPD aceptado o traía la cookie/sesión, guardar consent_cookies_at
            if (!empty($user->consent_cookies_at) || !empty($user->consent_rgpd_at) || (bool) $user->consent_rgpd || $request->cookie('cookie_consent') === 'true' || $request->session()->get('cookie_consent')) {
                if (empty($user->consent_cookies_at)) {
                    $user->update(['consent_cookies_at' => now()]);
                }
                session(['cookie_consent' => true]);
            }
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
