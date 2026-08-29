<?php

namespace Database\Factories;

use App\Models\Ciclo;
use App\Models\Linea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Linea>
 */
class LineaFactory extends Factory
{
    protected $model = Linea::class;

    public function definition(): array
    {
        return [
            'ciclo_id' => Ciclo::factory(),
            'nombre' => fake()->randomElement(['DAW', 'DAM', 'DSIW', 'DASIR']),
            'turno' => fake()->randomElement(['manana', 'tarde']),
            'is_active' => true,
        ];
    }
}