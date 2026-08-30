<?php

namespace Database\Factories;

use App\Models\Sustitucion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Sustitucion>
 */
class SustitucionFactory extends Factory
{
    protected $model = Sustitucion::class;

    public function definition(): array
    {
        $profesores = User::whereJsonContains('roles', 'profesor')->get();
        $titular = $profesores->random();
        $sustituto = $profesores->where('id', '!=', $titular->id)->random();

        $fechaInicio = fake()->dateTimeBetween('-30 days', '+60 days');

        return [
            'profesor_titular_id' => $titular->id,
            'profesor_sustituto_id' => $sustituto->id,
            'asignatura_id' => null,
            'grupo_id' => null,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => fake()->dateTimeBetween($fechaInicio->format('Y-m-d'), '+30 days')->format('Y-m-d'),
            'motivo' => fake()->optional(0.5)->sentence(),
            'is_active' => true,
        ];
    }
}