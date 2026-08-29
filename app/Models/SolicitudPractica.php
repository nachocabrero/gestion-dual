<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudPractica extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_practicas';

    protected $fillable = [
        'oferta_id',
        'alumno_id',
        'estado',
        'motivo_rechazo',
    ];

    /**
     * Estados válidos.
     */
    public static function estados(): array
    {
        return ['pendiente', 'aceptado', 'rechazado', 'retirado'];
    }

    /**
     * Oferta asociada.
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(OfertaPractica::class, 'oferta_id');
    }

    /**
     * Alumno solicitante.
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    /**
     * ¿Está pendiente?
     */
    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    /**
     * ¿Está aceptada?
     */
    public function estaAceptada(): bool
    {
        return $this->estado === 'aceptado';
    }
}