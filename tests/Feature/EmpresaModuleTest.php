<?php

namespace Tests\Feature;

use App\Models\Ciclo;
use App\Models\Convenio;
use App\Models\Empresa;
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
        Ciclo::factory()->create();

        $response = $this->actingAs($admin)->get(route('empresas.create'));
        $response->assertOk();
        $response->assertViewHas('ciclos');
    }

    public function test_admin_can_create_empresa(): void
    {
        $admin = $this->createAdmin();
        $ciclo = Ciclo::factory()->create();
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
            'convenios' => [
                ['ciclo_id' => $ciclo->id, 'curso_academico' => '26/27'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('empresas', ['nombre' => 'Test Empresa S.L.', 'cif' => $cif]);
        $this->assertDatabaseHas('tutores_laborales', ['nombre' => 'Tutor 1']);
        $this->assertDatabaseHas('tutores_laborales', ['nombre' => 'Tutor 2']);
        $this->assertDatabaseHas('convenios', ['curso_academico' => '26/27']);
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
        Convenio::factory()->create(['empresa_id' => $empresa->id, 'ciclo_id' => $ciclo->id]);

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
            'convenios' => [],
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
            'convenios' => [],
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
            'convenios' => [],
        ]);

        $this->assertDatabaseHas('tutores_laborales', [
            'empresa_id' => $empresa->id,
            'nombre' => 'New Tutor',
        ]);
    }

    public function test_admin_can_update_convenio(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $convenio = Convenio::factory()->create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'estado' => 'no_firmado',
        ]);

        $response = $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => $empresa->nombre,
            'cif' => $empresa->cif,
            'tutores' => [],
            'convenios' => [
                ['id' => (string)$convenio->id, 'ciclo_id' => $ciclo->id, 'curso_academico' => '26/27', 'estado' => 'firmado', 'fecha_firma' => '2026-09-01'],
            ],
        ]);

        $this->assertDatabaseHas('convenios', [
            'id' => $convenio->id,
            'estado' => 'firmado',
        ]);
        $this->assertEquals('2026-09-01', $convenio->fresh()->fecha_firma->format('Y-m-d'));
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

    public function test_convenio_belongs_to_empresa_and_ciclo(): void
    {
        $empresa = Empresa::factory()->create();
        $ciclo = Ciclo::factory()->create();
        $convenio = Convenio::factory()->create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
        ]);

        $this->assertInstanceOf(Empresa::class, $convenio->empresa);
        $this->assertInstanceOf(Ciclo::class, $convenio->ciclo);
    }

    public function test_convenio_esta_firmado(): void
    {
        $empresa = Empresa::factory()->create();
        $ciclo = Ciclo::factory()->create();

        $convenio = Convenio::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico' => '26/27',
            'estado' => 'firmado',
        ]);
        $this->assertTrue($convenio->estaFirmado());

        $convenio2 = Convenio::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico' => '27/28',
            'estado' => 'no_firmado',
        ]);
        $this->assertFalse($convenio2->estaFirmado());
    }

    public function test_convenio_scope_firmados(): void
    {
        Convenio::query()->delete();
        
        $empresa = Empresa::factory()->create();
        $ciclo = Ciclo::factory()->create();
        Convenio::create(['empresa_id' => $empresa->id, 'ciclo_id' => $ciclo->id, 'curso_academico' => '26/27', 'estado' => 'firmado']);
        Convenio::create(['empresa_id' => $empresa->id, 'ciclo_id' => $ciclo->id, 'curso_academico' => '27/28', 'estado' => 'no_firmado']);

        $firmados = Convenio::firmados()->get();
        $this->assertCount(1, $firmados);
    }

    public function test_empresa_scope_active(): void
    {
        // Clean first
        Empresa::query()->delete();
        
        Empresa::create(['nombre' => 'Active', 'cif' => 'A' . uniqid() . 'x', 'is_active' => true]);
        Empresa::create(['nombre' => 'Inactive', 'cif' => 'B' . uniqid() . 'x', 'is_active' => false]);

        $active = Empresa::active()->get();
        $this->assertCount(1, $active);
    }
}