<?php

namespace Database\Factories;

use App\Models\Asignatura;
use App\Models\Ciclo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Asignatura>
 */
class AsignaturaFactory extends Factory
{
    protected $model = Asignatura::class;

    public function definition(): array
    {
        $ciclo = Ciclo::inRandomOrder()->first();
        $codigos = [
            'DAWES' => 'Desarrollo en Entorno Servidor',
            'DAWEC' => 'Desarrollo en Entorno Cliente',
            'BD' => 'Bases de Datos',
            'FG' => 'Formación y Orientación Laboral',
            'DACC' => 'Desarrollo de Interfaces Web',
            'PRC' => 'Proyecto de Desarrollo de Aplicaciones Web',
            'DAMES' => 'Desarrollo en Entorno Servidor',
            'DAMC' => 'Desarrollo en Entorno Cliente',
            'DAIM' => 'Desarrollo de Aplicaciones Multiplataforma',
            'PRD' => 'Proyecto de Desarrollo de Aplicaciones Multiplataforma',
            'SIO' => 'Sistemas Informáticos en Red',
            'SI' => 'Sistemas Informáticos',
            'ACC' => 'Acceso a Datos',
            'SS' => 'Seguridad Informática',
            'PPR' => 'Proyecto de Administración de Sistemas Informáticos en Red',
        ];

        $codigo = fake()->randomElement(array_keys($codigos));
        $nombre = $codigos[$codigo];

        return [
            'ciclo_id' => $ciclo?->id ?? Ciclo::first()->id,
            'codigo' => $codigo,
            'nombre' => $nombre,
            'horas_semanales' => fake()->numberBetween(2, 8),
            'es_practicas' => in_array($codigo, ['PRC', 'PRD', 'PPR']),
            'is_active' => true,
        ];
    }
}