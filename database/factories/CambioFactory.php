<?php

namespace Database\Factories;

use App\Models\Cambio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CambioFactory extends Factory
{
    protected $model = Cambio::class;

    public function definition(): array
    {
        return [
            'registrable_type' => $this->faker->randomElement([
                'App\\Models\\User',
                'App\\Models\\Alumno',
                'App\\Models\\Convenio',
                'App\\Models\\Proyecto',
            ]),
            'registrable_id' => $this->faker->numberBetween(1, 100),
            'accion' => $this->faker->randomElement(['created', 'updated', 'deleted', 'estado_cambiado']),
            'campo' => $this->faker->randomElement(['estado', 'nombre', 'email', 'descripcion']),
            'antes' => json_encode(['estado' => 'pendiente']),
            'despues' => json_encode(['estado' => 'firmado']),
            'descripcion' => $this->faker->sentence(),
            'usuario_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}