<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignatura extends Model
{
    use HasFactory;

    protected $table = 'asignaturas';

    protected $fillable = [
        'ciclo_id',
        'codigo',
        'nombre',
        'horas_semanales',
        'es_practicas',
        'is_active',
    ];

    protected $casts = [
        'es_practicas' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function profesores(): BelongsToMany
    {
        return $this->belongsToMany(Profesor::class, 'profesor_asignatura');
    }

    public function scopePracticas($query)
    {
        return $query->where('es_practicas', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}