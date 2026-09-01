<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\TutorLaboral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaModuleTest extends TestCase
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

    public function test_admin_can_view_empresas_index(): void
    {
        $admin = $this->createAdmin();
        Empresa::factory()->count(3)->create(['is_active' => true]);

        $response = $this->actingAs($admin)->get(route('empresas.index'));

        $response->assertOk();
        $response->assertViewHas('empresas');
    }

    public function test_profesor_cannot_view_empresas(): void
    {
        $profesor = $this->createProfesor();
        $response = $this->actingAs($profesor)->get(route('empresas.index'));
        $response->assertStatus(403);
    }

    public function test_alumno_cannot_view_empresas(): void
    {
        $alumno = $this->createAlumno();
        $response = $this->actingAs($alumno)->get(route('empresas.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_see_create_form(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('empresas.create'));
        $response->assertOk();
    }

    public function test_admin_can_create_empresa(): void
    {
        $admin = $this->createAdmin();
        $ciclo = Ciclo::factory()->create();
        $linea = Linea::factory()->create(['ciclo_id' => $ciclo->id]);
        $grupo = Grupo::factory()->create(['linea_id' => $linea->id]);
        $alumno = Alumno::factory()->create();
        $alumno->grupos()->attach($grupo->id);
        $cif = 'A' . uniqid() . '1234';

        $response = $this->actingAs($admin)->post(route('empresas.store'), [
            'nombre' => 'Test Empresa S.L.',
            'cif' => $cif,
            'direccion' => 'Calle Test 123, Granada',
            'telefono' => '958123456',
            'email' => 'contacto@testempresa.com',
            'responsable_nombre' => 'Juan Test',
            'responsable_dni' => '12345678A',
            'tutores' => [
                ['nombre' => 'Tutor 1', 'email' => 'tutor1@test.com', 'telefono' => '600111222'],
                ['nombre' => 'Tutor 2', 'email' => 'tutor2@test.com', 'telefono' => '600333444'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('empresas', ['nombre' => 'Test Empresa S.L.', 'cif' => $cif]);
        $this->assertDatabaseHas('tutores_laborales', ['nombre' => 'Tutor 1']);
        $this->assertDatabaseHas('tutores_laborales', ['nombre' => 'Tutor 2']);
    }

    public function test_empresa_store_requires_validation(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->post(route('empresas.store'), []);
        $response->assertSessionHasErrors(['nombre', 'cif']);
    }

    public function test_empresa_store_requires_unique_cif(): void
    {
        $admin = $this->createAdmin();
        Empresa::factory()->create(['cif' => 'A12345678']);
        $response = $this->actingAs($admin)->post(route('empresas.store'), [
            'nombre' => 'Otra Empresa',
            'cif' => 'A12345678',
        ]);
        $response->assertSessionHasErrors('cif');
    }

    public function test_admin_can_see_empresa_detail(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create();
        TutorLaboral::factory()->create(['empresa_id' => $empresa->id]);

        $response = $this->actingAs($admin)->get(route('empresas.show', $empresa));
        $response->assertOk();
        $response->assertViewHas('empresa', fn($e) => $e->id === $empresa->id);
    }

    public function test_admin_can_see_edit_form(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $linea = Linea::factory()->create(['ciclo_id' => $ciclo->id]);
        $grupo = Grupo::factory()->create(['linea_id' => $linea->id]);
        $alumno = Alumno::factory()->create();
        $alumno->grupos()->attach($grupo->id);
        Convenio::factory()->create(['empresa_id' => $empresa->id, 'alumno_id' => $alumno->id, 'grupo_id' => $grupo->id]);

        $response = $this->actingAs($admin)->get(route('empresas.edit', $empresa));
        $response->assertOk();
        $response->assertViewHas('empresa', fn($e) => $e->id === $empresa->id);
    }

    public function test_admin_can_update_empresa(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create(['nombre' => 'Old Name']);

        $response = $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => 'New Name',
            'cif' => $empresa->cif,
            'direccion' => 'New Address',
            'telefono' => '958999888',
            'email' => 'new@test.com',
            'responsable_nombre' => 'New Responsible',
            'responsable_dni' => '87654321B',
            'tutores' => [],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('empresas', ['id' => $empresa->id, 'nombre' => 'New Name']);
    }

    public function test_admin_can_update_tutores(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create();
        $tutor1 = TutorLaboral::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Tutor Old']);

        $response = $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => $empresa->nombre,
            'cif' => $empresa->cif,
            'tutores' => [
                ['id' => (string)$tutor1->id, 'nombre' => 'Tutor New', 'email' => 'new@test.com'],
            ],
        ]);

        $this->assertDatabaseHas('tutores_laborales', [
            'id' => $tutor1->id,
            'nombre' => 'Tutor New',
        ]);
    }

    public function test_admin_can_add_new_tutor_on_update(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => $empresa->nombre,
            'cif' => $empresa->cif,
            'tutores' => [
                ['nombre' => 'New Tutor', 'email' => 'newtutor@test.com'],
            ],
        ]);

        $this->assertDatabaseHas('tutores_laborales', [
            'empresa_id' => $empresa->id,
            'nombre' => 'New Tutor',
        ]);
    }

    public function test_admin_can_view_show_with_ofertas_y_practicas_del_curso_actual_y_anterior(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create();

        $cursoActual = \App\Models\CursoAcademico::factory()->create(['is_active' => true, 'fecha_inicio' => now()->subMonths(2)]);
        $cursoAnterior = \App\Models\CursoAcademico::factory()->create(['is_active' => false, 'fecha_inicio' => now()->subYear()]);

        $alumnoA = \App\Models\Alumno::factory()->create();
        $alumnoB = \App\Models\Alumno::factory()->create();

        // Oferta del curso actual y oferta del curso anterior
        \App\Models\OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'curso_academico_id' => $cursoActual->id,
            'especialidad_requerida' => 'DAW Actual',
        ]);
        \App\Models\OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'curso_academico_id' => $cursoAnterior->id,
            'especialidad_requerida' => 'DAW Anterior',
        ]);

        // Práctica del curso actual
        \App\Models\Practica::factory()->create([
            'empresa_id' => $empresa->id,
            'alumno_id' => $alumnoA->id,
            'curso_academico_id' => $cursoActual->id,
        ]);

        $response = $this->actingAs($admin)->get(route('empresas.show', $empresa));

        $response->assertOk();
        $response->assertViewHas('bloques');

        $bloques = $response->viewData('bloques');

        // Se agrupan por curso y el actual va primero
        $this->assertCount(2, $bloques);
        $this->assertTrue($bloques->first()->es_actual);
        $this->assertSame($cursoActual->id, $bloques->first()->curso->id);

        // La oferta del curso actual está en el bloque actual
        $this->assertEquals(1, $bloques->first()->ofertas->count());
        $this->assertEquals('DAW Actual', $bloques->first()->ofertas->first()->especialidad_requerida);
        $this->assertEquals(1, $bloques->first()->practicas->count());

        // La oferta del curso anterior está en el bloque anterior
        $this->assertSame($cursoAnterior->id, $bloques->last()->curso->id);
        $this->assertEquals(1, $bloques->last()->ofertas->count());
        $this->assertEquals('DAW Anterior', $bloques->last()->ofertas->first()->especialidad_requerida);
    }

    public function test_empresa_show_agrupa_sin_curso_como_actual(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create();

        \App\Models\OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'curso_academico_id' => null,
            'especialidad_requerida' => 'Oferta Sin Curso',
        ]);

        $response = $this->actingAs($admin)->get(route('empresas.show', $empresa));

        $response->assertOk();
        $response->assertViewHas('bloques');

        $bloques = $response->viewData('bloques');
        $this->assertCount(1, $bloques);
        $this->assertTrue($bloques->first()->es_actual);
        $this->assertEquals(1, $bloques->first()->ofertas->count());
    }

    public function test_admin_can_deactivate_empresa(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('empresas.deactivate', $empresa));
        $response->assertRedirect();
        $this->assertFalse(Empresa::find($empresa->id)->is_active);
    }

    public function test_admin_can_reactivate_empresa(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create(['is_active' => false]);

        $response = $this->actingAs($admin)->post(route('empresas.reactivate', $empresa));
        $response->assertRedirect();
        $this->assertTrue(Empresa::find($empresa->id)->is_active);
    }

    public function test_admin_can_delete_empresa(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($admin)->delete(route('empresas.destroy', $empresa));
        $response->assertRedirect(route('empresas.index'));
        $this->assertDatabaseMissing('empresas', ['id' => $empresa->id]);
    }

    public function test_empresas_index_can_filter_by_search(): void
    {
        $admin = $this->createAdmin();
        Empresa::factory()->create(['nombre' => 'Empresa A Test']);
        Empresa::factory()->create(['nombre' => 'Empresa B Test']);

        $response = $this->actingAs($admin)->get(route('empresas.index', ['search' => 'A Test']));
        $response->assertOk();
        $response->assertSee('Empresa A');
        $response->assertDontSee('Empresa B');
    }

    public function test_empresa_model_has_correct_table(): void
    {
        $empresa = Empresa::factory()->create();
        $this->assertEquals('empresas', $empresa->getTable());
    }

    public function test_tutor_laboral_belongs_to_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $tutor = TutorLaboral::factory()->create(['empresa_id' => $empresa->id]);

        $this->assertInstanceOf(Empresa::class, $tutor->empresa);
        $this->assertEquals($empresa->id, $tutor->empresa->id);
    }

    public function test_convenio_belongs_to_empresa_and_alumno(): void
    {
        $empresa = Empresa::factory()->create();
        $alumno = Alumno::factory()->create();
        $convenio = Convenio::factory()->create([
            'empresa_id' => $empresa->id,
            'alumno_id' => $alumno->id,
        ]);

        $this->assertInstanceOf(Empresa::class, $convenio->empresa);
        $this->assertInstanceOf(Alumno::class, $convenio->alumno);
    }

    public function test_convenio_esta_firmado(): void
    {
        $empresa = Empresa::factory()->create();
        $alumno = Alumno::factory()->create();
        $grupo = \App\Models\Grupo::factory()->create();

        $convenio = Convenio::create([
            'empresa_id' => $empresa->id,
            'alumno_id' => $alumno->id,
            'grupo_id' => $grupo->id,
            'estado' => 'firmado',
        ]);
        $this->assertTrue($convenio->estaFirmado());

        $convenio2 = Convenio::create([
            'empresa_id' => $empresa->id,
            'alumno_id' => $alumno->id,
            'grupo_id' => $grupo->id,
            'estado' => 'no_firmado',
        ]);
        $this->assertFalse($convenio2->estaFirmado());
    }

    public function test_convenio_scope_firmados(): void
    {
        Convenio::query()->delete();
        
        $empresa = Empresa::factory()->create();
        $alumno = Alumno::factory()->create();
        $grupo = \App\Models\Grupo::factory()->create();
        Convenio::create(['empresa_id' => $empresa->id, 'alumno_id' => $alumno->id, 'grupo_id' => $grupo->id, 'estado' => 'firmado']);
        Convenio::create(['empresa_id' => $empresa->id, 'alumno_id' => $alumno->id, 'grupo_id' => $grupo->id, 'estado' => 'no_firmado']);

        $firmados = Convenio::firmados()->get();
        $this->assertCount(1, $firmados);
    }

    public function test_empresa_scope_active(): void
    {
        Empresa::query()->delete();
        
        Empresa::create(['nombre' => 'Active', 'cif' => 'A' . uniqid() . 'x', 'is_active' => true]);
        Empresa::create(['nombre' => 'Inactive', 'cif' => 'B' . uniqid() . 'x', 'is_active' => false]);

        $active = Empresa::active()->get();
        $this->assertCount(1, $active);
    }
}