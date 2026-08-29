<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alumno>
 */
class AlumnoFactory extends Factory
{
    protected $model = Alumno::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO]]),
            'linkedin_url' => fake()->optional()->url(),
            'telefono' => fake()->phoneNumber(),
            'domicilio' => fake()->address(),
            'fecha_nacimiento' => fake()->date('Y-m-d', '2005-12-31'),
        ];
    }
}