<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Anotacion>
 */
class AnotacionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'profesor_id' => Profesor::factory(),
            'titulo' => $this->faker->sentence(4),
            'contenido' => $this->faker->paragraph(),
        ];
    }
}