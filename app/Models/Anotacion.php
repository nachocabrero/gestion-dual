<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anotacion extends Model
{
    use HasFactory;

    protected $table = 'anotaciones';

    protected $fillable = [
        'alumno_id',
        'profesor_id',
        'titulo',
        'contenido',
        'es_publica',
    ];

    protected $casts = [
        'es_publica' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($anotacion) {
            if ($anotacion->es_publica === null) {
                $anotacion->es_publica = false;
            }
        });
    }

    /**
     * Alumno al que pertenece la anotación.
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /**
     * Profesor que creó la anotación.
     */
    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }

    /**
     * Anotaciones de un alumno.
     */
    public function scopeParaAlumno($query, int $alumnoId)
    {
        return $query->where('alumno_id', $alumnoId)
            ->with(['profesor.user', 'alumno.user'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Anotaciones creadas por un profesor.
     */
    public function scopeCreadasPor($query, int $profesorId)
    {
        return $query->where('profesor_id', $profesorId)
            ->with(['profesor.user', 'alumno.user'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Anotaciones visibles para un profesor (las suyas + las públicas de otros).
     */
    public function scopeVisiblesPara($query, int $profesorId)
    {
        return $query->where(function ($q) use ($profesorId) {
            $q->where('profesor_id', $profesorId)
              ->orWhere('es_publica', true);
        })
        ->with(['profesor.user', 'alumno.user'])
        ->orderBy('created_at', 'desc');
    }
}