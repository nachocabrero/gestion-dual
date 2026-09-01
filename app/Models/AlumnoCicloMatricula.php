<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AlumnoCicloMatricula extends Pivot
{
    use HasFactory;

    protected $table = 'alumno_ciclo_matricula';

    protected $fillable = [
        'alumno_id',
        'ciclo_id',
        'curso_academico',
        'matriculado_at',
        'graduado_at',
    ];

    protected $casts = [
        'matriculado_at' => 'datetime',
        'graduado_at' => 'datetime',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }
}