<?php

namespace App\Models;

use App\Traits\RegistrableCambio;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComunicacionEmpresa extends Model
{
    use HasFactory, RegistrableCambio;

    protected $table = 'comunicaciones_empresa';

    public const TIPO_TELEFONO = 'telefono';
    public const TIPO_EMAIL = 'email';
    public const TIPO_PRESENCIAL = 'presencial';

    public const TIPOS = [
        self::TIPO_TELEFONO => 'Teléfono',
        self::TIPO_EMAIL => 'Email',
        self::TIPO_PRESENCIAL => 'Visita presencial',
    ];

    protected $fillable = [
        'empresa_id',
        'profesor_id',
        'user_id',
        'curso_academico_id',
        'tipo',
        'motivo',
        'respuesta',
        'fecha',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'profesor_id' => 'integer',
        'user_id' => 'integer',
        'curso_academico_id' => 'integer',
        'fecha' => 'datetime',
    ];

    /**
     * Empresa con la que se estableció la comunicación.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Profesor que realizó la comunicación.
     */
    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }

    /**
     * Usuario que registró la comunicación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Curso académico al que pertenece la comunicación.
     */
    public function cursoAcademico(): BelongsTo
    {
        return $this->belongsTo(CursoAcademico::class);
    }

    /**
     * Nombre legible del tipo de comunicación.
     */
    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }

    /**
     * Nombre del autor que realizó el registro.
     */
    public function getAutorNombreAttribute(): string
    {
        return $this->profesor?->user?->name ?? $this->user?->name ?? 'Docente / Admin';
    }
}

