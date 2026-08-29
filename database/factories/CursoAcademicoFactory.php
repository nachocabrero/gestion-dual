<?php

namespace Database\Factories;

use App\Models\CursoAcademico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CursoAcademico>
 */
class CursoAcademicoFactory extends Factory
{
    protected $model = CursoAcademico::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(24, 30);
        $nextYear = $year + 1;

        return [
            'nombre' => "{$year}/{$nextYear}",
            'fecha_inicio' => fake()->date("{$year}-09-01"),
            'fecha_fin' => fake()->date("{$nextYear}-06-30"),
            'is_active' => fake()->boolean(20),
        ];
    }
}