<?php

namespace Database\Factories;

use App\Models\Profesor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profesor>
 */
class ProfesorFactory extends Factory
{
    protected $model = Profesor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'especialidad' => fake()->words(2, true),
            'es_tutor' => false,
            'es_coordinador_dual' => false,
        ];
    }

    public function coordinadorDual(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_coordinador_dual' => true,
        ]);
    }
}