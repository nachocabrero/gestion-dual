<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\User;
use Tests\TestCase;

class AlumnoPracticasPrimeroSoloSegundoTest extends TestCase
{
    private function createAcademicStructure(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'IFC_TEST_2'],
            ['nombre' => 'Informática Test 2', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAW_TEST_2'],
            ['familia_id' => $familia->id, 'nombre' => 'DAW Test 2', 'grado' => 'superior', 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAW Mañana Test 2', 'is_active' => true]
        );

        $grupo1 = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 1],
            ['nombre' => '1º DAW - Mañana Test 2', 'is_active' => true]
        );

        $grupo2 = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 2],
            ['nombre' => '2º DAW - Mañana Test 2', 'is_active' => true]
        );

        return compact('curso', 'familia', 'ciclo', 'linea', 'grupo1', 'grupo2');
    }

    public function test_student_in_second_year_sees_practicas_primero_block_in_profile_edit(): void
    {
        $structure = $this->createAcademicStructure();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = Alumno::create(['user_id' => $user->id]);
        $alumno->grupos()->attach($structure['grupo2']->id, [
            'curso_academico_id' => $structure['curso']->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('profile.edit'));
        $response->assertOk();
        $response->assertSee('Prácticas Realizadas en 1º Curso y Situación Académica');
        $response->assertSee('Matriculado en 2º');
    }

    public function test_student_in_first_year_does_not_see_practicas_primero_block_in_profile_edit(): void
    {
        $structure = $this->createAcademicStructure();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = Alumno::create(['user_id' => $user->id]);
        $alumno->grupos()->attach($structure['grupo1']->id, [
            'curso_academico_id' => $structure['curso']->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('profile.edit'));
        $response->assertOk();
        $response->assertDontSee('Prácticas Realizadas en 1º Curso y Situación Académica');
        $response->assertDontSee('Matriculado en 2º');
    }

    public function test_first_year_student_saving_profile_does_not_alter_practicas_primero_fields(): void
    {
        $structure = $this->createAcademicStructure();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'sin_practicas_primero' => false,
            'repitiendo_modulos_primero' => false,
            'empresa_practicas_primero' => null,
            'horas_practicas_primero' => null,
        ]);
        $alumno->grupos()->attach($structure['grupo1']->id, [
            'curso_academico_id' => $structure['curso']->id,
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('profile.update'), [
            'name' => 'Alumno 1º Modificado',
            'email' => $user->email,
            'telefono' => '654987321',
            'tiene_vehiculo_propio' => '1',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $alumno->refresh();

        $this->assertEquals('654987321', $alumno->telefono);
        $this->assertTrue($alumno->tiene_vehiculo_propio);
        $this->assertFalse($alumno->sin_practicas_primero);
        $this->assertFalse($alumno->repitiendo_modulos_primero);
        $this->assertNull($alumno->empresa_practicas_primero);
        $this->assertNull($alumno->horas_practicas_primero);
    }

    public function test_student_in_second_year_sees_practicas_primero_block_in_alumnos_show(): void
    {
        $structure = $this->createAcademicStructure();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'empresa_practicas_primero' => 'Tech Granada',
            'horas_practicas_primero' => 100,
        ]);
        $alumno->grupos()->attach($structure['grupo2']->id, [
            'curso_academico_id' => $structure['curso']->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertOk();
        $response->assertSee('Prácticas de 1º Curso y Situación Académica');
        $response->assertSee('Tech Granada');
        $response->assertSee('100 horas computables');
    }

    public function test_student_in_first_year_does_not_see_practicas_primero_block_in_alumnos_show(): void
    {
        $structure = $this->createAcademicStructure();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            // Even if fields have residual values, since student is in 1º, block must not appear
            'empresa_practicas_primero' => 'Tech Granada',
            'horas_practicas_primero' => 100,
            'repitiendo_modulos_primero' => true,
        ]);
        $alumno->grupos()->attach($structure['grupo1']->id, [
            'curso_academico_id' => $structure['curso']->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertOk();
        $response->assertDontSee('Prácticas de 1º Curso y Situación Académica');
        $response->assertDontSee('100 horas computables');
    }

    public function test_admin_editing_second_year_student_sees_practicas_primero_block_in_alumnos_edit(): void
    {
        $structure = $this->createAcademicStructure();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO]]);
        $alumno = Alumno::create(['user_id' => $user->id]);
        $alumno->grupos()->attach($structure['grupo2']->id, [
            'curso_academico_id' => $structure['curso']->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('alumnos.edit', $alumno));
        $response->assertOk();
        $response->assertSee('Prácticas de 1º Curso y Situación de Prácticas');
        $response->assertSee('2º Curso');
    }

    public function test_admin_editing_first_year_student_does_not_see_practicas_primero_block_in_alumnos_edit(): void
    {
        $structure = $this->createAcademicStructure();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO]]);
        $alumno = Alumno::create(['user_id' => $user->id]);
        $alumno->grupos()->attach($structure['grupo1']->id, [
            'curso_academico_id' => $structure['curso']->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('alumnos.edit', $alumno));
        $response->assertOk();
        $response->assertDontSee('Prácticas de 1º Curso y Situación de Prácticas');
    }
}
