<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Convenio extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'empresa_id',
        'tutor_laboral_id',
        'tutor_docente_id',
        'grupo_id',
        'numero_horas',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'fecha_firma',
    ];

    protected $casts = [
        'fecha_firma' => 'date',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'numero_horas' => 'integer',
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
     * Alumno del convenio.
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /**
     * Empresa del convenio.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Tutor laboral del convenio.
     */
    public function tutorLaboral(): BelongsTo
    {
        return $this->belongsTo(TutorLaboral::class);
    }

    /**
     * Tutor docente (Profesor) del convenio.
     */
    public function tutorDocente(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'tutor_docente_id');
    }

    /**
     * Grupo/Clase del convenio.
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
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
}