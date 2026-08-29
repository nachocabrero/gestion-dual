<?php

namespace Database\Factories;

use App\Models\ProyectoImagen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProyectoImagen>
 */
class ProyectoImagenFactory extends Factory
{
    protected $model = ProyectoImagen::class;

    public function definition(): array
    {
        return [
            'url' => fake()->imageUrl(640, 480, 'projects', true),
        ];
    }
}