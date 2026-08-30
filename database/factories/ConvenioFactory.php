<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Convenio>
 */
class ConvenioFactory extends Factory
{
    protected $model = Convenio::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'alumno_id' => Alumno::factory(),
            'grupo_id' => Grupo::factory(),
            'tutor_laboral_id' => null,
            'tutor_docente_id' => null,
            'numero_horas' => fake()->numberBetween(100, 400),
            'fecha_inicio' => fake()->date(),
            'fecha_fin' => fake()->date(),
            'estado' => fake()->randomElement(['no_firmado', 'firmado']),
            'fecha_firma' => fake()->optional()->date(),
        ];
    }
}