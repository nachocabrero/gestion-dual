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

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar_url) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar_url);
            }
            $validated['avatar_url'] = $this->optimizeAndStoreAvatar($request->file('avatar'));
        }

        // Handle CV Upload
        if ($request->hasFile('cv')) {
            if ($user->cv_url) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->cv_url);
            }
            $cvPath = $request->file('cv')->store('cvs', 'public');
            $validated['cv_url'] = $cvPath;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Optimiza el avatar antes de guardarlo.
     */
    protected function optimizeAndStoreAvatar($file): string
    {
        $tempPath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        list($width, $height) = getimagesize($tempPath);

        switch ($extension) {
            case 'jpeg':
            case 'jpg': $src = imagecreatefromjpeg($tempPath); break;
            case 'png': $src = imagecreatefrompng($tempPath); break;
            case 'webp': $src = imagecreatefromwebp($tempPath); break;
            default: return $file->store('avatars', 'public');
        }

        if (!$src) return $file->store('avatars', 'public');

        $maxDim = 400; // Avatares no necesitan ser enormes
        if ($width > $maxDim || $height > $maxDim) {
            if ($width > $height) {
                $newWidth = $maxDim;
                $newHeight = (int) ($height * ($maxDim / $width));
            } else {
                $newHeight = $maxDim;
                $newWidth = (int) ($width * ($maxDim / $height));
            }

            $dst = imagecreatetruecolor($newWidth, $newHeight);
            if ($extension === 'png' || $extension === 'webp') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($src);
            $src = $dst;
        }

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($src, null, 80); // Convertir siempre a WebP para máxima optimización
            $imageData = ob_get_clean();
            imagedestroy($src);
            $fileName = uniqid() . '.webp';
        } else {
            imagejpeg($src, null, 80); // Fallback a JPEG si WebP no está soportado
            $imageData = ob_get_clean();
            imagedestroy($src);
            $fileName = uniqid() . '.jpg';
        }

        $path = 'avatars/' . $fileName;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);

        return $path;
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