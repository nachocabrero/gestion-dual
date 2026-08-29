<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\RgpdController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página principal
Route::get('/', function () {
    return view('welcome');
});

// Rutas públicas (sin autenticación)
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/rgpd/consent', function () {
    return view('rgpd.consent');
})->name('rgpd.consent');

Route::post('/rgpd/accept', [RgpdController::class, 'accept'])->name('rgpd.accept')->middleware('auth');

// Autenticación (Breeze)
require __DIR__ . '/auth.php';

// Dashboard (requiere auth + activo + RGPD consent)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'active', 'rgpd', 'verified'])->name('dashboard');

// Perfil (requiere auth + activo + RGPD)
Route::middleware(['auth', 'active', 'rgpd'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/deletion-request', [ProfileController::class, 'requestDeletion'])->name('profile.deletion-request');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Notificaciones (requiere auth + activo + RGPD)
Route::middleware(['auth', 'active', 'rgpd'])->prefix('notificaciones')->name('notificaciones.')->group(function () {
    Route::get('/', [NotificacionController::class, 'index'])->name('index');
    Route::get('/contador', [NotificacionController::class, 'contador'])->name('contador');
});

// Alumnos (requiere auth + RGPD, NO active para permitir reactivar)
Route::middleware(['auth', 'rgpd'])->prefix('alumnos')->name('alumnos.')->group(function () {
    Route::get('/', [AlumnoController::class, 'index'])->name('index')->middleware('active');
    Route::get('/create', [AlumnoController::class, 'create'])->name('create')->middleware('can:create-alumno');
    Route::post('/', [AlumnoController::class, 'store'])->name('store')->middleware('can:create-alumno');
    Route::get('/{alumno}', [AlumnoController::class, 'show'])->name('show')->middleware('can:view-alumno,alumno');
    Route::get('/{alumno}/edit', [AlumnoController::class, 'edit'])->name('edit')->middleware('can:update-alumno,alumno');
    Route::put('/{alumno}', [AlumnoController::class, 'update'])->name('update')->middleware('can:update-alumno,alumno');
    Route::post('/{alumno}/deactivate', [AlumnoController::class, 'deactivate'])->name('deactivate')->middleware('can:deactivate-alumno');
    Route::post('/{alumno}/reactivate', function (\App\Models\Alumno $alumno) {
        abort_unless(auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN) || auth()->user()->hasRole(\App\Models\User::ROLE_COORDINADOR_DUAL), 403);
        $alumno->user->update(['is_active' => true]);
        return redirect()->route('alumnos.index')->with('success', 'Alumno reactivado.');
    })->name('reactivate');
    Route::delete('/{alumno}', [AlumnoController::class, 'destroy'])->name('destroy')->middleware('can:delete-alumno');
});

// Calificaciones (Admin, Coordinador Dual, Profesor)
Route::middleware(['auth', 'active', 'rgpd'])->prefix('calificaciones')->name('calificaciones.')->group(function () {
    Route::get('/', [CalificacionController::class, 'index'])->name('index');
    Route::get('/create', [CalificacionController::class, 'create'])->name('create')->middleware('can:create-alumno');
    Route::post('/', [CalificacionController::class, 'store'])->name('store')->middleware('can:create-alumno');
    Route::get('/{calificacion}/edit', [CalificacionController::class, 'edit'])->name('edit')->middleware('can:update-alumno,calificacion.alumno');
    Route::put('/{calificacion}', [CalificacionController::class, 'update'])->name('update')->middleware('can:update-alumno,calificacion.alumno');
    Route::delete('/{calificacion}', [CalificacionController::class, 'destroy'])->name('destroy')->middleware('can:delete-alumno');
    Route::get('/{alumno}', [CalificacionController::class, 'show'])->name('show')->middleware('can:view-alumno,alumno');
});

// Profesores (requiere auth + RGPD, NO active para permitir desactivar)
Route::middleware(['auth', 'rgpd'])->prefix('profesores')->name('profesores.')->group(function () {
    Route::get('/', [ProfesorController::class, 'index'])->name('index')->middleware('active');
    Route::get('/create', [ProfesorController::class, 'create'])->name('create')->middleware('can:create-profesor');
    Route::post('/', [ProfesorController::class, 'store'])->name('store')->middleware('can:create-profesor');
    Route::get('/{profesor}', [ProfesorController::class, 'show'])->name('show')->middleware('can:view-profesor,profesor');
    Route::get('/{profesor}/edit', [ProfesorController::class, 'edit'])->name('edit')->middleware('can:update-profesor,profesor');
    Route::put('/{profesor}', [ProfesorController::class, 'update'])->name('update')->middleware('can:update-profesor,profesor');
    Route::post('/{profesor}/deactivate', [ProfesorController::class, 'deactivate'])->name('deactivate')->middleware('can:deactivate-profesor');
    Route::delete('/{profesor}', [ProfesorController::class, 'destroy'])->name('destroy')->middleware('can:delete-profesor');
    // Sustituciones
    Route::post('/{profesor}/sustituciones', [ProfesorController::class, 'storeSustitucion'])->name('sustituciones.store')->middleware('can:update-profesor,profesor');
    Route::delete('/sustituciones/{sustitucion}', [ProfesorController::class, 'destroySustitucion'])->name('sustituciones.destroy')->middleware('can:update-profesor,profesor');
});

// Admin (solo admin)
Route::middleware(['auth', 'active', 'rgpd', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Gestión de usuarios
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/deactivate', [\App\Http\Controllers\Admin\UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/reactivate', [\App\Http\Controllers\Admin\UserController::class, 'reactivate'])->name('users.reactivate');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
});