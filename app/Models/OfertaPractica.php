<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfertaPractica extends Model
{
    use HasFactory;

    protected $table = 'ofertas_practicas';

    protected $fillable = [
        'empresa_id',
        'creador_id',
        'creador_type',
        'especialidad_requerida',
        'num_alumnos',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'num_alumnos' => 'integer',
    ];

    /**
     * Boot: generar creador si no se proporcionó (para tests con create() directo).
     */
    protected static function booted(): void
    {
        static::creating(function ($oferta) {
            if (empty($oferta->creador_id) || empty($oferta->creador_type)) {
                $profesor = User::factory()->create(['roles' => [User::ROLE_PROFESOR]]);
                $oferta->creador_id = $profesor->id;
                $oferta->creador_type = User::class;
            }
            if (empty($oferta->empresa_id)) {
                $oferta->empresa_id = \App\Models\Empresa::factory()->create()->id;
            }
        });
    }

    /**
     * Estados válidos.
     */
    public static function estados(): array
    {
        return ['pendiente', 'activa', 'cerrada'];
    }

    /**
     * Creador (polymorphic: profesor o empresa).
     */
    public function creador(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Empresa asociada.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Solicitudes de esta oferta.
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudPractica::class, 'oferta_id');
    }

    /**
     * Alumnos a los que se dirigió la oferta.
     */
    public function alumnos_destinatarios(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'solicitudes_practicas', 'oferta_id', 'alumno_id')
                    ->withPivot('estado', 'created_at', 'motivo_rechazo')
                    ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePorEspecialidad($query, string $especialidad)
    {
        return $query->where('especialidad_requerida', $especialidad);
    }

    public function scopePorAlumno($query, int $alumnoId)
    {
        return $query->whereHas('solicitudes', function ($q) use ($alumnoId) {
            $q->where('alumno_id', $alumnoId)->whereIn('estado', ['pendiente', 'aceptado']);
        });
    }
}