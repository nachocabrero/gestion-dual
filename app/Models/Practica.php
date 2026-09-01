<?php

namespace App\Models;

use App\Traits\RegistrableCambio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;

class Practica extends Model
{
use RegistrableCambio;
    use HasFactory;

    protected $table = 'practicas';

    protected $fillable = [
        'alumno_id',
        'oferta_id',
        'empresa_id',
        'curso_academico_id',
        'tutor_laboral_id',
        'fecha_inicio',
        'fecha_fin',
        'horas_acumuladas',
        'convenio_firmado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'horas_acumuladas' => 'integer',
        'convenio_firmado' => 'boolean',
    ];

    /**
     * Alumno.
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /**
     * Oferta asociada.
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(OfertaPractica::class);
    }

    /**
     * Empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Tutor laboral.
     */
    public function tutorLaboral(): BelongsTo
    {
        return $this->belongsTo(TutorLaboral::class);
    }

    /**
     * Curso académico.
     */
    public function cursoAcademico(): BelongsTo
    {
        return $this->belongsTo(CursoAcademico::class);
    }

    /**
     * ¿Está en curso?
     */
    public function estaEnCurso(): bool
    {
        return now()->between($this->fecha_inicio, $this->fecha_fin ?? now());
    }

    /**
     * Horas acumuladas del alumno en el mismo curso académico.
     */
    public function horasEnCurso(): int
    {
        return static::where('alumno_id', $this->alumno_id)
            ->where('curso_academico_id', $this->curso_academico_id)
            ->sum('horas_acumuladas');
    }

    /**
     * Total de horas acumuladas del alumno entre 1º y 2º de prácticas
     * (suma de todas sus prácticas, independientemente del curso académico).
     */
    public function totalHorasAlumno(): int
    {
        return static::where('alumno_id', $this->alumno_id)
            ->sum('horas_acumuladas');
    }

    /**
     * Validar los datos de una práctica.
     * No se exige un mínimo por práctica: el mínimo de 500h se valida
     * sobre la suma total acumulada entre 1º y 2º.
     */
    public static function validarDatos(array $data): array
    {
        $validator = Validator::make($data, [
            'alumno_id' => 'required|exists:alumnos,id',
            'empresa_id' => 'required|exists:empresas,id',
            'tutor_laboral_id' => 'nullable|exists:tutores_laborales,id',
            'curso_academico_id' => 'required|exists:cursos_academicos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'horas_acumuladas' => 'required|integer|min:0',
            'convenio_firmado' => 'boolean',
        ]);

        return $validator->errors()->toArray();
    }

    /**
     * Scope: prácticas en curso.
     */
    public function scopeEnCurso($query)
    {
        return $query->where('fecha_fin', '>=', now())
            ->where('fecha_inicio', '<=', now());
    }

    /**
     * Scope: prácticas finalizadas.
     */
    public function scopeFinalizadas($query)
    {
        return $query->where('fecha_fin', '<', now());
    }

    /**
     * Scope: prácticas pendientes.
     */
    public function scopePendientes($query)
    {
        return $query->where('fecha_inicio', '>', now());
    }
}