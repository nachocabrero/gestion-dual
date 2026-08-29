<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RgpdController;
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

// Admin (solo admin)
Route::middleware(['auth', 'active', 'rgpd', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Gestión de usuarios
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});