<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumno extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'grupo_id',
        'linkedin_url',
        'portfolio_url',
        'telefono',
        'domicilio',
        'fecha_nacimiento',
        'tutor_practicas_id',
        'empresa_practicas_primero',
        'horas_practicas_primero',
        'sin_practicas_primero',
        'repitiendo_modulos_primero',
        'no_hace_practicas_este_ano',
        'tiene_vehiculo_propio',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'horas_practicas_primero' => 'integer',
        'sin_practicas_primero' => 'boolean',
        'repitiendo_modulos_primero' => 'boolean',
        'no_hace_practicas_este_ano' => 'boolean',
        'tiene_vehiculo_propio' => 'boolean',
    ];

    /**
     * Determina si el alumno puede realizar prácticas en el curso actual.
     * Si está repitiendo con módulos pendientes de 1º o tiene marcado que no hace prácticas
     * (menor de 16 años, no superó PRL...), la normativa no permite prácticas.
     */
    public function puedeHacerPracticas(): bool
    {
        return !$this->repitiendo_modulos_primero && !$this->no_hace_practicas_este_ano;
    }

    /**
     * Scope para filtrar alumnos que pueden hacer prácticas (no están repitiendo 1º y no tienen marcado que no hacen prácticas).
     */
    public function scopePuedenHacerPracticas($query)
    {
        return $query->where(function ($q) {
            $q->where('repitiendo_modulos_primero', false)
              ->orWhereNull('repitiendo_modulos_primero');
        })->where(function ($q) {
            $q->where('no_hace_practicas_este_ano', false)
              ->orWhereNull('no_hace_practicas_este_ano');
        });
    }

    /**
     * Determina si el alumno ha realizado prácticas en 1º curso o tiene convalidación / 0 horas.
     */
    public function haRealizadoPracticasPrimero(): bool
    {
        return !$this->sin_practicas_primero && (!empty($this->empresa_practicas_primero) || ($this->horas_practicas_primero && $this->horas_practicas_primero > 0));
    }

    /**
     * Determina si el alumno está matriculado en algún grupo de 2º curso.
     */
    public function esSegundoCurso(): bool
    {
        if ($this->relationLoaded('grupos')) {
            $grupos = $this->grupos;
        } else {
            $grupos = $this->gruposActuales()->get();
            if ($grupos->isEmpty()) {
                $grupos = $this->grupos()->get();
            }
        }

        return $grupos->contains(function ($grupo) {
            if ((int) $grupo->numero === 2) {
                return true;
            }
            if ((int) $grupo->numero === 1) {
                return false;
            }

            $nombre = $grupo->nombre ?? '';
            if (str_contains($nombre, '1º') || str_contains($nombre, '1.º')) {
                return false;
            }

            return str_contains($nombre, '2º') || str_contains($nombre, '2.º');
        });
    }

    /**
     * Determina si el alumno está matriculado exclusivamente en grupos de 1º curso
     * (es decir, tiene al menos un grupo asignado y ninguno de ellos es de 2º curso).
     */
    public function soloEnGruposPrimero(): bool
    {
        if ($this->relationLoaded('grupos')) {
            $grupos = $this->grupos;
        } else {
            $grupos = $this->gruposActuales()->get();
            if ($grupos->isEmpty()) {
                $grupos = $this->grupos()->get();
            }
        }

        if ($grupos->isEmpty()) {
            return false;
        }

        // Si pertenece a algún grupo de 2º, no está solo en primero
        if ($this->esSegundoCurso()) {
            return false;
        }

        // Verificar que todos los grupos en los que está son de 1º
        return $grupos->every(function ($grupo) {
            if ((int) $grupo->numero === 1) {
                return true;
            }
            if ((int) $grupo->numero === 2) {
                return false;
            }

            $nombre = $grupo->nombre ?? '';
            if (str_contains($nombre, '2º') || str_contains($nombre, '2.º')) {
                return false;
            }

            return str_contains($nombre, '1º') || str_contains($nombre, '1.º');
        });
    }

    /**
     * Usuario asociado (extiende User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Portfolio URL con fallback al usuario.
     */
    public function getPortfolioUrlAttribute(?string $value): ?string
    {
        return $value ?: $this->user?->portfolio_url;
    }

    /**
     * Práctica formal confirmada en 2º curso (creada en módulo de prácticas).
     */
    public function getPracticaConfirmadaAttribute(): ?Practica
    {
        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        if ($this->relationLoaded('practicas')) {
            return $this->practicas
                ->filter(fn($p) => $cursoActual ? $p->curso_academico_id === $cursoActual->id : true)
                ->first() ?? $this->practicas->sortByDesc('fecha_inicio')->first();
        }

        return $this->practicas()
            ->when($cursoActual, fn($q) => $q->where('curso_academico_id', $cursoActual->id))
            ->with('empresa')
            ->latest('fecha_inicio')
            ->first() ?? $this->practicas()->with('empresa')->latest('fecha_inicio')->first();
    }

    /**
     * Solicitud o propuesta de prácticas asignada (en oferta pero sin práctica formal creada aún).
     */
    public function getPropuestaPracticaAttribute(): ?SolicitudPractica
    {
        if ($this->relationLoaded('solicitudesPracticas')) {
            return $this->solicitudesPracticas
                ->whereIn('estado', ['aceptado', 'pendiente'])
                ->first();
        }

        return $this->solicitudesPracticas()
            ->whereIn('estado', ['aceptado', 'pendiente'])
            ->with('oferta.empresa')
            ->latest('created_at')
            ->first();
    }

    /**
     * Empresa asignada en 2º curso (práctica registrada o solicitud de oferta aceptada).
     */
    public function getEmpresaSegundoAttribute(): ?string
    {
        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();

        // 1. Práctica formal registrada (preferencia al curso actual o más reciente)
        if ($this->relationLoaded('practicas')) {
            $practica = $this->practicas
                ->filter(fn($p) => $cursoActual ? $p->curso_academico_id === $cursoActual->id : true)
                ->first() ?? $this->practicas->sortByDesc('fecha_inicio')->first();
            if ($practica && $practica->empresa) {
                return $practica->empresa->nombre;
            }
        } else {
            $practica = $this->practicas()
                ->when($cursoActual, fn($q) => $q->where('curso_academico_id', $cursoActual->id))
                ->with('empresa')
                ->latest('fecha_inicio')
                ->first() ?? $this->practicas()->with('empresa')->latest('fecha_inicio')->first();
            if ($practica && $practica->empresa) {
                return $practica->empresa->nombre;
            }
        }

        // 2. Solicitud de oferta de prácticas aceptada
        if ($this->relationLoaded('solicitudesPracticas')) {
            $solicitud = $this->solicitudesPracticas
                ->where('estado', 'aceptado')
                ->first();
            if ($solicitud && $solicitud->oferta && $solicitud->oferta->empresa) {
                return $solicitud->oferta->empresa->nombre;
            }
        } else {
            $solicitud = $this->solicitudesPracticas()
                ->where('estado', 'aceptado')
                ->with('oferta.empresa')
                ->latest('created_at')
                ->first();
            if ($solicitud && $solicitud->oferta && $solicitud->oferta->empresa) {
                return $solicitud->oferta->empresa->nombre;
            }
        }

        return null;
    }

    /**
     * Primer grupo del alumno (solo lectura, usar con precaución).
     * Nota: no es una relación Eloquent válida — usar ->grupos->first() en su lugar.
     */
    public function getGrupoAttribute(): ?Grupo
    {
        return $this->grupos()->first();
    }

    /**
     * Todos los grupos a los que pertenece (cualquier curso académico).
     */
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'alumno_grupo')
            ->withPivot('curso_academico_id')
            ->withTimestamps();
    }

    /**
     * Grupos del alumno en un curso concreto (histórico o actual).
     */
    public function gruposEnCurso($cursoId)
    {
        return $this->grupos()
            ->wherePivot('curso_academico_id', $cursoId instanceof CursoAcademico ? $cursoId->getKey() : $cursoId);
    }

    /**
     * Grupos del alumno en el curso académico activo (el curso en marcha).
     */
    public function gruposActuales()
    {
        $activo = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
        if (!$activo) {
            return $this->grupos();
        }

        return $this->gruposEnCurso($activo->id);
    }

    /**
     * Tutor de prácticas.
     */
    public function tutorPracticas()
    {
        return $this->belongsTo(User::class, 'tutor_practicas_id');
    }

    /**
     * Anotaciones del alumno.
     */
    public function anotaciones(): HasMany
    {
        return $this->hasMany(Anotacion::class);
    }

    /**
     * Solicitudes de prácticas.
     */
    public function solicitudesPracticas(): HasMany
    {
        return $this->hasMany(SolicitudPractica::class, 'alumno_id');
    }

    /**
     * Prácticas realizadas por el alumno.
     */
    public function practicas(): HasMany
    {
        return $this->hasMany(Practica::class);
    }

    /**
     * Proyectos del alumno.
     */
    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class);
    }

    /**
     * Convenios del alumno.
     */
    public function convenios(): HasMany
    {
        return $this->hasMany(Convenio::class);
    }

    /**
     * Ciclos en los que está matriculado (puede estar en varios a la vez).
     */
    public function ciclosMatriculados(): BelongsToMany
    {
        return $this->belongsToMany(Ciclo::class, 'alumno_ciclo_matricula')
                    ->using(AlumnoCicloMatricula::class)
                    ->withPivot('curso_academico', 'matriculado_at', 'graduado_at')
                    ->withTimestamps();
    }

    /**
     * Obtener el nombre de archivo normalizado para la descarga del CV.
     * Formato: CV IESHLANZ - {nombre del alumno}.pdf
     */
    public function getCvDownloadFilenameAttribute(): string
    {
        $user = $this->user;
        $extension = $user?->cv_url ? (strtolower(pathinfo($user->cv_url, PATHINFO_EXTENSION)) ?: 'pdf') : 'pdf';
        $nombre = trim(preg_replace('/\s+/', ' ', str_replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'], ' ', $user?->name ?: ('alumno_' . $this->id))));

        return "CV IESHLANZ - {$nombre}.{$extension}";
    }
}