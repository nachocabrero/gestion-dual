<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';

    protected $fillable = [
        'alumno_id',
        'asignatura_id',
        'evaluacion',
        'nota',
        'observaciones',
    ];

    protected $casts = [
        'nota' => 'decimal:2',
    ];

    /**
     * Alumno al que pertenece la calificación.
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /**
     * Asignatura calificada.
     */
    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class);
    }

    /**
     * Evaluaciones válidas.
     */
    public static function evaluaciones(): array
    {
        return ['primera', 'segunda', 'tercera'];
    }

    /**
     * Escala de notas válida.
     */
    public static function notaValida(?float $nota): bool
    {
        if ($nota === null) return true; // nullable en DB
        return $nota >= 0 && $nota <= 10;
    }

    /**
     * Escalar a numérico (si es cualitativo).
     */
    public static function escalarNota(?string $notaCualitativa): ?float
    {
        if ($notaCualitativa === null) return null;
        $mapa = [
            'SS' => 0,
            'INS' => 3.5,
            'SUF' => 5.5,
            'B' => 7,
            'SB' => 8.5,
            'S' => 10,
        ];
        return $mapa[strtoupper($notaCualitativa)] ?? null;
    }

    /**
     * Calificación media del alumno en una asignatura.
     */
    public static function mediaAlumnoAsignatura(int $alumnoId, int $asignaturaId): ?float
    {
        return self::where('alumno_id', $alumnoId)
            ->where('asignatura_id', $asignaturaId)
            ->avg('nota');
    }

    /**
     * Calificaciones de un alumno en todas sus asignaturas.
     */
    public static function getAlumnoCalificaciones(int $alumnoId): \Illuminate\Database\Eloquent\Collection
    {
        return self::with('asignatura')
            ->where('alumno_id', $alumnoId)
            ->orderBy('asignatura.nombre')
            ->get()
            ->groupBy('evaluacion');
    }
}