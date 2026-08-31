<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumno extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'grupo_id',
        'linkedin_url',
        'telefono',
        'domicilio',
        'fecha_nacimiento',
        'tutor_practicas_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    /**
     * Usuario asociado (extiende User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Primer grupo del alumno (solo lectura, usar con precaución).
     * Nota: no es una relación Eloquent válida — usar ->grupos->first() en su lugar.
     */
    public function getGrupoAttribute(): ?Grupo
    {
        return $this->grupos()->first();
    }

    /**
     * Grupo al que pertenece.
     */
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'alumno_grupo')->withTimestamps();
    }

    /**
     * Tutor de prácticas.
     */
    public function tutorPracticas()
    {
        return $this->belongsTo(User::class, 'tutor_practicas_id');
    }

    /**
     * Anotaciones del alumno.
     */
    public function anotaciones(): HasMany
    {
        return $this->hasMany(Anotacion::class);
    }

    /**
     * Solicitudes de prácticas.
     */
    public function solicitudesPracticas(): HasMany
    {
        return $this->hasMany(SolicitudPractica::class, 'alumno_id');
    }

    /**
     * Proyectos del alumno.
     */
    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class);
    }

    /**
     * Ciclos en los que está matriculado (puede estar en varios a la vez).
     */
    public function ciclosMatriculados(): BelongsToMany
    {
        return $this->belongsToMany(Ciclo::class, 'alumno_ciclo_matricula')
                    ->withPivot('id', 'curso_academico', 'matriculado_at', 'graduado_at')
                    ->withTimestamps();
    }
}