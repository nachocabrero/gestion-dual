<?php

namespace App\Providers;

use App\Http\Controllers\AlumnoController;
use App\Models\Alumno;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate: Alumno
        Gate::define('view-alumno', function (User $user, Alumno $alumno) {
            // Admin ve todo
            if ($user->hasRole(User::ROLE_ADMIN)) return true;
            // Coordinador Dual ve todo
            if ($user->hasRole(User::ROLE_COORDINADOR_DUAL)) return true;
            // Profesor ve solo su grupo
            if ($user->hasRole(User::ROLE_PROFESOR)) {
                $tutorGrupos = \App\Models\Grupo::where('tutor_id', $user->id)->pluck('id');
                return $tutorGrupos->contains($alumno->grupo_id);
            }
            // Alumno ve solo su propio perfil
            if ($user->hasRole(User::ROLE_ALUMNO)) {
                return $alumno->user_id === $user->id;
            }
            return false;
        });

        Gate::define('create-alumno', function (User $user) {
            return $user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_COORDINADOR_DUAL);
        });

        Gate::define('update-alumno', function (User $user, Alumno $alumno) {
            return $user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_COORDINADOR_DUAL);
        });

        Gate::define('delete-alumno', function (User $user) {
            return $user->hasRole(User::ROLE_ADMIN);
        });

        Gate::define('deactivate-alumno', function (User $user) {
            return $user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_COORDINADOR_DUAL);
        });

        // Gates: Profesor
        Gate::define('view-profesor', function (User $user, Profesor $profesor) {
            return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL, User::ROLE_PROFESOR]);
        });

        Gate::define('create-profesor', function (User $user) {
            return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL]);
        });

        Gate::define('update-profesor', function (User $user, Profesor $profesor) {
            return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL]);
        });

        Gate::define('delete-profesor', function (User $user) {
            return $user->hasRole(User::ROLE_ADMIN);
        });

        Gate::define('deactivate-profesor', function (User $user) {
            return $user->hasRole(User::ROLE_ADMIN);
        });
    }
}