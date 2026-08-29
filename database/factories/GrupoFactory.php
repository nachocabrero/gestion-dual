<?php

namespace Database\Factories;

use App\Models\CursoAcademico;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grupo>
 */
class GrupoFactory extends Factory
{
    protected $model = Grupo::class;

    public function definition(): array
    {
        return [
            'linea_id' => Linea::factory(),
            'curso_academico_id' => CursoAcademico::factory(),
            'numero' => fake()->numberBetween(1, 20),
            'nombre' => fake()->words(2, true),
            'tutor_id' => User::factory(),
            'is_active' => true,
        ];
    }
}