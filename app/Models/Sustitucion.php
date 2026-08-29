<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sustitucion extends Model
{
    use HasFactory;

    protected $table = 'sustituciones';

    protected $fillable = [
        'profesor_titular_id',
        'profesor_sustituto_id',
        'asignatura_id',
        'grupo_id',
        'fecha_inicio',
        'fecha_fin',
        'is_active',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'is_active' => 'boolean',
    ];

    public function profesorTitular(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_titular_id');
    }

    public function profesorSustituto(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'profesor_sustituto_id');
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('fecha_fin', '>=', now()->toDateString());
    }
}