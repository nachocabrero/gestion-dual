<?php

namespace Database\Factories;

use App\Models\Familia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Familia>
 */
class FamiliaFactory extends Factory
{
    protected $model = Familia::class;

    public function definition(): array
    {
        return [
            'codigo' => fake()->regexify('[A-Z]{3}[0-9]{3}'),
            'nombre' => fake()->randomElement([
                'Informática y Comunicaciones',
                'Electrónica',
                'Imagen Personal',
                'Imagen para la Industria',
                'Hostelería y Turismo',
                'Imagen Personal y Social',
            ]),
            'descripcion' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}