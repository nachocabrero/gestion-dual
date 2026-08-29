<?php

namespace Database\Factories;

use App\Models\TutorLaboral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TutorLaboral>
 */
class TutorLaboralFactory extends Factory
{
    protected $model = TutorLaboral::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->phoneNumber(),
            'empresa_id' => \App\Models\Empresa::factory(),
        ];
    }
}