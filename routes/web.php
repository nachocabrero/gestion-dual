<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CambioController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AnotacionController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\RgpdController;
use App\Http\Controllers\OfertaPracticaController;
use App\Http\Controllers\CookieController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página principal — redirige a dashboard si auth, a login si no
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Rutas públicas (sin autenticación)
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/cookies', [CookieController::class, 'index'])->name('cookies');
Route::post('/cookies/accept', [CookieController::class, 'accept'])->name('cookies.accept');
Route::post('/cookies/reject', [CookieController::class, 'reject'])->name('cookies.reject');

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

// Anotaciones / Tutorías (Admin, Coordinador Dual, Profesor)
Route::middleware(['auth', 'active', 'rgpd'])->prefix('anotaciones')->name('anotaciones.')->group(function () {
    Route::get('/', [AnotacionController::class, 'index'])->name('index');
    Route::get('/create', [AnotacionController::class, 'create'])->name('create')->middleware('can:create-anotacion');
    Route::post('/', [AnotacionController::class, 'store'])->name('store')->middleware('can:create-anotacion');
    Route::get('/{anotacion}/edit', [AnotacionController::class, 'edit'])->name('edit')->middleware('can:update-anotacion,anotacion');
    Route::put('/{anotacion}', [AnotacionController::class, 'update'])->name('update')->middleware('can:update-anotacion,anotacion');
    Route::delete('/{anotacion}', [AnotacionController::class, 'destroy'])->name('destroy')->middleware('can:delete-anotacion');
    Route::get('/alumno/{alumno}', [AnotacionController::class, 'show'])->name('show');
});

// Admin (solo admin)
Route::middleware(['auth', 'active', 'rgpd', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de usuarios
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/deactivate', [\App\Http\Controllers\Admin\UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/reactivate', [\App\Http\Controllers\Admin\UserController::class, 'reactivate'])->name('users.reactivate');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Historial de cambios
    Route::get('/cambios', [CambioController::class, 'index'])->name('cambios.index');
    Route::get('/cambios/{cambio}', [CambioController::class, 'show'])->name('cambios.show');
});

// Empresas (solo admin)
Route::middleware(['auth', 'active', 'rgpd', 'role:admin'])->prefix('empresas')->name('empresas.')->group(function () {
    Route::get('/', [\App\Http\Controllers\EmpresaController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\EmpresaController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\EmpresaController::class, 'store'])->name('store');
    Route::get('/{empresa}', [\App\Http\Controllers\EmpresaController::class, 'show'])->name('show');
    Route::get('/{empresa}/edit', [\App\Http\Controllers\EmpresaController::class, 'edit'])->name('edit');
    Route::put('/{empresa}', [\App\Http\Controllers\EmpresaController::class, 'update'])->name('update');
    Route::delete('/{empresa}', [\App\Http\Controllers\EmpresaController::class, 'destroy'])->name('destroy');
    Route::post('/{empresa}/deactivate', [\App\Http\Controllers\EmpresaController::class, 'deactivate'])->name('deactivate');
    Route::post('/{empresa}/reactivate', [\App\Http\Controllers\EmpresaController::class, 'reactivate'])->name('reactivate');
    // Tutores laborales
    Route::post('/{empresa}/tutores', [\App\Http\Controllers\EmpresaController::class, 'storeTutorLaboral'])->name('tutores.store');
    Route::put('/tutores/{tutorLaboral}', [\App\Http\Controllers\EmpresaController::class, 'updateTutorLaboral'])->name('tutores.update');
    Route::delete('/tutores/{tutorLaboral}', [\App\Http\Controllers\EmpresaController::class, 'destroyTutorLaboral'])->name('tutores.destroy');
    // Convenios
    Route::post('/{empresa}/convenios', [\App\Http\Controllers\EmpresaController::class, 'storeConvenio'])->name('convenios.store');
    Route::put('/convenios/{convenio}', [\App\Http\Controllers\EmpresaController::class, 'updateConvenio'])->name('convenios.update');
});

// Ofertas y Solicitudes de Prácticas
Route::middleware(['auth', 'active', 'rgpd'])->prefix('ofertas')->name('ofertas.')->group(function () {
    Route::get('/', [OfertaPracticaController::class, 'index'])->name('index');
    Route::get('/create', [OfertaPracticaController::class, 'create'])->name('create');
    Route::get('/mis-ofertas', [OfertaPracticaController::class, 'misOfertas'])->name('mis-ofertas');
    Route::post('/', [OfertaPracticaController::class, 'store'])->name('store');
    Route::get('/{oferta}', [OfertaPracticaController::class, 'show'])->name('show');
    Route::get('/{oferta}/edit', [OfertaPracticaController::class, 'edit'])->name('edit');
    Route::put('/{oferta}', [OfertaPracticaController::class, 'update'])->name('update');
    Route::delete('/{oferta}', [OfertaPracticaController::class, 'destroy'])->name('destroy');
    Route::post('/{oferta}/postularse', [OfertaPracticaController::class, 'postularse'])->name('postularse');
    Route::post('/solicitudes/{solicitud}/retirar', [OfertaPracticaController::class, 'retirar'])->name('solicitudes.retirar');
    Route::post('/solicitudes/{solicitud}/aceptar', [OfertaPracticaController::class, 'aceptar'])->name('solicitudes.aceptar');
    Route::post('/solicitudes/{solicitud}/rechazar', [OfertaPracticaController::class, 'rechazar'])->name('solicitudes.rechazar');
    Route::get('/{oferta}/solicitudes', [OfertaPracticaController::class, 'solicitudes'])->name('solicitudes');
});
// Gestión de Prácticas
Route::middleware(['auth', 'active', 'rgpd'])->prefix('practicas')->name('practicas.')->group(function () {
    Route::get('/mis-practicas', [\App\Http\Controllers\PracticaController::class, 'misPracticas'])->name('mis-practicas');
    Route::get('/', [\App\Http\Controllers\PracticaController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\PracticaController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\PracticaController::class, 'store'])->name('store');
    Route::get('/{practica}', [\App\Http\Controllers\PracticaController::class, 'show'])->name('show');
    Route::get('/{practica}/edit', [\App\Http\Controllers\PracticaController::class, 'edit'])->name('edit');
    Route::put('/{practica}', [\App\Http\Controllers\PracticaController::class, 'update'])->name('update');
    Route::delete('/{practica}', [\App\Http\Controllers\PracticaController::class, 'destroy'])->name('destroy');
    Route::post('/{practica}/horas', [\App\Http\Controllers\PracticaController::class, 'actualizarHoras'])->name('horas');
});

// Proyectos (2º) — alumno y profesor
Route::middleware(['auth', 'active', 'rgpd'])->prefix('proyectos')->name('proyectos.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ProyectoController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\ProyectoController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\ProyectoController::class, 'store'])->name('store');
    Route::get('/{proyecto}', [\App\Http\Controllers\ProyectoController::class, 'show'])->name('show');
    Route::get('/{proyecto}/edit', [\App\Http\Controllers\ProyectoController::class, 'edit'])->name('edit');
    Route::put('/{proyecto}', [\App\Http\Controllers\ProyectoController::class, 'update'])->name('update');
    Route::delete('/{proyecto}', [\App\Http\Controllers\ProyectoController::class, 'destroy'])->name('destroy');
    Route::post('/{proyecto}/calificar', [\App\Http\Controllers\ProyectoController::class, 'calificar'])->name('calificar');
});

// Portfolio público
Route::get('/portfolio', [\App\Http\Controllers\ProyectoController::class, 'portfolio'])->name('portfolio');
