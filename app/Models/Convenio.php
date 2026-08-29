<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Convenio extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'ciclo_id',
        'curso_academico',
        'estado',
        'fecha_firma',
    ];

    protected $casts = [
        'fecha_firma' => 'date',
    ];

    /**
     * Estados válidos.
     */
    public static function estados(): array
    {
        return ['no_firmado', 'firmado'];
    }

    /**
     * ¿Está firmado?
     */
    public function estaFirmado(): bool
    {
        return $this->estado === 'firmado';
    }

    /**
     * Empresa del convenio.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Ciclo del convenio.
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class);
    }

    /**
     * Scopes
     */
    public function scopeFirmados($query)
    {
        return $query->where('estado', 'firmado');
    }

    public function scopeNoFirmados($query)
    {
        return $query->where('estado', 'no_firmado');
    }

    public function scopePorCurso($query, string $curso)
    {
        return $query->where('curso_academico', $curso);
    }
}