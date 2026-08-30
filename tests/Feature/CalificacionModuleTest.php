<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asignatura;
use App\Models\Calificacion;
use App\Models\Ciclo;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalificacionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestStructure(): array
    {
        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'turno' => 'manana', 'nombre' => 'DAW Mañana', 'is_active' => true]);
        $grupo = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW Mañana', 'is_active' => true]);
        $asignatura = Asignatura::create(['ciclo_id' => $ciclo->id, 'codigo' => 'DAWES', 'nombre' => 'Desarrollo Servidor', 'horas_semanales' => 6, 'is_active' => true]);

        $alumno = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'nss' => '1234567890A',
            'domicilio' => 'Calle 1',
        ]);
        $alumno->grupos()->attach($grupo->id);

        return ['alumno' => $alumno, 'asignatura' => $asignatura, 'grupo' => $grupo];
    }

    public function test_admin_can_view_calificaciones(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $data = $this->createTestStructure();

        Calificacion::create([
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 7.5,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('calificaciones.index'));
        $response->assertOk();
        $response->assertSee('7.50');
    }

    public function test_admin_can_create_calificacion(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $data = $this->createTestStructure();

        $this->actingAs($admin);
        $response = $this->post(route('calificaciones.store'), [
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 8.5,
            'observaciones' => 'Buen trabajo',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('calificaciones', [
            'alumno_id' => $data['alumno']->id,
            'nota' => 8.5,
        ]);
    }

    public function test_admin_can_update_calificacion(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $data = $this->createTestStructure();

        $cal = Calificacion::create([
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 5.0,
        ]);

        $this->actingAs($admin);
        $response = $this->put(route('calificaciones.update', $cal), [
            'nota' => 6.5,
            'observaciones' => 'Mejora',
        ]);

        $response->assertRedirect();
        $cal->refresh();
        $this->assertEquals(6.5, $cal->nota);
    }

    public function test_admin_can_delete_calificacion(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $data = $this->createTestStructure();

        $cal = Calificacion::create([
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 5.0,
        ]);

        $this->actingAs($admin);
        $response = $this->delete(route('calificaciones.destroy', $cal));
        $response->assertRedirect();

        $this->assertDatabaseMissing('calificaciones', ['id' => $cal->id]);
    }

    public function test_professor_can_view_own_group_calificaciones(): void
    {
        $profesorUser = User::factory()->create(['roles' => [User::ROLE_PROFESOR]]);
        $profesor = \App\Models\Profesor::create(['user_id' => $profesorUser->id, 'especialidad' => 'Informática']);
        $data = $this->createTestStructure();

        // Asignar profesor como tutor del grupo
        $data['grupo']->update(['tutor_id' => $profesorUser->id]);

        Calificacion::create([
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 7.0,
        ]);

        $this->actingAs($profesorUser);
        $response = $this->get(route('calificaciones.index'));
        $response->assertOk();
        $response->assertSee('7.00');
    }

    public function test_professor_cannot_view_other_group_calificaciones(): void
    {
        $profesorUser = User::factory()->create(['roles' => [User::ROLE_PROFESOR]]);
        $profesor = \App\Models\Profesor::create(['user_id' => $profesorUser->id, 'especialidad' => 'Informática']);
        $data = $this->createTestStructure();

        // Otro grupo sin este profesor como tutor
        Calificacion::create([
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 7.0,
        ]);

        $this->actingAs($profesorUser);
        $response = $this->get(route('calificaciones.index'));
        $response->assertOk();
        $response->assertDontSee('7.00');
    }

    public function test_alumno_cannot_access_calificaciones(): void
    {
        $data = $this->createTestStructure();
        $alumno = User::find($data['alumno']->user_id);

        $this->actingAs($alumno);
        $response = $this->get(route('calificaciones.index'));
        $response->assertForbidden();
    }

    public function test_calificacion_show_alumno(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $data = $this->createTestStructure();

        Calificacion::create([
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 7.5,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('calificaciones.show', $data['alumno']));
        $response->assertOk();
        $response->assertSee('7.50');
        $response->assertSee('7.50'); // media
    }

    public function test_duplicate_calificacion_fails(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $data = $this->createTestStructure();

        Calificacion::create([
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 5.0,
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('calificaciones.store'), [
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 6.0,
        ]);

        $response->assertSessionHasErrors(['_error']);
    }

    public function test_invalid_nota_fails(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $data = $this->createTestStructure();

        $this->actingAs($admin);
        $response = $this->post(route('calificaciones.store'), [
            'alumno_id' => $data['alumno']->id,
            'asignatura_id' => $data['asignatura']->id,
            'evaluacion' => 'primera',
            'nota' => 11.0,
        ]);

        $response->assertSessionHasErrors(['nota']);
    }

    public function test_calificacion_model_static_methods(): void
    {
        $data = $this->createTestStructure();

        Calificacion::create(['alumno_id' => $data['alumno']->id, 'asignatura_id' => $data['asignatura']->id, 'evaluacion' => 'primera', 'nota' => 6.0]);
        Calificacion::create(['alumno_id' => $data['alumno']->id, 'asignatura_id' => $data['asignatura']->id, 'evaluacion' => 'segunda', 'nota' => 7.0]);
        Calificacion::create(['alumno_id' => $data['alumno']->id, 'asignatura_id' => $data['asignatura']->id, 'evaluacion' => 'tercera', 'nota' => 8.0]);

        $media = Calificacion::mediaAlumnoAsignatura($data['alumno']->id, $data['asignatura']->id);
        $this->assertEquals(7.0, $media);

        $this->assertTrue(Calificacion::notaValida(5.0));
        $this->assertFalse(Calificacion::notaValida(11.0));
        $this->assertFalse(Calificacion::notaValida(-1.0));
        $this->assertTrue(Calificacion::notaValida(null));

        $this->assertEquals(7.0, Calificacion::escalarNota('B'));
        $this->assertEquals(10.0, Calificacion::escalarNota('S'));
        $this->assertNull(Calificacion::escalarNota(null));
    }
}