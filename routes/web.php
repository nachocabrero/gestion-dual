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

// Página principal — Portfolio público
Route::get('/', [\App\Http\Controllers\ProyectoController::class, 'portfolio'])->name('portfolio');

// Contacto empresas
Route::post('/contacto-empresa', [\App\Http\Controllers\ProyectoController::class, 'enviarContacto'])->name('contacto.empresa');

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
    Route::delete('/{alumno}/matricula', [AlumnoController::class, 'destroyMatricula'])->name('matricula-destroy')->middleware('can:update-alumno,alumno');
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

    // Estructura académica (familias → ciclos → líneas → grupos, y asignaturas por ciclo)
    Route::prefix('estructura')->name('estructura.')->group(function () {
        Route::resource('familias', \App\Http\Controllers\Admin\Estructura\FamiliaController::class);

        Route::get('/ciclos/familia/{familia}', [\App\Http\Controllers\Admin\Estructura\CicloController::class, 'index'])->name('ciclos.index');
        Route::get('/ciclos/crear/{familia}', [\App\Http\Controllers\Admin\Estructura\CicloController::class, 'create'])->name('ciclos.create');
        Route::post('/ciclos/{familia}', [\App\Http\Controllers\Admin\Estructura\CicloController::class, 'store'])->name('ciclos.store');
        Route::get('/ciclos/{ciclo}', [\App\Http\Controllers\Admin\Estructura\CicloController::class, 'show'])->name('ciclos.show');
        Route::get('/ciclos/{ciclo}/editar', [\App\Http\Controllers\Admin\Estructura\CicloController::class, 'edit'])->name('ciclos.edit');
        Route::put('/ciclos/{ciclo}', [\App\Http\Controllers\Admin\Estructura\CicloController::class, 'update'])->name('ciclos.update');
        Route::delete('/ciclos/{ciclo}', [\App\Http\Controllers\Admin\Estructura\CicloController::class, 'destroy'])->name('ciclos.destroy');

        Route::get('/lineas/ciclo/{ciclo}', [\App\Http\Controllers\Admin\Estructura\LineaController::class, 'index'])->name('lineas.index');
        Route::get('/lineas/crear/{ciclo}', [\App\Http\Controllers\Admin\Estructura\LineaController::class, 'create'])->name('lineas.create');
        Route::post('/lineas/{ciclo}', [\App\Http\Controllers\Admin\Estructura\LineaController::class, 'store'])->name('lineas.store');
        Route::get('/lineas/{linea}', [\App\Http\Controllers\Admin\Estructura\LineaController::class, 'show'])->name('lineas.show');
        Route::get('/lineas/{linea}/editar', [\App\Http\Controllers\Admin\Estructura\LineaController::class, 'edit'])->name('lineas.edit');
        Route::put('/lineas/{linea}', [\App\Http\Controllers\Admin\Estructura\LineaController::class, 'update'])->name('lineas.update');
        Route::delete('/lineas/{linea}', [\App\Http\Controllers\Admin\Estructura\LineaController::class, 'destroy'])->name('lineas.destroy');

        Route::get('/grupos/ciclo/{ciclo}/crear', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'createPorCiclo'])->name('grupos.create-ciclo');
        Route::post('/grupos/ciclo/{ciclo}', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'storePorCiclo'])->name('grupos.store-ciclo');
        Route::get('/grupos/linea/{linea}', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'index'])->name('grupos.index');
        Route::get('/grupos/crear/{linea}', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'create'])->name('grupos.create');
        Route::post('/grupos/{linea}', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'store'])->name('grupos.store');
        Route::get('/grupos/{grupo}', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'show'])->name('grupos.show');
        Route::get('/grupos/{grupo}/editar', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'edit'])->name('grupos.edit');
        Route::put('/grupos/{grupo}', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'update'])->name('grupos.update');
        Route::delete('/grupos/{grupo}', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'destroy'])->name('grupos.destroy');
        Route::post('/grupos/{grupo}/alumnos', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'addAlumno'])->name('grupos.alumnos.add');
        Route::delete('/grupos/{grupo}/alumnos/{alumno}', [\App\Http\Controllers\Admin\Estructura\GrupoController::class, 'removeAlumno'])->name('grupos.alumnos.remove');

        Route::get('/asignaturas/ciclo/{ciclo}', [\App\Http\Controllers\Admin\Estructura\AsignaturaController::class, 'index'])->name('asignaturas.index');
        Route::get('/asignaturas/crear/{ciclo}', [\App\Http\Controllers\Admin\Estructura\AsignaturaController::class, 'create'])->name('asignaturas.create');
        Route::post('/asignaturas/{ciclo}', [\App\Http\Controllers\Admin\Estructura\AsignaturaController::class, 'store'])->name('asignaturas.store');
        Route::get('/asignaturas/{asignatura}/editar', [\App\Http\Controllers\Admin\Estructura\AsignaturaController::class, 'edit'])->name('asignaturas.edit');
        Route::put('/asignaturas/{asignatura}', [\App\Http\Controllers\Admin\Estructura\AsignaturaController::class, 'update'])->name('asignaturas.update');
        Route::delete('/asignaturas/{asignatura}', [\App\Http\Controllers\Admin\Estructura\AsignaturaController::class, 'destroy'])->name('asignaturas.destroy');

        Route::get('/cursos', [\App\Http\Controllers\Admin\Estructura\CursoAcademicoController::class, 'index'])->name('cursos.index');
        Route::get('/cursos/crear', [\App\Http\Controllers\Admin\Estructura\CursoAcademicoController::class, 'create'])->name('cursos.create');
        Route::post('/cursos', [\App\Http\Controllers\Admin\Estructura\CursoAcademicoController::class, 'store'])->name('cursos.store');
        Route::post('/cursos/{curso}/activo', [\App\Http\Controllers\Admin\Estructura\CursoAcademicoController::class, 'setActive'])->name('cursos.activo');
        Route::delete('/cursos/{curso}', [\App\Http\Controllers\Admin\Estructura\CursoAcademicoController::class, 'destroy'])->name('cursos.destroy');
    });
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
    Route::resource('convenios', \App\Http\Controllers\ConvenioController::class)->except(['index', 'show']);
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
    Route::get('/{oferta}/enviar', [OfertaPracticaController::class, 'enviarForm'])->name('enviar-form');
    Route::post('/{oferta}/enviar', [OfertaPracticaController::class, 'enviarAAlumnos'])->name('enviar');
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
Route::get('/fix-alumnos', function() {
    $users = \App\Models\User::whereJsonContains('roles', 'alumno')->doesntHave('alumno')->get();
    foreach ($users as $user) {
        \App\Models\Alumno::create(['user_id' => $user->id]);
    }
    return "Fix aplicado a " . $users->count() . " usuarios.";
});

Route::get('/run-migrations', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return nl2br(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Exception $e) {
        return "Error al migrar: " . $e->getMessage() . "<br>" . nl2br($e->getTraceAsString());
    }
});

