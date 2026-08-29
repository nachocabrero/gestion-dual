<?php

namespace Database\Factories;

use App\Models\Ciclo;
use App\Models\Convenio;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Convenio>
 */
class ConvenioFactory extends Factory
{
    protected $model = Convenio::class;

    public function definition(): array
    {
        $cursoIndex = $this->counter ?? 1;
        $curso = '2' . ($cursoIndex + 24) . '/' . ($cursoIndex + 25);

        return [
            'empresa_id' => Empresa::factory(),
            'ciclo_id' => Ciclo::factory(),
            'curso_academico' => $curso,
            'estado' => fake()->randomElement(['no_firmado', 'firmado']),
            'fecha_firma' => fake()->optional()->date(),
        ];
    }
}