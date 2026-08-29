<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\OfertaPractica;
use App\Models\SolicitudPractica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SolicitudPractica>
 */
class SolicitudPracticaFactory extends Factory
{
    protected $model = SolicitudPractica::class;

    public function definition(): array
    {
        $oferta = OfertaPractica::factory()->create();
        $alumno = Alumno::factory()->create();

        return [
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => fake()->randomElement(['pendiente', 'aceptado', 'rechazado', 'retirado']),
            'motivo_rechazo' => fake()->optional()->sentence(),
        ];
    }
}