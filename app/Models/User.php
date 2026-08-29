<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Roles disponibles
     */
    const ROLE_ALUMNO = 'alumno';
    const ROLE_PROFESOR = 'profesor';
    const ROLE_COORDINADOR_DUAL = 'coordinador_dual';
    const ROLE_EMPRESA = 'empresa';
    const ROLE_ADMIN = 'admin';

    /**
     * Los atributos que son asignables masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
        'is_active',
        'consent_rgpd',
        'consent_rgpd_at',
        'data_deletion_requested_at',
    ];

    /**
     * Los atributos que deben ser ocultos para serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'roles' => 'array',
        'is_active' => 'boolean',
        'consent_rgpd' => 'boolean',
        'consent_rgpd_at' => 'datetime',
        'data_deletion_requested_at' => 'datetime',
    ];

    /**
     * Verificar si el usuario tiene un rol.
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    /**
     * Verificar si el usuario tiene alguno de los roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array(true, array_map(fn($role) => $this->hasRole($role), $roles), true);
    }

    /**
     * Verificar si el usuario tiene todos los roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        return collect($roles)->every(fn($role) => $this->hasRole($role));
    }

    /**
     * Asignar un rol al usuario.
     */
    public function assignRole(string $role): void
    {
        if (! $this->hasRole($role)) {
            $this->roles = array_unique(array_merge($this->roles, [$role]));
            $this->save();
        }
    }

    /**
     * Retirar un rol del usuario.
     */
    public function removeRole(string $role): void
    {
        $this->roles = array_filter($this->roles, fn($r) => $r !== $role);
        $this->save();
    }

    /**
     * Verificar si el usuario está activo.
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

    /**
     * Verificar si el usuario ha aceptado el RGPD.
     */
    public function hasConsentedRgpd(): bool
    {
        return (bool) ($this->consent_rgpd ?? false);
    }

    /**
     * Verificar si se ha solicitado la eliminación de datos.
     */
    public function isDeletionRequested(): bool
    {
        return $this->data_deletion_requested_at !== null;
    }

    /**
     * Relación 1:1 con Alumno.
     */
    public function alumno()
    {
        return $this->hasOne(Alumno::class);
    }

    /**
     * Relación 1:1 con Profesor.
     */
    public function profesor()
    {
        return $this->hasOne(Profesor::class);
    }
}