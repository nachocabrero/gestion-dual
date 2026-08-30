<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesor extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $table = 'profesores';

    protected $fillable = [
        'user_id',
        'especialidad',
        'es_tutor',
        'es_coordinador_dual',
    ];

    protected $casts = [
        'es_tutor' => 'boolean',
        'es_coordinador_dual' => 'boolean',
    ];

    /**
     * Usuario asociado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function gruposImpartidos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_profesor')->withTimestamps();
    }

    public function asignaturas(): BelongsToMany
    {
        return $this->belongsToMany(Asignatura::class, 'profesor_asignatura')->withTimestamps();
    }

    public function gruposTutor()
    {
        return $this->hasMany(Grupo::class, 'tutor_id', 'user_id');
    }

    /**
     * Departamentos que dirige.
     */
    public function jefaturasDepartamento(): HasMany
    {
        return $this->hasMany(Departamento::class, 'jefe_departamento_id');
    }



    /**
     * Sustituciones donde este profesor es el sustituto.
     */
    public function sustitucionesComoSustituto(): HasMany
    {
        return $this->hasMany(Sustitucion::class, 'profesor_sustituto_id');
    }

    /**
     * Sustituciones donde este profesor es el titular.
     */
    public function sustitucionesComoTitular(): HasMany
    {
        return $this->hasMany(Sustitucion::class, 'profesor_titular_id');
    }

    /**
     * Sustituciones (titular o sustituto).
     */
    public function sustituciones(): HasMany
    {
        return $this->hasMany(Sustitucion::class, 'profesor_sustituto_id');
    }

    /**
     * Scopes
     */
    public function scopeTutores($query)
    {
        return $query->where('es_tutor', true);
    }

    public function scopeCoordinadores($query)
    {
        return $query->where('es_coordinador_dual', true);
    }
}