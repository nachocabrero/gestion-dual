<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $fillable = [
        'nombre',
        'cif',
        'direccion',
        'telefono',
        'email',
        'responsable_nombre',
        'responsable_dni',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Tutores laborales de la empresa.
     */
    public function tutoresLaborales(): HasMany
    {
        return $this->hasMany(TutorLaboral::class);
    }

    /**
     * Convenios de la empresa.
     */
    public function convenios(): HasMany
    {
        return $this->hasMany(Convenio::class);
    }

    /**
     * Ciclos con convenio activo.
     */
    public function ciclosConConvenio(): BelongsToMany
    {
        return $this->belongsToMany(Ciclo::class, 'convenios')
            ->where('estado', 'firmado')
            ->withPivot('curso_academico', 'fecha_firma')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByFamilia($query, int $familiaId)
    {
        return $query->whereHas('ciclosConConvenio', function ($q) use ($familiaId) {
            $q->where('familia_id', $familiaId);
        });
    }
}