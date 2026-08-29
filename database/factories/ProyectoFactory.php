<?php

namespace Database\Factories;

use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proyecto>
 */
class ProyectoFactory extends Factory
{
    protected $model = Proyecto::class;

    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(3),
            'descripcion' => fake()->paragraph(),
            'enlace_repositorio' => fake()->optional()->url(),
            'enlace_despliegue' => fake()->optional()->url(),
            'calificacion' => fake()->optional()->randomFloat(2, 1, 10),
            'es_destacado' => fake()->boolean(20),
        ];
    }
}