<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
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
            'alumno_id' => Alumno::factory(),
            'ciclo_id' => Ciclo::factory(),
            'curso_academico_id' => CursoAcademico::factory(),
            'titulo' => fake()->sentence(3),
            'descripcion' => fake()->paragraph(),
            'enlace_repositorio' => fake()->optional()->url(),
            'enlace_despliegue' => fake()->optional()->url(),
            'calificacion' => fake()->optional()->randomFloat(2, 1, 10),
            'es_destacado' => fake()->boolean(20),
        ];
    }

    public function sinCalificar(): static
    {
        return $this->state(fn (array $attributes) => [
            'calificacion' => null,
        ]);
    }

    public function calificado(): static
    {
        return $this->state(fn (array $attributes) => [
            'calificacion' => fake()->randomFloat(2, 1, 10),
        ]);
    }

    public function destacado(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_destacado' => true,
        ]);
    }
}