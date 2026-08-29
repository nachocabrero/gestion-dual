<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'cif' => fake()->unique()->regexify('[A-Z][0-9]{7}[A-Z]'),
            'direccion' => fake()->address(),
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'responsable_nombre' => fake()->name(),
            'responsable_dni' => fake()->regexify('[0-9]{8}[A-Z]'),
            'is_active' => true,
        ];
    }
}