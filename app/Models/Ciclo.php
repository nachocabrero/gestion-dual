<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ciclo extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $fillable = [
        'familia_id',
        'codigo',
        'nombre',
        'descripcion',
        'grado',
        'duracion_anos',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Grado del ciclo: basica, media, superior, especializacion, acreditacion
     */
    public function familia(): BelongsTo
    {
        return $this->belongsTo(Familia::class);
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(Linea::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGrado($query, string $grado)
    {
        return $query->where('grado', $grado);
    }
}