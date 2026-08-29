<?php

namespace App\Policies;

use App\Models\Profesor;
use App\Models\User;

class ProfesorPolicy
{
    /**
     * Ver perfil de un profesor.
     * Admin, Coordinador Dual, y otros profesores pueden ver.
     */
    public function view(User $authUser, Profesor $profesor): bool
    {
        return $authUser->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL, User::ROLE_PROFESOR]);
    }

    /**
     * Crear profesor. Solo Admin y Coordinador Dual.
     */
    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL]);
    }

    /**
     * Actualizar profesor. Solo Admin y Coordinador Dual.
     */
    public function update(User $authUser, Profesor $profesor): bool
    {
        return $authUser->hasAnyRole([User::ROLE_ADMIN, User::ROLE_COORDINADOR_DUAL]);
    }

    /**
     * Desactivar profesor. Solo Admin.
     */
    public function deactivate(User $authUser, Profesor $profesor): bool
    {
        return $authUser->hasRole(User::ROLE_ADMIN);
    }

    /**
     * Eliminar profesor. Solo Admin.
     */
    public function delete(User $authUser, Profesor $profesor): bool
    {
        return $authUser->hasRole(User::ROLE_ADMIN);
    }
}