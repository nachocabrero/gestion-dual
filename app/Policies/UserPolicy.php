<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determinar si el usuario puede ver el perfil de otro usuario.
     * RGPD: Solo usuarios autorizados pueden ver datos personales.
     */
    public function view(User $authUser, User $user): bool
    {
        // Un usuario puede ver su propio perfil
        if ($authUser->id === $user->id) {
            return true;
        }

        // Admin puede ver todos
        if ($authUser->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        // Coordinador Dual puede ver profesores y alumnos
        if ($authUser->hasRole(User::ROLE_COORDINADOR_DUAL)) {
            return $user->hasAnyRole([User::ROLE_ALUMNO, User::ROLE_PROFESOR, User::ROLE_EMPRESA]);
        }

        // Profesor puede ver alumnos de sus grupos
        if ($authUser->hasRole(User::ROLE_PROFESOR)) {
            // Esto se validará a nivel de modelo Alumno con su grupo
            return $user->hasRole(User::ROLE_ALUMNO);
        }

        return false;
    }

    /**
     * Determinar si el usuario puede actualizar otro usuario.
     * RGPD: Solo admin o el propio usuario puede modificar datos.
     */
    public function update(User $authUser, User $user): bool
    {
        // Propio perfil
        if ($authUser->id === $user->id) {
            return true;
        }

        // Admin puede actualizar todos
        if ($authUser->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        // Coordinador Dual puede actualizar alumnos, profesores, empresas
        if ($authUser->hasRole(User::ROLE_COORDINADOR_DUAL)) {
            return $user->hasAnyRole([User::ROLE_ALUMNO, User::ROLE_PROFESOR, User::ROLE_EMPRESA]);
        }

        return false;
    }

    /**
     * Determinar si el usuario puede desactivar/reactivar otro usuario.
     * Solo Admin puede desactivar usuarios.
     * RGPD: Derecho de limitación del tratamiento (Art. 18).
     */
    public function deactivate(User $authUser, User $user): bool
    {
        return $authUser->hasRole(User::ROLE_ADMIN);
    }

    /**
     * Determinar si el usuario puede solicitar la eliminación de sus datos.
     * RGPD: Derecho de supresión (Art. 17) — el propio usuario.
     */
    public function requestDeletion(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }

    /**
     * Determinar si el usuario puede ser creado.
     * Solo Admin y Coordinador Dual pueden crear usuarios.
     */
    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL]);
    }

    /**
     * Determinar si el usuario puede ser eliminado (borrado definitivo).
     * Solo Admin puede borrar usuarios definitivamente.
     * RGPD: Derecho de supresión (Art. 17) con salvaguardas.
     */
    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasRole(User::ROLE_ADMIN);
    }
}