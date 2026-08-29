<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     * RGPD: El usuario puede actualizar sus datos (Art. 16 - Rectificación).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Solicitar la eliminación de datos personales.
     * RGPD: Art. 17 - Derecho de supresión ("derecho al olvido").
     */
    public function requestDeletion(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->update([
            'data_deletion_requested_at' => now(),
        ]);

        Auth::logout();

        return redirect()->route('login')->with('status', 'deletion-requested');
    }

    /**
     * Delete the user's account with permanent data removal.
     * RGPD: Art. 17 - Supresión definitiva tras confirmación.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Soft delete: marcar como inactivo
        $user->update(['is_active' => false]);

        // Eliminar datos personales (no el registro de auditoría)
        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}