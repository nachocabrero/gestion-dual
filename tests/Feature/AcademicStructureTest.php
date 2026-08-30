<?php

namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Ciclo;
use App\Models\Linea;
use App\Models\Grupo;
use App\Models\User;
use App\Models\Alumno;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_familia_can_be_created(): void
    {
        $familia = Familia::create([
            'codigo' => 'INFORMATICA',
            'nombre' => 'Familia de Informática',
            'descripcion' => 'Familia profesional de Informática',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('familias', ['codigo' => 'INFORMATICA']);
        $this->assertEquals('Familia de Informática', $familia->nombre);
    }

    public function test_ciclo_belongs_to_familia(): void
    {
        $familia = Familia::create([
            'codigo' => 'INFORMATICA',
            'nombre' => 'Familia de Informática',
            'is_active' => true,
        ]);

        $ciclo = Ciclo::create([
            'familia_id' => $familia->id,
            'codigo' => 'DAW',
            'nombre' => 'Desarrollo de Aplicaciones Web',
            'grado' => 'superior',
            'duracion_anos' => 2,
            'is_active' => true,
        ]);

        $this->assertEquals($familia->id, $ciclo->familia_id);
        $this->assertEquals('DAW', $ciclo->codigo);
        $this->assertEquals('superior', $ciclo->grado);
    }

    public function test_linea_belongs_to_ciclo(): void
    {
        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);

        $linea = Linea::create([
            'ciclo_id' => $ciclo->id,
            'nombre' => 'DAW - Mañana',
            'turno' => 'manana',
            'is_active' => true,
        ]);

        $this->assertEquals($ciclo->id, $linea->ciclo_id);
        $this->assertEquals('manana', $linea->turno);
    }

    public function test_grupo_belongs_to_linea(): void
    {
        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);

        $profesor = User::factory()->create(['roles' => ['profesor']]);

        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'numero' => 1,
            'nombre' => '1º DAW-Manana',
            'tutor_id' => $profesor->id,
            'is_active' => true,
        ]);

        $this->assertEquals($linea->id, $grupo->linea_id);
        $this->assertEquals(1, $grupo->numero);
        $this->assertEquals($profesor->id, $grupo->tutor_id);
    }

    public function test_alumno_belongs_to_user_and_grupo(): void
    {
        $user = User::factory()->create([
            'roles' => ['alumno'],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);
        $grupo = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'is_active' => true]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'linkedin_url' => 'https://linkedin.com/in/test',
            'telefono' => '600123456',
        ]);
        $alumno->grupos()->attach($grupo->id);

        $this->assertEquals($user->id, $alumno->user_id);
        $this->assertTrue($alumno->grupos->contains('id', $grupo->id));
        $this->assertEquals('https://linkedin.com/in/test', $alumno->linkedin_url);
    }

    public function test_alumno_can_be_in_multiple_ciclos(): void
    {
        $user = User::factory()->create([
            'roles' => ['alumno'],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);

        $daw = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $dam = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAM', 'nombre' => 'DAM', 'grado' => 'superior', 'is_active' => true]);

        // Matricular al alumno en ambos ciclos
        $alumno->ciclosMatriculados()->attach($daw->id, ['curso_academico' => '2026-2027']);
        $alumno->ciclosMatriculados()->attach($dam->id, ['curso_academico' => '2026-2027']);

        $this->assertCount(2, $alumno->ciclosMatriculados);
        $this->assertTrue($alumno->ciclosMatriculados->contains('codigo', 'DAW'));
        $this->assertTrue($alumno->ciclosMatriculados->contains('codigo', 'DAM'));
    }

    public function test_familia_active_scope(): void
    {
        $active = Familia::create(['codigo' => 'ACTIVA', 'nombre' => 'Activa', 'is_active' => true]);
        $inactive = Familia::create(['codigo' => 'INACTIVA', 'nombre' => 'Inactiva', 'is_active' => false]);

        $this->assertEquals($active->id, Familia::active()->first()->id);
        $this->assertNotEquals($inactive->id, Familia::active()->first()->id);
    }

    public function test_linea_turno_scope(): void
    {
        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);

        $manana = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Mañana', 'turno' => 'manana', 'is_active' => true]);
        $tarde = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Tarde', 'turno' => 'tarde', 'is_active' => true]);

        $this->assertEquals($manana->id, Linea::manana()->first()->id);
        $this->assertEquals($tarde->id, Linea::tarde()->first()->id);
    }
}