<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CursoAcademico extends Model
{
    use HasFactory;

    protected $table = 'cursos_academicos';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'is_active',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Proyectos del curso.
     */
    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'curso_academico_id');
    }

    /**
     * Scope: cursos activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}