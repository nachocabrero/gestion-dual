<?php

namespace Database\Factories;

use App\Models\OfertaPractica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfertaPractica>
 */
class OfertaPracticaFactory extends Factory
{
    protected $model = OfertaPractica::class;

    public function definition(): array
    {
        $creador = User::factory()->create(['roles' => [User::ROLE_PROFESOR]]);

        return [
            'empresa_id' => \App\Models\Empresa::factory(),
            'creador_id' => $creador->id,
            'creador_type' => User::class,
            'especialidad_requerida' => fake()->randomElement(['BD', 'Desarrollo Web', 'Sistemas', 'Redes', 'Frontend']),
            'num_alumnos' => fake()->numberBetween(1, 5),
            'descripcion' => fake()->sentence(),
            'estado' => fake()->randomElement(['pendiente', 'activa', 'cerrada']),
        ];
    }
}