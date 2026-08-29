<?php

namespace Database\Factories;

use App\Models\Ciclo;
use App\Models\Familia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ciclo>
 */
class CicloFactory extends Factory
{
    protected $model = Ciclo::class;

    public function definition(): array
    {
        return [
            'codigo' => fake()->regexify('[A-Z]{2,5}[0-9]{0,2}'),
            'nombre' => fake()->words(3, true),
            'descripcion' => fake()->sentence(),
            'grado' => fake()->randomElement(['basica', 'media', 'superior', 'especializacion']),
            'duracion_anos' => fake()->randomElement([1, 2, 3]),
            'is_active' => true,
            'familia_id' => \App\Models\Familia::first()?->id ?? \App\Models\Familia::factory()->create()->id,
        ];
    }
}