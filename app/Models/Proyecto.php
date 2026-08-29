<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'ciclo_id',
        'curso_academico_id',
        'titulo',
        'descripcion',
        'enlace_repositorio',
        'enlace_despliegue',
        'calificacion',
        'es_destacado',
        'destacado_por_id',
    ];

    protected $casts = [
        'calificacion' => 'decimal:2',
        'es_destacado' => 'boolean',
    ];

    /**
     * Alumno autor.
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /**
     * Ciclo asociado.
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class);
    }

    /**
     * Curso académico.
     */
    public function curso Academico(): BelongsTo
    {
        return $this->belongsTo(CursoAcademico::class, 'curso_academico_id');
    }

    /**
     * Imágenes del proyecto.
     */
    public function imagenes(): HasMany
    {
        return $this->hasMany(ProyectoImagen::class);
    }

    /**
     * ¿Está calificado?
     */
    public function estaCalificado(): bool
    {
        return $this->calificacion !== null;
    }

    /**
     * Scopes
     */
    public function scopeDestacados($query)
    {
        return $query->where('es_destacado', true);
    }

    public function scopePorCiclo($query, int $cicloId)
    {
        return $query->where('ciclo_id', $cicloId);
    }

    public function scopePorCurso($query, int $cursoId)
    {
        return $query->where('curso_academico_id', $cursoId);
    }
}