<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Practica;
use App\Models\TutorLaboral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Practica>
 */
class PracticaFactory extends Factory
{
    protected $model = Practica::class;

    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-2 months', '+1 month');
        $fin = fake()->dateTimeBetween($inicio, '+6 months');

        return [
            'alumno_id' => Alumno::factory(),
            'empresa_id' => Empresa::factory(),
            'tutor_laboral_id' => TutorLaboral::factory(),
            'curso_academico_id' => CursoAcademico::factory(),
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'horas_acumuladas' => fake()->numberBetween(200, 800),
            'convenio_firmado' => fake()->boolean(70),
        ];
    }

    /**
     * Practica en curso.
     */
    public function enCurso(): static
    {
        return $this->state(function (array $attributes) {
            $inicio = now()->subDays(fake()->numberBetween(1, 30));
            $fin = now()->addDays(fake()->numberBetween(30, 120));
            return [
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
            ];
        });
    }

    /**
     * Practica finalizada.
     */
    public function finalizada(): static
    {
        return $this->state(function (array $attributes) {
            $inicio = now()->subMonths(fake()->numberBetween(3, 12));
            $fin = now()->subDays(fake()->numberBetween(1, 60));
            return [
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
            ];
        });
    }

    /**
     * Sin convenio firmado.
     */
    public function sinConvenio(): static
    {
        return $this->state(['convenio_firmado' => false]);
    }

    /**
     * Con convenio firmado.
     */
    public function conConvenio(): static
    {
        return $this->state(['convenio_firmado' => true]);
    }

    /**
     * Mínimo 500 horas.
     */
    public function conMinimo500Horas(): static
    {
        return $this->state(['horas_acumuladas' => fake()->numberBetween(500, 800)]);
    }
}