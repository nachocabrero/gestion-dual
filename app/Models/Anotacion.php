<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anotacion extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $table = 'anotaciones';

    protected $fillable = [
        'alumno_id',
        'profesor_id',
        'titulo',
        'contenido',
    ];

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
     * Anotaciones visibles para un profesor: las suyas más las de alumnos
     * de cualquier grupo al que imparta (todo el equipo educativo lo ve).
     */
    public function scopeVisiblesPara($query, ?int $profesorId)
    {
        return $query->where(function ($q) use ($profesorId) {
            $q->where('profesor_id', $profesorId)
              ->orWhereHas('alumno.grupos', function ($g) use ($profesorId) {
                  $g->whereHas('profesores', fn ($p) => $p->whereKey($profesorId));
              });
        })
        ->with(['profesor.user', 'alumno.user'])
        ->orderBy('created_at', 'desc');
    }
}