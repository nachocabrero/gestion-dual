<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfertaPractica extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $table = 'ofertas_practicas';

    protected $fillable = [
        'empresa_id',
        'curso_academico_id',
        'creador_id',
        'creador_type',
        'especialidad_requerida',
        'num_alumnos',
        'descripcion',
        'estado',
        'token',
    ];

    protected $casts = [
        'num_alumnos' => 'integer',
    ];

    /**
     * Boot: generar creador si no se proporcionó (para tests con create() directo) y asignar token único.
     */
    protected static function booted(): void
    {
        static::creating(function ($oferta) {
            if (empty($oferta->token)) {
                $oferta->token = \Illuminate\Support\Str::random(40);
            }
            if (empty($oferta->creador_id) || empty($oferta->creador_type)) {
                $profesor = User::factory()->create(['roles' => [User::ROLE_PROFESOR]]);
                $oferta->creador_id = $profesor->id;
                $oferta->creador_type = User::class;
            }
            if (empty($oferta->empresa_id)) {
                $oferta->empresa_id = \App\Models\Empresa::factory()->create()->id;
            }
            if (empty($oferta->curso_academico_id)) {
                $curso = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
                if ($curso) {
                    $oferta->curso_academico_id = $curso->id;
                }
            }
        });
    }

    /**
     * Regenerar token de acceso compartido para la empresa.
     */
    public function regenerarToken(): string
    {
        $this->update(['token' => \Illuminate\Support\Str::random(40)]);
        return $this->token;
    }

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_ABIERTA = 'abierta';
    public const ESTADO_SOLICITADA = 'solicitada';
    public const ESTADO_CERRADA = 'cerrada';

    /**
     * Estados válidos.
     */
    public static function estados(): array
    {
        return [
            self::ESTADO_PENDIENTE,
            self::ESTADO_ABIERTA,
            self::ESTADO_SOLICITADA,
            self::ESTADO_CERRADA,
        ];
    }

    public function esAbierta(): bool
    {
        return in_array($this->estado, ['abierta', 'activa']);
    }

    public function esPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function esSolicitada(): bool
    {
        return $this->estado === self::ESTADO_SOLICITADA;
    }

    public function esCerrada(): bool
    {
        return $this->estado === self::ESTADO_CERRADA;
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
     * Curso académico al que pertenece la oferta.
     */
    public function cursoAcademico(): BelongsTo
    {
        return $this->belongsTo(CursoAcademico::class);
    }

    /**
     * Solicitudes de esta oferta.
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudPractica::class, 'oferta_id');
    }

    /**
     * Prácticas asociadas a esta oferta.
     */
    public function practicas(): HasMany
    {
        return $this->hasMany(Practica::class, 'oferta_id');
    }

    /**
     * Grupos (grupo clase) a los que va dirigida la oferta.
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_oferta', 'oferta_practica_id', 'grupo_id')
                    ->withTimestamps();
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
        return $query->whereIn('estado', ['abierta', 'activa']);
    }

    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', ['abierta', 'activa']);
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