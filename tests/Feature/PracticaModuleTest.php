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

    public function test_profesor_can_access_practicas_index(): void
    {
        $profesorUser = $this->createProfesor();
        $profesor = \App\Models\Profesor::create([
            'user_id' => $profesorUser->id,
            'especialidad' => 'Informática',
        ]);

        $grupo = \App\Models\Grupo::factory()->create(['tutor_id' => $profesorUser->id]);
        $alumno = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $alumno->grupos()->attach($grupo->id);

        $empresa = Empresa::factory()->create();
        $tutor = TutorLaboral::factory()->create(['empresa_id' => $empresa->id]);
        $curso = CursoAcademico::factory()->create();
        $practica = Practica::factory()->create([
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'tutor_laboral_id' => $tutor->id,
            'curso_academico_id' => $curso->id,
        ]);

        // Alumno ajeno fuera del grupo del profesor
        $ajenoUser = $this->createAlumno();
        $ajenoUser->update(['name' => 'Alumno Fuera De Ambito']);
        $alumnoAjeno = Alumno::factory()->create([
            'user_id' => $ajenoUser->id,
        ]);
        $practicaAjena = Practica::factory()->create([
            'alumno_id' => $alumnoAjeno->id,
            'empresa_id' => $empresa->id,
            'tutor_laboral_id' => $tutor->id,
            'curso_academico_id' => $curso->id,
        ]);

        $this->actingAs($profesorUser);
        $response = $this->get(route('practicas.index'));
        $response->assertOk();
        $response->assertSee($alumno->user->name);
        $response->assertDontSee('Alumno Fuera De Ambito');
    }

    public function test_profesor_without_group_can_access_practicas_without_403(): void
    {
        $profesorUser = $this->createProfesor();
        \App\Models\Profesor::create([
            'user_id' => $profesorUser->id,
            'especialidad' => 'Informática',
        ]);

        $this->actingAs($profesorUser);
        $response = $this->get(route('practicas.index'));
        $response->assertOk();
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

    public function test_practica_permite_horas_por_debajo_de_500_acumulando(): void
    {
        $admin = $this->createAdmin();
        $alumno = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
        ]);
        $empresa = Empresa::factory()->create();
        $tutor = TutorLaboral::factory()->create(['empresa_id' => $empresa->id]);
        $curso = CursoAcademico::factory()->create();

        // Primera práctica con menos de 500h: se permite (el mínimo es acumulable)
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
        $response->assertRedirect(route('practicas.index'));
        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('practicas', [
            'alumno_id' => $alumno->id,
            'horas_acumuladas' => 300,
        ]);

        // Segunda práctica: entre las dos se superan las 500h
        $response = $this->post(route('practicas.store'), [
            ...$data,
            'horas_acumuladas' => 250,
            'fecha_inicio' => now()->addMonths(4)->toDateString(),
            'fecha_fin' => now()->addMonths(7)->toDateString(),
        ]);
        $response->assertRedirect(route('practicas.index'));
        $response->assertSessionMissing('warning');
        $this->assertDatabaseHas('practicas', [
            'alumno_id' => $alumno->id,
            'horas_acumuladas' => 250,
        ]);
        $this->assertEquals(550, \App\Models\Practica::where('alumno_id', $alumno->id)->sum('horas_acumuladas'));
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

    public function test_can_open_create_form_with_preselected_parameters_from_oferta(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create(['is_active' => true]);
        $oferta = \App\Models\OfertaPractica::factory()->create(['empresa_id' => $empresa->id]);
        $alumno = Alumno::factory()->create([
            'user_id' => $this->createAlumno()->id,
            'empresa_practicas_primero' => 'Innovatech 1º',
            'horas_practicas_primero' => 180,
        ]);

        $response = $this->actingAs($admin)->get(route('practicas.create', [
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
        ]));

        $response->assertOk();
        $response->assertSee('Innovatech');
        $response->assertSee('180');
        $response->assertViewHas('preselectedOfertaId', $oferta->id);
        $response->assertViewHas('preselectedAlumnoId', $alumno->id);
        $response->assertViewHas('preselectedEmpresaId', $empresa->id);
    }

    public function test_store_practica_with_oferta_id_marks_solicitud_as_aceptado_and_redirects_to_oferta(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create(['is_active' => true]);
        $tutor = TutorLaboral::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Tutor Empresa',
            'dni' => '12345678A',
        ]);
        $curso = CursoAcademico::factory()->create(['is_active' => true]);
        $oferta = \App\Models\OfertaPractica::factory()->create(['empresa_id' => $empresa->id]);
        $alumno = Alumno::factory()->create(['user_id' => $this->createAlumno()->id]);

        $solicitud = \App\Models\SolicitudPractica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($admin)->post(route('practicas.store'), [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'oferta_id' => $oferta->id,
            'tutor_laboral_id' => $tutor->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonths(3)->toDateString(),
            'horas_acumuladas' => 200,
            'convenio_firmado' => 1,
        ]);

        $response->assertRedirect(route('ofertas.show', $oferta->id));
        $this->assertEquals('aceptado', $solicitud->fresh()->estado);
        $this->assertDatabaseHas('practicas', [
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'tutor_laboral_id' => $tutor->id,
        ]);
    }

    public function test_tutor_laboral_must_belong_to_selected_empresa(): void
    {
        $admin = $this->createAdmin();
        $empresa1 = Empresa::factory()->create(['is_active' => true]);
        $empresa2 = Empresa::factory()->create(['is_active' => true]);
        $tutorEmpresa2 = TutorLaboral::create([
            'empresa_id' => $empresa2->id,
            'nombre' => 'Tutor De Otra Empresa',
            'dni' => '87654321B',
        ]);
        $curso = CursoAcademico::factory()->create(['is_active' => true]);
        $alumno = Alumno::factory()->create(['user_id' => $this->createAlumno()->id]);

        $response = $this->actingAs($admin)->post(route('practicas.store'), [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa1->id,
            'tutor_laboral_id' => $tutorEmpresa2->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonths(3)->toDateString(),
            'horas_acumuladas' => 100,
        ]);

        $response->assertSessionHasErrors('tutor_laboral_id');
    }
}