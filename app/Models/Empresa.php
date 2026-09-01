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
     * Ofertas de prácticas de la empresa.
     */
    public function ofertasPracticas(): HasMany
    {
        return $this->hasMany(OfertaPractica::class, 'empresa_id');
    }

    /**
     * Prácticas realizadas con la empresa.
     */
    public function practicas(): HasMany
    {
        return $this->hasMany(Practica::class, 'empresa_id');
    }

    /**
     * Ciclos con convenio activo (a través de los grupos de los convenios).
     */
    public function ciclosConConvenio(): \Illuminate\Database\Eloquent\Relations\Relation
    {
        // No es una relación directa porque convenios no tiene ciclo_id.
        // Se obtiene a través de: convenios -> grupo -> linea -> ciclo
        return Ciclo::whereHas('lineas.grupos', function ($q) {
            $q->whereHas('convenios', function ($q2) {
                $q2->where('empresa_id', $this->id)
                   ->where('estado', 'firmado');
            });
        })->distinct()->get();
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
        return $query->whereHas('convenios', function ($q) use ($familiaId) {
            $q->where('estado', 'firmado')
              ->whereHas('grupo.linea.ciclo.familia', function ($q2) use ($familiaId) {
                  $q2->where('familias.id', $familiaId);
              });
        });
    }
}