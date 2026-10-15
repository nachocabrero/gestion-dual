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
            $cvFile = $request->file('cv');
            $ext = strtolower($cvFile->getClientOriginalExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $cvPath = $this->optimizeAndStoreDocumentImage($cvFile);
            } else {
                $cvPath = $cvFile->store('cvs', 'public');
            }
            $validated['cv_url'] = $cvPath;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->alumno) {
            $alumnoData = [
                'portfolio_url' => $user->portfolio_url,
                'linkedin_url' => $user->linkedin_url,
                'telefono' => $user->telefono,
            ];

            if ($request->has('empresa_practicas_primero')) {
                $alumnoData['empresa_practicas_primero'] = $validated['empresa_practicas_primero'] ?? null;
            }

            if ($request->has('horas_practicas_primero')) {
                $alumnoData['horas_practicas_primero'] = isset($validated['horas_practicas_primero']) && $validated['horas_practicas_primero'] !== ''
                    ? (int) $validated['horas_practicas_primero']
                    : null;
            }

            $user->alumno->update($alumnoData);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Optimiza el avatar antes de guardarlo.
     */
    protected function optimizeAndStoreAvatar($file): string
    {
        try {
            $tempPath = $file->getRealPath();
            $imgInfo = @getimagesize($tempPath);
            if (!$imgInfo) {
                return $file->store('avatars', 'public');
            }

            $width = $imgInfo[0];
            $height = $imgInfo[1];
            $mime = $imgInfo['mime'] ?? '';
            $extension = strtolower($file->getClientOriginalExtension());

            // Aumentar límite de memoria temporalmente para redimensionar imágenes de alta resolución
            @ini_set('memory_limit', '256M');

            $src = null;
            if (($mime === 'image/jpeg' || in_array($extension, ['jpg', 'jpeg'])) && function_exists('imagecreatefromjpeg')) {
                $src = @imagecreatefromjpeg($tempPath);
            } elseif (($mime === 'image/png' || $extension === 'png') && function_exists('imagecreatefrompng')) {
                $src = @imagecreatefrompng($tempPath);
            } elseif (($mime === 'image/webp' || $extension === 'webp') && function_exists('imagecreatefromwebp')) {
                $src = @imagecreatefromwebp($tempPath);
            }

            // Si GD no tiene soporte (ej: falta webp en el entorno) o falló al cargar, guardar original directamente
            if (!$src) {
                return $file->store('avatars', 'public');
            }

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
                if (in_array($extension, ['png', 'webp']) || in_array($mime, ['image/png', 'image/webp'])) {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                }
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($src);
                $src = $dst;
            }

            ob_start();
            if (function_exists('imagewebp')) {
                imagewebp($src, null, 80);
                $imageData = ob_get_clean();
                imagedestroy($src);
                $fileName = uniqid() . '.webp';
            } elseif (function_exists('imagejpeg')) {
                imagejpeg($src, null, 80);
                $imageData = ob_get_clean();
                imagedestroy($src);
                $fileName = uniqid() . '.jpg';
            } else {
                ob_end_clean();
                imagedestroy($src);
                return $file->store('avatars', 'public');
            }

            if (!$imageData) {
                return $file->store('avatars', 'public');
            }

            $path = 'avatars/' . $fileName;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);

            return $path;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error al procesar avatar, guardando archivo original: ' . $e->getMessage());
            return $file->store('avatars', 'public');
        }
    }

    /**
     * Optimiza y guarda una imagen subida como CV o documento.
     */
    protected function optimizeAndStoreDocumentImage($file): string
    {
        try {
            $tempPath = $file->getRealPath();
            $imgInfo = @getimagesize($tempPath);
            if (!$imgInfo) {
                return $file->store('cvs', 'public');
            }

            $width = $imgInfo[0];
            $height = $imgInfo[1];
            $mime = $imgInfo['mime'] ?? '';
            $extension = strtolower($file->getClientOriginalExtension());

            @ini_set('memory_limit', '256M');

            $src = null;
            if (($mime === 'image/jpeg' || in_array($extension, ['jpg', 'jpeg'])) && function_exists('imagecreatefromjpeg')) {
                $src = @imagecreatefromjpeg($tempPath);
            } elseif (($mime === 'image/png' || $extension === 'png') && function_exists('imagecreatefrompng')) {
                $src = @imagecreatefrompng($tempPath);
            } elseif (($mime === 'image/webp' || $extension === 'webp') && function_exists('imagecreatefromwebp')) {
                $src = @imagecreatefromwebp($tempPath);
            }

            if (!$src) {
                return $file->store('cvs', 'public');
            }

            $maxDim = 1920; // Tamaño legible para documentos o diplomas
            if ($width > $maxDim || $height > $maxDim) {
                if ($width > $height) {
                    $newWidth = $maxDim;
                    $newHeight = (int) ($height * ($maxDim / $width));
                } else {
                    $newHeight = $maxDim;
                    $newWidth = (int) ($width * ($maxDim / $height));
                }

                $dst = imagecreatetruecolor($newWidth, $newHeight);
                if (in_array($extension, ['png', 'webp']) || in_array($mime, ['image/png', 'image/webp'])) {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                }
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($src);
                $src = $dst;
            }

            ob_start();
            if (function_exists('imagewebp')) {
                imagewebp($src, null, 85);
                $imageData = ob_get_clean();
                imagedestroy($src);
                $fileName = uniqid('cv_') . '.webp';
            } elseif (function_exists('imagejpeg')) {
                imagejpeg($src, null, 85);
                $imageData = ob_get_clean();
                imagedestroy($src);
                $fileName = uniqid('cv_') . '.jpg';
            } else {
                ob_end_clean();
                imagedestroy($src);
                return $file->store('cvs', 'public');
            }

            if (!$imageData) {
                return $file->store('cvs', 'public');
            }

            $path = 'cvs/' . $fileName;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);

            return $path;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error al procesar documento imagen, guardando archivo original: ' . $e->getMessage());
            return $file->store('cvs', 'public');
        }
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