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

class AlumnoPracticasChecksTest extends TestCase
{
    private function createAcademicContext(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'IFC_CHK'],
            ['nombre' => 'Informática Checks', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAW_CHK'],
            ['familia_id' => $familia->id, 'nombre' => 'DAW Checks', 'grado' => 'superior', 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAW Mañana Checks', 'is_active' => true]
        );

        $grupo2 = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 2],
            ['nombre' => '2º DAW - Mañana Checks', 'is_active' => true]
        );

        return compact('curso', 'familia', 'ciclo', 'linea', 'grupo2');
    }

    public function test_student_can_check_sin_practicas_primero_in_profile(): void
    {
        $ctx = $this->createAcademicContext();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'empresa_practicas_primero' => 'Empresa Antigua',
            'horas_practicas_primero' => 80,
            'sin_practicas_primero' => false,
        ]);
        $alumno->grupos()->attach($ctx['grupo2']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($user);

        // El perfil muestra la información y anotaciones
        $res = $this->get(route('profile.edit'));
        $res->assertOk();
        $res->assertSee('No he realizado ninguna hora de prácticas en este ciclo formativo anteriormente');
        $res->assertSee('Estoy repitiendo con módulos de 1º curso');
        $res->assertSee('aunque las realizaras hace 2 años');

        // Alumno marca que no hizo prácticas en 1º (convalidado)
        $updateRes = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'sin_practicas_primero' => '1',
            'repitiendo_modulos_primero' => '0',
        ]);
        $updateRes->assertRedirect(route('profile.edit'));

        $alumno->refresh();
        $this->assertTrue($alumno->sin_practicas_primero);
        $this->assertEquals(0, $alumno->horas_practicas_primero);
        $this->assertNull($alumno->empresa_practicas_primero);
        $this->assertFalse($alumno->haRealizadoPracticasPrimero());
    }

    public function test_student_can_check_repitiendo_modulos_primero_in_profile(): void
    {
        $ctx = $this->createAcademicContext();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'empresa_practicas_primero' => 'Innova Tech',
            'horas_practicas_primero' => 100,
            'repitiendo_modulos_primero' => false,
        ]);
        $alumno->grupos()->attach($ctx['grupo2']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($user);

        // Marca que está repitiendo con módulos de 1º
        $updateRes = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'empresa_practicas_primero' => 'Innova Tech',
            'horas_practicas_primero' => 100,
            'repitiendo_modulos_primero' => '1',
        ]);
        $updateRes->assertRedirect(route('profile.edit'));

        $alumno->refresh();
        $this->assertTrue($alumno->repitiendo_modulos_primero);
        $this->assertFalse($alumno->puedeHacerPracticas());
        $this->assertEquals('Innova Tech', $alumno->empresa_practicas_primero);
        $this->assertEquals(100, $alumno->horas_practicas_primero);
    }

    public function test_admin_and_coordinator_can_update_checks_in_alumno_crud(): void
    {
        $ctx = $this->createAcademicContext();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $studentUser = User::factory()->create([
            'name' => 'Mario Repetidor',
            'email' => 'mario@test.es',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $studentUser->id,
            'empresa_practicas_primero' => null,
            'horas_practicas_primero' => null,
        ]);
        $alumno->grupos()->attach($ctx['grupo2']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($admin);

        // Vista de edición muestra campos
        $editRes = $this->get(route('alumnos.edit', $alumno));
        $editRes->assertOk();
        $editRes->assertSee('No ha hecho ninguna hora de prácticas en este ciclo formativo anteriormente');
        $editRes->assertSee('Está repitiendo módulos de 1º (este año no hace prácticas)');

        // Admin marca que repite módulos de 1º
        $putRes = $this->put(route('alumnos.update', $alumno), [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
            'grupos_ids' => [$ctx['grupo2']->id],
            'repitiendo_modulos_primero' => '1',
            'sin_practicas_primero' => '1',
        ]);
        $putRes->assertRedirect(route('alumnos.show', $alumno));

        $alumno->refresh();
        $this->assertTrue($alumno->repitiendo_modulos_primero);
        $this->assertTrue($alumno->sin_practicas_primero);
        $this->assertEquals(0, $alumno->horas_practicas_primero);

        // Vista detalle (show) muestra badges y aviso de no elegible
        $showRes = $this->get(route('alumnos.show', $alumno));
        $showRes->assertOk();
        $showRes->assertSee('Repitiendo módulos de 1º (Sin prácticas)');
        $showRes->assertSee('0h anteriores (Convalidación 1º)');
        $showRes->assertSee('No elegible para prácticas este año');
    }

    public function test_alumnos_index_displays_informative_badges(): void
    {
        $ctx = $this->createAcademicContext();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $u1 = User::factory()->create(['name' => 'Alumno Sin Practicas', 'roles' => [User::ROLE_ALUMNO]]);
        $a1 = Alumno::create(['user_id' => $u1->id, 'sin_practicas_primero' => true, 'horas_practicas_primero' => 0]);
        $a1->grupos()->attach($ctx['grupo2']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $u2 = User::factory()->create(['name' => 'Alumno Repite Primero', 'roles' => [User::ROLE_ALUMNO]]);
        $a2 = Alumno::create(['user_id' => $u2->id, 'repitiendo_modulos_primero' => true]);
        $a2->grupos()->attach($ctx['grupo2']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($admin);
        $res = $this->get(route('alumnos.index'));
        $res->assertOk();
        $res->assertSee('Sin prácticas 1º (0h)');
        $res->assertSee('No hace prácticas (Repite 1º)');
    }
}
