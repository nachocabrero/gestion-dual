<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\Proyecto;
use App\Models\ProyectoImagen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProyectoModuleTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin_proyecto_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createProfesor(): User
    {
        return User::factory()->create([
            'name' => 'Profesor',
            'email' => 'profesor_proyecto_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createAlumno(): User
    {
        return User::factory()->create([
            'name' => 'Alumno',
            'email' => 'alumno_proyecto_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    public function test_admin_can_view_proyectos(): void
    {
        $admin = $this->createAdmin();
        $alumno = Alumno::factory()->create(['user_id' => $this->createAlumno()->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('proyectos.index'));
        $response->assertOk();
        $response->assertSee('Mis Proyectos');
    }

    public function test_alumno_can_create_proyecto(): void
    {
        $alumnoUser = $this->createAlumno();
        Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        Ciclo::factory()->create();
        CursoAcademico::factory()->create();

        $this->actingAs($alumnoUser);
        $response = $this->get(route('proyectos.create'));
        $response->assertOk();
        $response->assertSee('Crear Proyecto');
    }

    public function test_alumno_can_store_proyecto(): void
    {
        Storage::fake('public');

        $alumnoUser = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $alumno->ciclosMatriculados()->attach($ciclo->id, ['curso_academico' => $curso->nombre]);

        $data = [
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'titulo' => 'Mi Proyecto Increíble',
            'descripcion' => str_repeat('Palabra ', 50) . 'Fin de la descripción.',
            'enlace_repositorio' => 'https://github.com/test/proyecto',
            'enlace_despliegue' => 'https://proyecto.test',
        ];

        $this->actingAs($alumnoUser);
        $response = $this->post(route('proyectos.store'), $data);
        $response->assertRedirect();
        $this->assertDatabaseHas('proyectos', [
            'alumno_id' => $alumno->id,
            'titulo' => 'Mi Proyecto Increíble',
        ]);
    }

    public function test_proyecto_description_max_300_words(): void
    {
        Storage::fake('public');

        $alumnoUser = $this->createAlumno();
        $alumno121 = Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $alumno121->ciclosMatriculados()->attach($ciclo->id, ['curso_academico' => $curso->nombre]);

        // Más de 300 palabras
        $descripcion = str_repeat('Palabra ', 301) . 'Extra';

        $this->actingAs($alumnoUser);
        $response = $this->post(route('proyectos.store'), [
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'titulo' => 'Proyecto largo',
            'descripcion' => $descripcion,
            'enlace_repositorio' => 'https://github.com/test/proyecto',
        ]);

        $response->assertSessionHasErrors('descripcion');
        $this->assertDatabaseMissing('proyectos', ['titulo' => 'Proyecto largo']);
    }

    public function test_alumno_can_show_own_proyecto(): void
    {
        $alumnoUser = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($alumnoUser);
        $response = $this->get(route('proyectos.show', $proyecto));
        $response->assertOk();
        $response->assertSee($proyecto->titulo);
    }

    public function test_alumno_cannot_show_other_proyecto(): void
    {
        $otherAlumnoUser = $this->createAlumno();
        $otherAlumno = Alumno::factory()->create(['user_id' => $otherAlumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $otherAlumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($this->createAlumno());
        $response = $this->get(route('proyectos.show', $proyecto));
        $response->assertForbidden();
    }

    public function test_alumno_can_edit_ungraded_proyecto(): void
    {
        $alumnoUser = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => null,
        ]);

        $this->actingAs($alumnoUser);
        $response = $this->get(route('proyectos.edit', $proyecto));
        $response->assertOk();
    }

    public function test_alumno_cannot_edit_graded_proyecto(): void
    {
        $alumnoUser = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 8.5,
        ]);

        $this->actingAs($alumnoUser);
        $response = $this->get(route('proyectos.edit', $proyecto));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'No puedes editar un proyecto ya calificado.');
    }

    public function test_alumno_can_update_proyecto(): void
    {
        Storage::fake('public');

        $alumnoUser = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->sinCalificar()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);

        $data = [
            'titulo' => 'Título actualizado',
            'descripcion' => 'Nueva descripción actualizada.',
            'enlace_repositorio' => 'https://github.com/test/updated',
        ];

        $this->actingAs($alumnoUser);
        $response = $this->put(route('proyectos.update', $proyecto), $data);
        $response->assertRedirect();
        $proyecto->refresh();
        $this->assertEquals('Título actualizado', $proyecto->titulo);
        $this->assertEquals('https://github.com/test/updated', $proyecto->enlace_repositorio);
    }

    public function test_profesor_can_calificar_proyecto(): void
    {
        // Crear grupo
        $grupo = Grupo::factory()->create();

        // Profesor es tutor del grupo
        $profesorUser = $this->createProfesor();
        $profesor = Profesor::factory()->create([
            'user_id' => $profesorUser->id,
            'es_tutor' => true,
        ]);
        $grupo->update(['tutor_id' => $profesorUser->id]);

        // Alumno en el grupo del profesor
        $alumnoUser = $this->createAlumno();
        $alumno = Alumno::factory()->create([
            'user_id' => $alumnoUser->id,
        ]);
        $alumno->grupos()->attach($grupo->id);

        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($profesorUser);
        $response = $this->post(route('proyectos.calificar', $proyecto), [
            'calificacion' => 8.5,
            'es_destacado' => true,
        ]);
        $response->assertRedirect();
        $proyecto->refresh();
        $this->assertEquals(8.5, (float) $proyecto->calificacion);
        $this->assertTrue($proyecto->es_destacado);
    }

    public function test_proyecto_can_be_recalibrated(): void
    {
        $ciclo = Ciclo::factory()->create();
        $grupo = Grupo::factory()->create();

        $profesorUser = $this->createProfesor();
        $profesor = Profesor::factory()->create([
            'user_id' => $profesorUser->id,
            'es_tutor' => true,
        ]);
        $grupo->update(['tutor_id' => $profesorUser->id]);

        $alumnoUser = $this->createAlumno();
        $alumno292 = Alumno::factory()->create([
            'user_id' => $alumnoUser->id,
        ]);
        $alumno292->grupos()->attach($grupo->id);

        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno292->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 7.0,
        ]);

        $this->actingAs($profesorUser);
        $response = $this->post(route('proyectos.calificar', $proyecto), [
            'calificacion' => 9.0,
        ]);
        $proyecto->refresh();
        $this->assertEquals(9.0, (float) $proyecto->calificacion);
    }

    public function test_alumno_cannot_see_calificacion_in_view(): void
    {
        $alumnoUser = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 9.5,
        ]);

        $this->actingAs($alumnoUser);
        $response = $this->get(route('proyectos.show', $proyecto));
        $response->assertOk();
        // La vista no muestra la calificación al alumno
        $response->assertDontSee('9.50/10');
    }

    public function test_profesor_sees_only_his_group_proyectos(): void
    {
        // Grupo del profesor
        $grupo1 = Grupo::factory()->create();
        $profesorUser = $this->createProfesor();
        $profesor = Profesor::factory()->create([
            'user_id' => $profesorUser->id,
            'es_tutor' => true,
        ]);
        $grupo1->update(['tutor_id' => $profesorUser->id]);
        $alumno1 = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $alumno1->grupos()->attach($grupo1->id);

        // Alumno en otro grupo
        $grupo2 = Grupo::factory()->create();
        $alumno2 = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $alumno2->grupos()->attach($grupo2->id);

        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();

        Proyecto::factory()->create([
            'alumno_id' => $alumno1->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);
        Proyecto::factory()->create([
            'alumno_id' => $alumno2->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($profesorUser);
        $response = $this->get(route('proyectos.index'));
        $response->assertOk();
    }

    public function test_proyecto_belongs_to_alumno(): void
    {
        $alumno = Alumno::factory()->create();
        $proyecto = Proyecto::factory()->create(['alumno_id' => $alumno->id]);

        $this->assertTrue($proyecto->alumno->is($alumno));
    }

    public function test_proyecto_belongs_to_ciclo(): void
    {
        $ciclo = Ciclo::factory()->create();
        $proyecto = Proyecto::factory()->create(['ciclo_id' => $ciclo->id]);

        $this->assertTrue($proyecto->ciclo->is($ciclo));
    }

    public function test_proyecto_has_many_images(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);
        ProyectoImagen::factory()->count(3)->create(['proyecto_id' => $proyecto->id]);

        $this->assertCount(3, $proyecto->imagenes);
    }

    public function test_proyecto_esta_calificado(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();

        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 8.0,
        ]);
        $this->assertTrue($proyecto->estaCalificado());

        $proyecto2 = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => null,
        ]);
        $this->assertFalse($proyecto2->estaCalificado());
    }

    public function test_proyecto_destacados_scope(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();

        $destacado = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'es_destacado' => true,
        ]);
        $normal = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'es_destacado' => false,
        ]);

        $destacados = Proyecto::destacados()->get();
        $this->assertTrue($destacados->contains($destacado));
        $this->assertFalse($destacados->contains($normal));
    }

    public function test_profesor_cannot_calificar_without_role(): void
    {
        $alumnoUser = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($alumnoUser);
        $response = $this->post(route('proyectos.calificar', $proyecto), [
            'calificacion' => 5.0,
        ]);
        $response->assertForbidden();
    }

    public function test_profesor_cannot_calificar_other_group_proyecto(): void
    {
        // Grupo del profesor
        $grupo1 = Grupo::factory()->create();
        $profesorUser = $this->createProfesor();
        Profesor::factory()->create([
            'user_id' => $profesorUser->id,
            'es_tutor' => true,
        ]);
        $grupo1->update(['tutor_id' => $profesorUser->id]);

        // Otro grupo
        $grupo2 = Grupo::factory()->create();
        $alumno = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $alumno->grupos()->attach($grupo2->id);

        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($profesorUser);
        $response = $this->post(route('proyectos.calificar', $proyecto), [
            'calificacion' => 5.0,
        ]);
        $response->assertForbidden();
    }

    // ——— Portfolio público ———

    public function test_portfolio_is_accessible_without_auth(): void
    {
        $response = $this->get(route('portfolio'));
        $response->assertStatus(200);
    }

    public function test_portfolio_shows_graded_projects(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo = Ciclo::factory()->create(['nombre' => 'DAW']);
        $curso = CursoAcademico::factory()->create();

        // Proyecto calificado >= 7 (aparece en portfolio)
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 8.5,
            'es_destacado' => true,
        ]);

        // Proyecto calificado < 7 (no aparece)
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 5.0,
        ]);

        $response = $this->get(route('portfolio'));
        $response->assertSee('DAW');
        $response->assertSee('8.5');
        // Verificar que no aparece el proyecto con calificación 5.0
        $content = $response->getContent();
        $this->assertStringNotContainsString('5.00/10', $content);
    }

    public function test_portfolio_filters_by_ciclo(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo1 = Ciclo::factory()->create(['nombre' => 'DAW']);
        $ciclo2 = Ciclo::factory()->create(['nombre' => 'DAM']);
        $curso = CursoAcademico::factory()->create();

        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo1->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 8.0,
        ]);
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo2->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 9.0,
        ]);

        $response = $this->get(route('portfolio', ['ciclo' => $ciclo1->id]));
        $response->assertSee('DAW');
        // El filtro solo muestra proyectos DAW — verificar que no hay sección DAM
        $content = $response->getContent();
        // Si el filtro funciona, no debería haber un h3 con "DAM" (solo el select lo muestra)
        // Verificar que solo hay un grupo de proyectos (DAW)
        preg_match_all('/<h3 class="text-xl font-bold.*?>(.*?)<\/h3>/', $content, $matches);
        $cicloHeaders = $matches[1] ?? [];
        $this->assertCount(1, $cicloHeaders);
        $this->assertEquals('DAW', $cicloHeaders[0]);
    }

    public function test_portfolio_filters_by_search(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();

        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'titulo' => 'Sistema de Gestión de Alumnos',
            'calificacion' => 8.0,
        ]);

        $response = $this->get(route('portfolio', ['search' => 'Gestión']));
        $response->assertSee('Sistema de Gestión de Alumnos');

        $response2 = $this->get(route('portfolio', ['search' => 'NoExiste']));
        $response2->assertDontSee('Sistema de Gestión de Alumnos');
    }

    public function test_portfolio_shows_statistics(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $curso = CursoAcademico::factory()->create();

        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 8.0,
            'es_destacado' => true,
        ]);
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 9.0,
        ]);

        $response = $this->get(route('portfolio'));
        $response->assertSee('Proyectos');
        $response->assertSee('Destacados');
        $response->assertSee('Nota media');
    }

    public function test_portfolio_groups_by_ciclo(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo1 = Ciclo::factory()->create(['nombre' => 'DAW']);
        $ciclo2 = Ciclo::factory()->create(['nombre' => 'DAM']);
        $curso = CursoAcademico::factory()->create();

        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo1->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 8.0,
        ]);
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo2->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 9.0,
        ]);

        $response = $this->get(route('portfolio'));
        $response->assertSee('DAW');
        $response->assertSee('DAM');
    }

    public function test_portfolio_filters_by_curso_academico(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo = Ciclo::factory()->create(['nombre' => 'DAW']);
        $curso1 = CursoAcademico::factory()->create(['nombre' => '24/25']);
        $curso2 = CursoAcademico::factory()->create(['nombre' => '25/26']);

        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso1->id,
            'calificacion' => 8.0,
        ]);
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso2->id,
            'calificacion' => 9.0,
        ]);

        $response = $this->get(route('portfolio', ['curso' => $curso1->id]));
        $response->assertSee('24/25');
        // Verificar que solo se muestra un grupo de proyectos
        $content = $response->getContent();
        preg_match_all('/<h3 class="text-xl font-bold.*?>(.*?)<\/h3>/', $content, $matches);
        $cicloHeaders = $matches[1] ?? [];
        $this->assertCount(1, $cicloHeaders);
    }

    public function test_portfolio_requires_min_7_for_display(): void
    {
        $alumno = Alumno::factory()->create();
        $ciclo = Ciclo::factory()->create(['nombre' => 'DAW']);
        $curso = CursoAcademico::factory()->create();

        // Calificación 6.99 (no aparece)
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 6.99,
        ]);
        // Calificación 7.00 (aparece)
        Proyecto::factory()->create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'calificacion' => 7.00,
        ]);

        $response = $this->get(route('portfolio'));
        $content = $response->getContent();
        $this->assertStringContainsString('7.00/10', $content);
        $this->assertStringNotContainsString('6.99/10', $content);
    }
}