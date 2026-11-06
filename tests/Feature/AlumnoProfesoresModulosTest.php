<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoProfesoresModulosTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicStructure(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'IFC_TEST_MOD'],
            ['nombre' => 'Informática y Comunicaciones Test', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAW_TEST_MOD'],
            ['familia_id' => $familia->id, 'nombre' => 'Desarrollo de Aplicaciones Web Test', 'grado' => 'superior', 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAW Mañana Test', 'is_active' => true]
        );

        $asig1 = Asignatura::firstOrCreate(
            ['codigo' => 'DAWES_T'],
            ['ciclo_id' => $ciclo->id, 'nombre' => 'Desarrollo Web Servidor Test', 'horas_semanales' => 8, 'is_active' => true]
        );

        $asig2 = Asignatura::firstOrCreate(
            ['codigo' => 'DAWEC_T'],
            ['ciclo_id' => $ciclo->id, 'nombre' => 'Desarrollo Web Cliente Test', 'horas_semanales' => 6, 'is_active' => true]
        );

        // Profesor 1 (también será tutor)
        $userTutor = User::factory()->create([
            'name' => 'Profesor Tutor Test',
            'email' => 'tutor_test@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);
        $profesorTutor = Profesor::firstOrCreate(
            ['user_id' => $userTutor->id],
            ['familia_id' => $familia->id, 'especialidad' => 'Informática', 'es_tutor' => true]
        );

        // Profesor 2 (docente)
        $userDocente = User::factory()->create([
            'name' => 'Docente Segundo Test',
            'email' => 'docente_test@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);
        $profesorDocente = Profesor::firstOrCreate(
            ['user_id' => $userDocente->id],
            ['familia_id' => $familia->id, 'especialidad' => 'Informática', 'es_tutor' => false]
        );

        // Grupo
        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAW - Grupo Test Profesores',
            'tutor_id' => $userTutor->id,
            'is_active' => true,
        ]);

        // Asignar profesores y asignaturas al grupo
        $grupo->profesores()->attach($profesorTutor->id, ['asignatura_id' => $asig1->id]);
        $grupo->profesores()->attach($profesorDocente->id, ['asignatura_id' => $asig2->id]);

        return compact('curso', 'familia', 'ciclo', 'linea', 'asig1', 'asig2', 'userTutor', 'profesorTutor', 'userDocente', 'profesorDocente', 'grupo');
    }

    public function test_student_views_teachers_by_module_with_tutor_and_emails(): void
    {
        $ctx = $this->createAcademicStructure();

        $userAlumno = User::factory()->create([
            'name' => 'Alumno Test Perfil',
            'email' => 'alumno_perfil@gmail.com',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $userAlumno->id,
        ]);
        $alumno->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        // Alumno consulta su propio perfil
        $response = $this->actingAs($userAlumno)->get(route('alumnos.show', $alumno));

        $response->assertStatus(200);
        $response->assertSee('Equipo Educativo y Profesorado por Módulo');
        $response->assertSee('2º DAW - Grupo Test Profesores');

        // Módulos
        $response->assertSee('DAWES_T');
        $response->assertSee('Desarrollo Web Servidor Test');
        $response->assertSee('DAWEC_T');
        $response->assertSee('Desarrollo Web Cliente Test');

        // Profesores
        $response->assertSee('Profesor Tutor Test');
        $response->assertSee('Docente Segundo Test');

        // Distintivo de tutor
        $response->assertSee('Tutor/a del grupo');

        // Correos electrónicos con mailto:
        $response->assertSee('mailto:tutor_test@ieshlanz.es');
        $response->assertSee('mailto:docente_test@ieshlanz.es');
        $response->assertSee('tutor_test@ieshlanz.es');
        $response->assertSee('docente_test@ieshlanz.es');
    }

    public function test_tutor_displayed_even_when_not_in_grupo_profesor(): void
    {
        $structure = $this->createAcademicStructure();
        $curso = $structure['curso'];
        $linea = $structure['linea'];

        $userTutorSolo = User::factory()->create([
            'name' => 'Tutor Solo Sin Materia',
            'email' => 'tutorsolo@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);

        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => 'Grupo Tutor Especial Test',
            'tutor_id' => $userTutorSolo->id,
            'is_active' => true,
        ]);

        $userAlumno = User::factory()->create([
            'name' => 'Alumno Sin Materias Asignadas',
            'email' => 'alumnosinmaterias@gmail.com',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $userAlumno->id,
        ]);
        $alumno->grupos()->attach($grupo->id, ['curso_academico_id' => $curso->id]);

        $response = $this->actingAs($userAlumno)->get(route('alumnos.show', $alumno));

        $response->assertStatus(200);
        $response->assertSee('Tutor Solo Sin Materia');
        $response->assertSee('Tutoría de Grupo');
        $response->assertSee('mailto:tutorsolo@ieshlanz.es');
        $response->assertSee('Tutor/a del grupo');
    }

    public function test_student_without_groups_shows_graceful_empty_state(): void
    {
        $userAlumno = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create(['user_id' => $userAlumno->id]);

        $response = $this->actingAs($userAlumno)->get(route('alumnos.show', $alumno));

        $response->assertStatus(200);
        $response->assertSee('El alumno no está matriculado en ningún grupo actualmente.');
    }

    public function test_group_without_teachers_shows_empty_state(): void
    {
        $structure = $this->createAcademicStructure();
        $linea = $structure['linea'];
        $curso = $structure['curso'];

        $grupoVacio = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => 'Grupo Totalmente Vacio Test',
            'tutor_id' => null,
            'is_active' => true,
        ]);

        $userAlumno = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create(['user_id' => $userAlumno->id]);
        $alumno->grupos()->attach($grupoVacio->id, ['curso_academico_id' => $curso->id]);

        $response = $this->actingAs($userAlumno)->get(route('alumnos.show', $alumno));

        $response->assertStatus(200);
        $response->assertSee('Grupo Totalmente Vacio Test');
        $response->assertSee('No hay profesores asignados todavía a este grupo.');
    }

    public function test_equipo_educativo_attribute_sorts_tutor_first(): void
    {
        $ctx = $this->createAcademicStructure();
        $equipo = $ctx['grupo']->equipo_educativo;

        $this->assertNotEmpty($equipo);
        $this->assertTrue($equipo->first()->es_tutor);
        $this->assertEquals('Profesor Tutor Test', $equipo->first()->profesor_nombre);
        $this->assertEquals('tutor_test@ieshlanz.es', $equipo->first()->profesor_email);
    }

    public function test_student_sees_profile_and_teachers_link_on_dashboard_and_sidebar(): void
    {
        $ctx = $this->createAcademicStructure();

        $userAlumno = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create(['user_id' => $userAlumno->id]);
        $alumno->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $response = $this->actingAs($userAlumno)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('alumnos.show', $alumno));
        $response->assertSee('Mis Profesores');
        $response->assertSee('Mi Ficha y Profesores');
    }
}

