<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Practica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoPracticasPrimeroTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicStructure(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'IFC_TEST'],
            ['nombre' => 'Informática Test', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAW_TEST'],
            ['familia_id' => $familia->id, 'nombre' => 'DAW Test', 'grado' => 'superior', 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAW Mañana Test', 'is_active' => true]
        );

        $grupo1 = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 1],
            ['nombre' => '1º DAW - Mañana Test', 'is_active' => true]
        );

        $grupo2 = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 2],
            ['nombre' => '2º DAW - Mañana Test', 'is_active' => true]
        );

        return compact('curso', 'familia', 'ciclo', 'linea', 'grupo1', 'grupo2');
    }

    public function test_student_in_second_year_can_view_and_update_first_year_practices_in_profile(): void
    {
        $structure = $this->createAcademicStructure();

        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'empresa_practicas_primero' => null,
            'horas_practicas_primero' => null,
        ]);

        $alumno->grupos()->attach($structure['grupo2']->id, [
            'curso_academico_id' => $structure['curso']->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('profile.edit'));
        $response->assertOk();
        $response->assertSee('Prácticas Realizadas en 1º Curso');
        $response->assertSee('Matriculado en 2º');

        $updateResponse = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'empresa_practicas_primero' => 'NTT Data Granada',
            'horas_practicas_primero' => 120,
        ]);

        $updateResponse->assertRedirect(route('profile.edit'));
        $alumno->refresh();

        $this->assertEquals('NTT Data Granada', $alumno->empresa_practicas_primero);
        $this->assertEquals(120, $alumno->horas_practicas_primero);
    }

    public function test_student_registration_with_second_year_group_saves_first_year_practices(): void
    {
        $structure = $this->createAcademicStructure();

        $response = $this->post(route('register'), [
            'name' => 'Ana Alumna 2',
            'email' => 'ana2@ieshlanz.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'alumno',
            'grupos' => [$structure['grupo2']->id],
            'empresa_practicas_primero' => 'Everis Spain',
            'horas_practicas_primero' => 150,
            'consent_rgpd' => '1',
            'privacy_policy' => '1',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'ana2@ieshlanz.es')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->alumno);
        $this->assertEquals('Everis Spain', $user->alumno->empresa_practicas_primero);
        $this->assertEquals(150, $user->alumno->horas_practicas_primero);
    }

    public function test_admin_can_set_and_edit_first_year_practices_in_alumno_crud(): void
    {
        $structure = $this->createAcademicStructure();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $this->actingAs($admin);

        // Create
        $response = $this->post(route('alumnos.store'), [
            'name' => 'Carlos Segundo',
            'email' => 'carlos@ieshlanz.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'grupos_ids' => [$structure['grupo2']->id],
            'empresa_practicas_primero' => 'Innova Tech',
            'horas_practicas_primero' => 160,
        ]);

        $response->assertRedirect(route('alumnos.index'));

        $alumno = Alumno::whereHas('user', fn($q) => $q->where('email', 'carlos@ieshlanz.es'))->first();
        $this->assertNotNull($alumno);
        $this->assertEquals('Innova Tech', $alumno->empresa_practicas_primero);
        $this->assertEquals(160, $alumno->horas_practicas_primero);

        // Edit
        $updateResponse = $this->put(route('alumnos.update', $alumno), [
            'name' => 'Carlos Segundo Modificado',
            'email' => 'carlos@ieshlanz.es',
            'grupos_ids' => [$structure['grupo2']->id],
            'empresa_practicas_primero' => 'Accenture Granada',
            'horas_practicas_primero' => 200,
        ]);

        $updateResponse->assertRedirect(route('alumnos.show', $alumno));
        $alumno->refresh();

        $this->assertEquals('Accenture Granada', $alumno->empresa_practicas_primero);
        $this->assertEquals(200, $alumno->horas_practicas_primero);

        // Show view asserts
        $showResponse = $this->get(route('alumnos.show', $alumno));
        $showResponse->assertOk();
        $showResponse->assertSee('Accenture Granada');
        $showResponse->assertSee('200 horas computables');
    }

    public function test_total_horas_alumno_computes_first_year_hours(): void
    {
        $structure = $this->createAcademicStructure();

        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO]]);
        $alumno = Alumno::create([
            'user_id' => $user->id,
            'empresa_practicas_primero' => 'Empresa 1º Curso',
            'horas_practicas_primero' => 100,
        ]);
        $alumno->grupos()->attach($structure['grupo2']->id);

        $empresa = Empresa::firstOrCreate(
            ['cif' => 'B99887766'],
            [
                'nombre' => 'Empresa 2º Curso Test',
                'direccion' => 'Calle Gran Vía 1',
                'telefono' => '958000000',
                'email' => 'info@empresa2test.es',
                'is_active' => true,
            ]
        );

        $practica = Practica::create([
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'curso_academico_id' => $structure['curso']->id,
            'fecha_inicio' => '2026-10-01',
            'fecha_fin' => '2027-02-15',
            'horas_acumuladas' => 250,
            'convenio_firmado' => true,
        ]);

        // totalHorasAlumno includes 250h from 2nd year + 100h from 1st year = 350h
        $this->assertEquals(350, $practica->totalHorasAlumno());
    }

    public function test_negative_hours_are_rejected(): void
    {
        $structure = $this->createAcademicStructure();
        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO]]);
        $alumno = Alumno::create(['user_id' => $user->id]);
        $alumno->grupos()->attach($structure['grupo2']->id);

        $this->actingAs($user);

        $response = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'horas_practicas_primero' => -50,
        ]);

        $response->assertSessionHasErrors('horas_practicas_primero');
    }
}
