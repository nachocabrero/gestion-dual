<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\Convenio;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\TutorLaboral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExclusionProfesoresInactivosTest extends TestCase
{
    use RefreshDatabase;
    private function createAcademicBase(): array
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

        $asig1 = Asignatura::firstOrCreate(
            ['codigo' => 'DAWES_T'],
            ['ciclo_id' => $ciclo->id, 'nombre' => 'Servidor Test', 'horas_semanales' => 8, 'is_active' => true]
        );

        $asig2 = Asignatura::firstOrCreate(
            ['codigo' => 'DAWEC_T'],
            ['ciclo_id' => $ciclo->id, 'nombre' => 'Cliente Test', 'horas_semanales' => 6, 'is_active' => true]
        );

        // Profesor ACTIVO
        $userActivo = User::factory()->create([
            'name' => 'Profesor Activo Exclusivo',
            'email' => 'activo_profesor@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);
        $profesorActivo = Profesor::create([
            'user_id' => $userActivo->id,
            'familia_id' => $familia->id,
            'especialidad' => 'Informática',
            'es_tutor' => false,
        ]);

        // Profesor INACTIVO
        $userInactivo = User::factory()->create([
            'name' => 'Profesor Inactivo Oculto',
            'email' => 'inactivo_profesor@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => false,
            'registration_status' => User::STATUS_APPROVED,
        ]);
        $profesorInactivo = Profesor::create([
            'user_id' => $userInactivo->id,
            'familia_id' => $familia->id,
            'especialidad' => 'Informática',
            'es_tutor' => false,
        ]);

        // Grupo
        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'nombre' => '2º DAW Mañana Test',
            'numero' => 2,
            'is_active' => true,
        ]);

        return compact(
            'curso',
            'familia',
            'ciclo',
            'linea',
            'asig1',
            'asig2',
            'userActivo',
            'profesorActivo',
            'userInactivo',
            'profesorInactivo',
            'grupo'
        );
    }

    public function test_inactive_professor_excluded_from_grupo_equipo_educativo(): void
    {
        $ctx = $this->createAcademicBase();

        // Asignar docente activo a asig1 y docente inactivo a asig2
        $ctx['grupo']->profesores()->attach($ctx['profesorActivo']->id, ['asignatura_id' => $ctx['asig1']->id]);
        $ctx['grupo']->profesores()->attach($ctx['profesorInactivo']->id, ['asignatura_id' => $ctx['asig2']->id]);

        $equipo = $ctx['grupo']->fresh()->equipo_educativo;

        $this->assertTrue($equipo->contains('profesor_id', $ctx['profesorActivo']->id));
        $this->assertFalse($equipo->contains('profesor_id', $ctx['profesorInactivo']->id));
        $this->assertCount(1, $equipo);
    }

    public function test_inactive_group_tutor_excluded_from_equipo_educativo_and_alumnos_show(): void
    {
        $ctx = $this->createAcademicBase();

        // Asignar al usuario inactivo como tutor del grupo (sin docencia con módulo)
        $ctx['grupo']->update(['tutor_id' => $ctx['userInactivo']->id]);

        $equipo = $ctx['grupo']->fresh()->equipo_educativo;
        $this->assertFalse($equipo->contains('user_id', $ctx['userInactivo']->id));

        // Crear alumno en este grupo
        $userAlumno = User::factory()->create([
            'name' => 'Alumno Test Inactivo',
            'email' => 'alumno_inactivo_test@ieshlanz.es',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $userAlumno->id,
            'sin_practicas_primero' => false,
            'repitiendo_modulos_primero' => false,
        ]);
        $alumno->grupos()->attach($ctx['grupo']->id);

        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $this->actingAs($admin);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertOk();

        // En la lista de grupos, el tutor debe figurar como 'No asignado' al estar inactivo
        $response->assertSee('— Tutor/a: No asignado');
        // No debe mostrar el badge de tutor con los datos del usuario inactivo
        $response->assertDontSee($ctx['userInactivo']->email);
    }

    public function test_inactive_tutor_practicas_shows_no_asignado_in_alumnos_show(): void
    {
        $ctx = $this->createAcademicBase();

        $userAlumno = User::factory()->create([
            'name' => 'Alumno Con Tutor Practicas Inactivo',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $userAlumno->id,
            'tutor_practicas_id' => $ctx['userInactivo']->id,
        ]);

        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $this->actingAs($admin);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertOk();
        $response->assertSee('No asignado');
        $response->assertDontSee($ctx['userInactivo']->name);
    }

    public function test_convenios_create_and_edit_excludes_inactive_professors_from_tutor_docente(): void
    {
        $ctx = $this->createAcademicBase();

        $empresa = Empresa::create([
            'nombre' => 'Empresa Convenios Test',
            'cif' => 'B12345678',
            'email' => 'empresa@convenio.com',
            'admite_alumnos_curso_actual' => true,
            'is_active' => true,
        ]);
        $tutorLab = TutorLaboral::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Tutor Laboral Test',
            'dni' => '12345678A',
        ]);
        $userAlumno = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'is_active' => true]);
        $alumno = Alumno::create(['user_id' => $userAlumno->id]);

        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $this->actingAs($admin);

        // CREATE: Verificar que en el select aparece el activo y no el inactivo
        $responseCreate = $this->get(route('empresas.convenios.create', ['empresa_id' => $empresa->id]));
        $responseCreate->assertOk();
        $responseCreate->assertSee($ctx['userActivo']->name);
        $responseCreate->assertDontSee($ctx['userInactivo']->name);

        // Intento de store con profesor inactivo debe fallar validación
        $postResponse = $this->post(route('empresas.convenios.store'), [
            'empresa_id' => $empresa->id,
            'alumno_id' => $alumno->id,
            'tutor_laboral_id' => $tutorLab->id,
            'tutor_docente_id' => $ctx['profesorInactivo']->id,
            'grupo_id' => $ctx['grupo']->id,
            'numero_horas' => 300,
            'fecha_inicio' => '2027-01-10',
            'fecha_fin' => '2027-06-15',
            'estado' => 'no_firmado',
        ]);
        $postResponse->assertSessionHasErrors('tutor_docente_id');

        // Crear un convenio válido con profesor activo
        $convenio = Convenio::create([
            'empresa_id' => $empresa->id,
            'alumno_id' => $alumno->id,
            'tutor_laboral_id' => $tutorLab->id,
            'tutor_docente_id' => $ctx['profesorActivo']->id,
            'grupo_id' => $ctx['grupo']->id,
            'numero_horas' => 300,
            'fecha_inicio' => '2027-01-10',
            'fecha_fin' => '2027-06-15',
            'estado' => 'no_firmado',
        ]);

        // EDIT: Verificar que no aparece el profesor inactivo
        $responseEdit = $this->get(route('empresas.convenios.edit', $convenio));
        $responseEdit->assertOk();
        $responseEdit->assertSee($ctx['userActivo']->name);
        $responseEdit->assertDontSee($ctx['userInactivo']->name);

        // Intento de update con profesor inactivo debe fallar validación
        $putResponse = $this->put(route('empresas.convenios.update', $convenio), [
            'alumno_id' => $alumno->id,
            'tutor_laboral_id' => $tutorLab->id,
            'tutor_docente_id' => $ctx['profesorInactivo']->id,
            'grupo_id' => $ctx['grupo']->id,
            'numero_horas' => 350,
            'fecha_inicio' => '2027-01-10',
            'fecha_fin' => '2027-06-15',
            'estado' => 'firmado',
        ]);
        $putResponse->assertSessionHasErrors('tutor_docente_id');
    }

    public function test_alumnos_create_and_edit_excludes_inactive_professors_from_tutor_practicas(): void
    {
        $ctx = $this->createAcademicBase();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $this->actingAs($admin);

        // CREATE: Debe mostrar activo y no inactivo
        $responseCreate = $this->get(route('alumnos.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee($ctx['userActivo']->name);
        $responseCreate->assertDontSee($ctx['userInactivo']->name);

        // Intento de store con tutor_practicas_id de profesor inactivo debe fallar validación
        $postResponse = $this->post(route('alumnos.store'), [
            'name' => 'Nuevo Alumno Prueba Inactivo',
            'email' => 'nuevo_alumno_inactivo@ieshlanz.es',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'tutor_practicas_id' => $ctx['userInactivo']->id,
        ]);
        $postResponse->assertSessionHasErrors('tutor_practicas_id');

        // Crear alumno válido
        $userAlumno = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'is_active' => true]);
        $alumno = Alumno::create(['user_id' => $userAlumno->id]);

        // EDIT: Debe mostrar activo y no inactivo
        $responseEdit = $this->get(route('alumnos.edit', $alumno));
        $responseEdit->assertOk();
        $responseEdit->assertSee($ctx['userActivo']->name);
        $responseEdit->assertDontSee($ctx['userInactivo']->name);

        // Intento de update con tutor inactivo debe fallar validación
        $putResponse = $this->put(route('alumnos.update', $alumno), [
            'name' => $userAlumno->name,
            'email' => $userAlumno->email,
            'tutor_practicas_id' => $ctx['userInactivo']->id,
        ]);
        $putResponse->assertSessionHasErrors('tutor_practicas_id');
    }

    public function test_admin_grupos_create_and_edit_excludes_inactive_professors_from_tutor(): void
    {
        $ctx = $this->createAcademicBase();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $this->actingAs($admin);

        // CREATE (por línea)
        $responseCreate = $this->get(route('admin.estructura.grupos.create', $ctx['linea']));
        $responseCreate->assertOk();
        $responseCreate->assertSee($ctx['userActivo']->name);
        $responseCreate->assertDontSee($ctx['userInactivo']->name);

        // CREATE (por ciclo)
        $responseCreateCiclo = $this->get(route('admin.estructura.grupos.create-ciclo', $ctx['ciclo']));
        $responseCreateCiclo->assertOk();
        $responseCreateCiclo->assertSee($ctx['userActivo']->name);
        $responseCreateCiclo->assertDontSee($ctx['userInactivo']->name);

        // Intento de store con tutor_id inactivo debe fallar validación
        $postResponse = $this->post(route('admin.estructura.grupos.store', $ctx['linea']), [
            'numero' => 3,
            'nombre' => '3º DAW Test',
            'tutor_id' => $ctx['userInactivo']->id,
        ]);
        $postResponse->assertSessionHasErrors('tutor_id');

        // EDIT
        $responseEdit = $this->get(route('admin.estructura.grupos.edit', $ctx['grupo']));
        $responseEdit->assertOk();
        $responseEdit->assertSee($ctx['userActivo']->name);
        $responseEdit->assertDontSee($ctx['userInactivo']->name);

        // Intento de update con tutor inactivo debe fallar validación
        $putResponse = $this->put(route('admin.estructura.grupos.update', $ctx['grupo']), [
            'linea_id' => $ctx['linea']->id,
            'numero' => $ctx['grupo']->numero,
            'nombre' => $ctx['grupo']->nombre,
            'tutor_id' => $ctx['userInactivo']->id,
        ]);
        $putResponse->assertSessionHasErrors('tutor_id');
    }

    public function test_scope_active_and_is_active_on_user_and_profesor_models(): void
    {
        $ctx = $this->createAcademicBase();

        $this->assertTrue($ctx['userActivo']->isActive());
        $this->assertFalse($ctx['userInactivo']->isActive());

        $this->assertTrue($ctx['profesorActivo']->isActive());
        $this->assertFalse($ctx['profesorInactivo']->isActive());

        $activeUsers = User::active()->pluck('id');
        $this->assertTrue($activeUsers->contains($ctx['userActivo']->id));
        $this->assertFalse($activeUsers->contains($ctx['userInactivo']->id));

        $activeProfesores = Profesor::active()->pluck('id');
        $this->assertTrue($activeProfesores->contains($ctx['profesorActivo']->id));
        $this->assertFalse($activeProfesores->contains($ctx['profesorInactivo']->id));
    }
}
