<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $fillable = [
        'linea_id',
        'curso_academico_id',
        'numero',
        'nombre',
        'tutor_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function linea(): BelongsTo
    {
        return $this->belongsTo(Linea::class);
    }

    public function cursoAcademico(): BelongsTo
    {
        return $this->belongsTo(CursoAcademico::class, 'curso_academico_id');
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_grupo')
            ->withPivot('curso_academico_id')
            ->withTimestamps();
    }

    /**
     * Alumnos del grupo en un curso concreto.
     */
    public function alumnosEnCurso($cursoId)
    {
        $cursoId = $cursoId instanceof CursoAcademico ? $cursoId->getKey() : $cursoId;

        return $this->alumnos()->wherePivot('curso_academico_id', $cursoId);
    }

    /**
     * Profesores del equipo educativo.
     */
    public function profesores(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Profesor::class, 'grupo_profesor')
            ->withPivot('asignatura_id')
            ->withTimestamps();
    }

    /**
     * Equipo educativo del grupo ordenado por módulo, incluyendo tutor y correos.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEquipoEducativoAttribute(): \Illuminate\Support\Collection
    {
        $profesores = $this->relationLoaded('profesores') ? $this->profesores : $this->profesores()->with('user')->get();

        $asigIds = $profesores->pluck('pivot.asignatura_id')->filter()->unique()->values();
        $asignaturas = $asigIds->isNotEmpty()
            ? Asignatura::whereIn('id', $asigIds)->get()->keyBy('id')
            : collect();

        $equipo = collect();
        $tutorUserId = $this->tutor_id;
        $tutorPresente = false;

        foreach ($profesores as $profesor) {
            $user = $profesor->user;
            if (!$user) {
                continue;
            }

            $asig = $profesor->pivot?->relationLoaded('asignatura')
                ? $profesor->pivot->asignatura
                : $asignaturas->get($profesor->pivot?->asignatura_id);

            $esTutor = ($tutorUserId && (int) $user->id === (int) $tutorUserId);
            if ($esTutor) {
                $tutorPresente = true;
            }

            $equipo->push((object) [
                'profesor_id' => $profesor->id,
                'user_id' => $user->id,
                'profesor_nombre' => $user->name,
                'profesor_email' => $user->email,
                'modulo_codigo' => $asig?->codigo,
                'modulo_nombre' => $asig?->nombre ?? 'Docencia general',
                'es_tutor' => $esTutor,
                'user' => $user,
                'profesor' => $profesor,
                'asignatura' => $asig,
            ]);
        }

        // Si el grupo tiene un tutor asignado que no imparte docencia con módulo, añadirlo como tutoría
        if ($tutorUserId && !$tutorPresente) {
            $tutorUser = $this->relationLoaded('tutor') ? $this->tutor : $this->tutor()->first();
            if ($tutorUser) {
                $equipo->prepend((object) [
                    'profesor_id' => $tutorUser->profesor?->id ?? null,
                    'user_id' => $tutorUser->id,
                    'profesor_nombre' => $tutorUser->name,
                    'profesor_email' => $tutorUser->email,
                    'modulo_codigo' => 'TUT',
                    'modulo_nombre' => 'Tutoría de Grupo',
                    'es_tutor' => true,
                    'user' => $tutorUser,
                    'profesor' => $tutorUser->profesor,
                    'asignatura' => null,
                ]);
            }
        }

        return $equipo->sortBy(function ($item) {
            return ($item->es_tutor ? '0_' : '1_') . ($item->modulo_codigo ?? 'ZZZ') . $item->modulo_nombre;
        })->values();
    }

    /**
     * Convenios del grupo.
     */
    public function convenios(): HasMany
    {
        return $this->hasMany(Convenio::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCursoActual($query)
    {
        $cursoActual = CursoAcademico::where('is_active', true)->first();

        return $cursoActual ? $query->where('curso_academico_id', $cursoActual->id) : $query;
    }
}