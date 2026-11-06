<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaDesactivarPermisosTest extends TestCase
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

    private function createCoordinadorDual(): User
    {
        return User::factory()->create([
            'name' => 'Coordinador Dual User',
            'email' => 'coord_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createProfesor(): User
    {
        $user = User::factory()->create([
            'name' => 'Profesor Docente',
            'email' => 'profesor_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        Profesor::factory()->create([
            'user_id' => $user->id,
            'especialidad' => 'Informática',
        ]);

        return $user;
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

    public function test_admin_sees_deactivate_button_for_active_empresa(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create([
            'nombre' => 'Empresa Activa Alpha',
            'is_active' => true,
            'admite_alumnos_curso_actual' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('empresas.index'));

        $response->assertOk();
        $response->assertSee(route('empresas.deactivate', $empresa));
    }

    public function test_admin_sees_reactivate_button_for_inactive_empresa(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create([
            'nombre' => 'Empresa Inactiva Beta',
            'is_active' => false,
            'admite_alumnos_curso_actual' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('empresas.index', ['active' => '0', 'admite_alumnos' => 'todas']));

        $response->assertOk();
        $response->assertSee(route('empresas.reactivate', $empresa));
    }

    public function test_coordinador_dual_sees_deactivate_button_for_active_empresa(): void
    {
        $coord = $this->createCoordinadorDual();
        $empresa = Empresa::factory()->create([
            'nombre' => 'Empresa Activa Gamma',
            'is_active' => true,
            'admite_alumnos_curso_actual' => true,
        ]);

        $response = $this->actingAs($coord)->get(route('empresas.index'));

        $response->assertOk();
        $response->assertSee(route('empresas.deactivate', $empresa));
    }

    public function test_coordinador_dual_sees_reactivate_button_for_inactive_empresa(): void
    {
        $coord = $this->createCoordinadorDual();
        $empresa = Empresa::factory()->create([
            'nombre' => 'Empresa Inactiva Delta',
            'is_active' => false,
            'admite_alumnos_curso_actual' => true,
        ]);

        $response = $this->actingAs($coord)->get(route('empresas.index', ['active' => '0', 'admite_alumnos' => 'todas']));

        $response->assertOk();
        $response->assertSee(route('empresas.reactivate', $empresa));
    }

    public function test_profesor_does_not_see_deactivate_nor_reactivate_buttons(): void
    {
        $profesor = $this->createProfesor();
        $empresaActiva = Empresa::factory()->create([
            'nombre' => 'Empresa Para Profesor Activa',
            'is_active' => true,
            'admite_alumnos_curso_actual' => true,
        ]);
        $empresaInactiva = Empresa::factory()->create([
            'nombre' => 'Empresa Para Profesor Inactiva',
            'is_active' => false,
            'admite_alumnos_curso_actual' => true,
        ]);

        // En listado general con activa
        $responseActiva = $this->actingAs($profesor)->get(route('empresas.index'));
        $responseActiva->assertOk();
        $responseActiva->assertSee($empresaActiva->nombre);
        $responseActiva->assertDontSee(route('empresas.deactivate', $empresaActiva));
        $responseActiva->assertDontSee(route('empresas.reactivate', $empresaActiva));

        // En filtro inactivas
        $responseInactiva = $this->actingAs($profesor)->get(route('empresas.index', ['active' => '0', 'admite_alumnos' => 'todas']));
        $responseInactiva->assertOk();
        $responseInactiva->assertSee($empresaInactiva->nombre);
        $responseInactiva->assertDontSee(route('empresas.deactivate', $empresaInactiva));
        $responseInactiva->assertDontSee(route('empresas.reactivate', $empresaInactiva));
    }

    public function test_admin_can_deactivate_and_reactivate_empresa(): void
    {
        $admin = $this->createAdmin();
        $empresa = Empresa::factory()->create(['is_active' => true]);

        $resDeactivate = $this->actingAs($admin)->post(route('empresas.deactivate', $empresa));
        $resDeactivate->assertRedirect();
        $this->assertFalse($empresa->fresh()->is_active);

        $resReactivate = $this->actingAs($admin)->post(route('empresas.reactivate', $empresa));
        $resReactivate->assertRedirect();
        $this->assertTrue($empresa->fresh()->is_active);
    }

    public function test_coordinador_dual_can_deactivate_and_reactivate_empresa(): void
    {
        $coord = $this->createCoordinadorDual();
        $empresa = Empresa::factory()->create(['is_active' => true]);

        $resDeactivate = $this->actingAs($coord)->post(route('empresas.deactivate', $empresa));
        $resDeactivate->assertRedirect();
        $this->assertFalse($empresa->fresh()->is_active);

        $resReactivate = $this->actingAs($coord)->post(route('empresas.reactivate', $empresa));
        $resReactivate->assertRedirect();
        $this->assertTrue($empresa->fresh()->is_active);
    }

    public function test_profesor_cannot_deactivate_nor_reactivate_empresa_and_gets_403(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create(['is_active' => true]);

        // Intentar desactivar
        $resDeactivate = $this->actingAs($profesor)->post(route('empresas.deactivate', $empresa));
        $resDeactivate->assertStatus(403);
        $this->assertTrue($empresa->fresh()->is_active);

        // Poner inactiva manualmente y probar reactivar
        $empresa->update(['is_active' => false]);
        $resReactivate = $this->actingAs($profesor)->post(route('empresas.reactivate', $empresa));
        $resReactivate->assertStatus(403);
        $this->assertFalse($empresa->fresh()->is_active);
    }

    public function test_alumno_cannot_deactivate_empresa(): void
    {
        $alumno = $this->createAlumno();
        $empresa = Empresa::factory()->create(['is_active' => true]);

        $response = $this->actingAs($alumno)->post(route('empresas.deactivate', $empresa));
        $response->assertStatus(403);
    }
}
