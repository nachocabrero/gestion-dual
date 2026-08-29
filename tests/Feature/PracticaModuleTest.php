<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Practica;
use App\Models\TutorLaboral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticaModuleTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createAlumno(): User
    {
        return User::factory()->create([
            'name' => 'Alumno User',
            'email' => 'alumno_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createProfesor(): User
    {
        return User::factory()->create([
            'name' => 'Profesor User',
            'email' => 'profesor_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    public function test_admin_can_view_practicas_index(): void
    {
        $admin = $this->createAdmin();
        $alumno = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $empresa = Empresa::factory()->create();
        $tutor = TutorLaboral::factory()->create(['empresa_id' => $empresa->id]);
        $curso = CursoAcademico::factory()->create();
        Practica::factory()->create([
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'tutor_laboral_id' => $tutor->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('practicas.index'));
        $response->assertOk();
        $response->assertSee($alumno->user->name);
    }

    public function test_admin_can_create_practica(): void
    {
        $admin = $this->createAdmin();
        $alumno = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $empresa = Empresa::factory()->create();
        $tutor = TutorLaboral::factory()->create(['empresa_id' => $empresa->id]);
        $curso = CursoAcademico::factory()->create();

        $this->actingAs($admin);
        $response = $this->get(route('practicas.create'));
        $response->assertOk();
        $response->assertViewHas('alumnos');
    }

    public function test_admin_can_store_practica(): void
    {
        $admin = $this->createAdmin();
        $alumno = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $empresa = Empresa::factory()->create();
        $tutor = TutorLaboral::factory()->create(['empresa_id' => $empresa->id]);
        $curso = CursoAcademico::factory()->create();

        $this->actingAs($admin);
        $data = [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'tutor_laboral_id' => $tutor->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonths(3)->toDateString(),
            'horas_acumuladas' => 600,
            'convenio_firmado' => true,
        ];
        $response = $this->post(route('practicas.store'), $data);
        $response->assertRedirect(route('practicas.index'));
        $this->assertDatabaseHas('practicas', [
            'alumno_id' => $alumno->id,
            'horas_acumuladas' => 600,
        ]);
    }

    public function test_admin_can_show_practica(): void
    {
        $admin = $this->createAdmin();
        $practica = Practica::factory()->create();

        $this->actingAs($admin);
        $response = $this->get(route('practicas.show', $practica));
        $response->assertOk();
        $response->assertSee($practica->alumno->user->name);
    }

    public function test_admin_can_edit_practica(): void
    {
        $admin = $this->createAdmin();
        $practica = Practica::factory()->create();

        $this->actingAs($admin);
        $response = $this->get(route('practicas.edit', $practica));
        $response->assertOk();
        $response->assertViewHas('practica');
    }

    public function test_admin_can_update_practica(): void
    {
        $admin = $this->createAdmin();
        $practica = Practica::factory()->create(['horas_acumuladas' => 200]);

        $this->actingAs($admin);
        $response = $this->put(route('practicas.update', $practica), [
            'alumno_id' => $practica->alumno_id,
            'empresa_id' => $practica->empresa_id,
            'tutor_laboral_id' => $practica->tutor_laboral_id,
            'curso_academico_id' => $practica->curso_academico_id,
            'fecha_inicio' => $practica->fecha_inicio->toDateString(),
            'fecha_fin' => $practica->fecha_fin?->toDateString() ?? now()->addMonth()->toDateString(),
            'horas_acumuladas' => 500,
            'convenio_firmado' => true,
        ]);
        $response->assertRedirect(route('practicas.show', $practica));
        $this->assertDatabaseHas('practicas', [
            'id' => $practica->id,
            'horas_acumuladas' => 500,
        ]);
    }

    public function test_admin_can_delete_practica(): void
    {
        $admin = $this->createAdmin();
        $practica = Practica::factory()->create();

        $this->actingAs($admin);
        $response = $this->delete(route('practicas.destroy', $practica));
        $response->assertRedirect(route('practicas.index'));
        $this->assertDatabaseMissing('practicas', ['id' => $practica->id]);
    }

    public function test_profesor_cannot_access_practicas(): void
    {
        $profesor = $this->createProfesor();

        $this->actingAs($profesor);
        $response = $this->get(route('practicas.index'));
        $response->assertStatus(403);
    }

    public function test_alumno_can_view_mis_practicas(): void
    {
        $user = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        $practica = Practica::factory()->create(['alumno_id' => $alumno->id]);

        $this->actingAs($user);
        $response = $this->get(route('practicas.mis-practicas'));
        $response->assertOk();
        $response->assertSee($practica->empresa->nombre);
    }

    public function test_practica_validation_minimo_500_horas(): void
    {
        $admin = $this->createAdmin();
        $alumno = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $empresa = Empresa::factory()->create();
        $tutor = TutorLaboral::factory()->create(['empresa_id' => $empresa->id]);
        $curso = CursoAcademico::factory()->create();

        // Menos de 500h en primera práctica del curso
        $this->actingAs($admin);
        $data = [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'tutor_laboral_id' => $tutor->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonths(3)->toDateString(),
            'horas_acumuladas' => 300,
            'convenio_firmado' => false,
        ];
        $response = $this->post(route('practicas.store'), $data);
        $response->assertSessionHasErrors('horas_acumuladas', 'Las prácticas requieren un mínimo de 500 horas entre 1º y 2º de prácticas.');
    }

    public function test_practica_model_static_methods(): void
    {
        $practica = Practica::factory()->create([
            'fecha_inicio' => now()->subDays(10),
            'fecha_fin' => now()->addDays(20),
        ]);

        $this->assertTrue($practica->estaEnCurso());

        $finalizada = Practica::factory()->finalizada()->create();
        $this->assertFalse($finalizada->estaEnCurso());

        $pendiente = Practica::factory()->create([
            'fecha_inicio' => now()->addDays(10),
            'fecha_fin' => now()->addDays(40),
        ]);
        $this->assertFalse($pendiente->estaEnCurso());
    }

    public function test_practica_scope_en_curso(): void
    {
        Practica::factory()->enCurso()->create();
        Practica::factory()->finalizada()->create();
        Practica::factory()->create([
            'fecha_inicio' => now()->addDays(10),
            'fecha_fin' => now()->addDays(40),
        ]);

        $enCurso = Practica::enCurso()->count();
        $this->assertEquals(1, $enCurso);
    }

    public function test_practica_scope_finalizadas(): void
    {
        Practica::factory()->enCurso()->create();
        Practica::factory()->finalizada()->create();

        $finalizadas = Practica::finalizadas()->count();
        $this->assertEquals(1, $finalizadas);
    }

    public function test_practica_scope_pendientes(): void
    {
        Practica::factory()->enCurso()->create();
        Practica::factory()->create([
            'fecha_inicio' => now()->addDays(10),
            'fecha_fin' => now()->addDays(40),
        ]);

        $pendientes = Practica::pendientes()->count();
        $this->assertEquals(1, $pendientes);
    }

    public function test_practica_has_correct_table(): void
    {
        $practica = new Practica();
        $this->assertEquals('practicas', $practica->getTable());
    }

    public function test_practica_belongs_to_alumno(): void
    {
        $practica = Practica::factory()->create();
        $this->assertInstanceOf(Alumno::class, $practica->alumno);
    }

    public function test_practica_belongs_to_empresa(): void
    {
        $practica = Practica::factory()->create();
        $this->assertInstanceOf(Empresa::class, $practica->empresa);
    }

    public function test_practica_belongs_to_tutor_laboral(): void
    {
        $practica = Practica::factory()->create();
        $this->assertInstanceOf(TutorLaboral::class, $practica->tutorLaboral);
    }

    public function test_practica_belongs_to_curso_academico(): void
    {
        $practica = Practica::factory()->create();
        $this->assertInstanceOf(CursoAcademico::class, $practica->cursoAcademico);
    }

    public function test_practica_convenio_firmado_cast(): void
    {
        $practica = Practica::factory()->conConvenio()->create();
        $this->assertTrue($practica->convenio_firmado);

        $practica2 = Practica::factory()->sinConvenio()->create();
        $this->assertFalse($practica2->convenio_firmado);
    }

    public function test_practica_horas_acumuladas_cast(): void
    {
        $practica = Practica::factory()->create(['horas_acumuladas' => 500]);
        $this->assertEquals(500, $practica->horas_acumuladas);
        $this->assertIsInt($practica->horas_acumuladas);
    }
}