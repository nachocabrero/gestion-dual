<?php

namespace Database\Factories;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notificacion>
 */
class NotificacionFactory extends Factory
{
    protected $model = Notificacion::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'titulo' => fake()->sentence(3),
            'mensaje' => fake()->paragraph(),
            'tipo' => fake()->randomElement(['empresa_asignada', 'estado_acuerdo', 'proyecto_calificado']),
            'expira_en' => fake()->optional()->dateTimeBetween('+1 day', '+30 days'),
        ];
    }
}