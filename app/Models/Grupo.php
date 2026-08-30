<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $fillable = [
        'linea_id',
        'curso_academico_id',
        'numero',
        'nombre',
        'tutor_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function linea(): BelongsTo
    {
        return $this->belongsTo(Linea::class);
    }

    public function cursoAcademico(): BelongsTo
    {
        return $this->belongsTo(CursoAcademico::class, 'curso_academico_id');
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }

    /**
     * Profesores del equipo educativo.
     */
    public function profesores(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Profesor::class, 'profesor_grupo');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}