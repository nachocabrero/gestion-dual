<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoRegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function setupAcademicStructure(): array
    {
        $curso = CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-25',
            'is_active' => true,
        ]);

        $suffix = uniqid();
        $familia = Familia::create([
            'nombre' => 'Informática y Comunicaciones ' . $suffix,
            'codigo' => 'IFC' . substr($suffix, 0, 5),
            'is_active' => true,
        ]);

        $ciclo = Ciclo::create([
            'familia_id' => $familia->id,
            'nombre' => 'Desarrollo de Aplicaciones Web ' . $suffix,
            'codigo' => 'DAW' . substr($suffix, 0, 5),
            'grado' => 'superior',
            'duracion_anos' => 2,
            'is_active' => true,
        ]);

        $linea = Linea::create([
            'ciclo_id' => $ciclo->id,
            'nombre' => 'Línea Mañana',
            'turno' => 'manana',
            'is_active' => true,
        ]);

        $grupo1 = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º DAW Mañana',
            'is_active' => true,
        ]);

        $grupo2 = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAW Mañana',
            'is_active' => true,
        ]);

        return [$curso, $grupo1, $grupo2];
    }

    public function test_registration_screen_shows_active_groups(): void
    {
        [$curso, $grupo1, $grupo2] = $this->setupAcademicStructure();

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('1º DAW Mañana');
        $response->assertSee('2º DAW Mañana');
    }

    public function test_student_registers_with_selected_groups_in_waiting_state(): void
    {
        [$curso, $grupo1, $grupo2] = $this->setupAcademicStructure();

        $response = $this->post('/register', [
            'name' => 'Carlos Alumno',
            'email' => 'carlos@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'alumno',
            'grupos' => [$grupo1->id, $grupo2->id],
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        // No inicia sesión automáticamente
        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        // Usuario creado inactivo y en espera
        $user = User::where('email', 'carlos@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->isActive());
        $this->assertTrue($user->isPendingApproval());
        $this->assertEquals(User::STATUS_PENDING, $user->registration_status);

        // Alumno creado y asociado a los grupos seleccionados con curso académico
        $alumno = $user->alumno;
        $this->assertNotNull($alumno);
        $this->assertCount(2, $alumno->grupos);
        $this->assertTrue($alumno->grupos->contains($grupo1));
        $this->assertTrue($alumno->grupos->contains($grupo2));
        $this->assertEquals($curso->id, $alumno->grupos->first()->pivot->curso_academico_id);
    }

    public function test_pending_student_cannot_log_in(): void
    {
        [$curso, $grupo1] = $this->setupAcademicStructure();

        $this->post('/register', [
            'name' => 'Elena Estudiante',
            'email' => 'elena@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'alumno',
            'grupos' => [$grupo1->id],
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        // Intento de login mientras está en espera
        $response = $this->post('/login', [
            'email' => 'elena@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_view_pending_student_with_requested_groups(): void
    {
        [$curso, $grupo1] = $this->setupAcademicStructure();

        // Registrar alumno
        $this->post('/register', [
            'name' => 'Marta García',
            'email' => 'marta@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'alumno',
            'grupos' => [$grupo1->id],
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['registration_status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('Marta García');
        $response->assertSee('marta@example.com');
        $response->assertSee('1º DAW Mañana');
        $response->assertSee('En espera');
        $response->assertSee('Aceptar');
    }

    public function test_admin_can_approve_registration_and_student_can_then_login(): void
    {
        [$curso, $grupo1] = $this->setupAcademicStructure();

        $this->post('/register', [
            'name' => 'David Torres',
            'email' => 'david@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'alumno',
            'grupos' => [$grupo1->id],
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $studentUser = User::where('email', 'david@example.com')->first();
        $this->assertTrue($studentUser->isPendingApproval());

        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
        ]);

        // Admin aprueba la solicitud
        $response = $this->actingAs($admin)->post(route('admin.users.approve', $studentUser));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $studentUser->refresh();
        $this->assertTrue($studentUser->isActive());
        $this->assertTrue($studentUser->isRegistrationApproved());
        $this->assertNotNull($studentUser->registration_approved_at);

        // Desconectar admin y probar login del alumno ya habilitado
        $this->post('/logout');

        $loginResponse = $this->post('/login', [
            'email' => 'david@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($studentUser);
        $loginResponse->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_reject_registration(): void
    {
        [$curso, $grupo1] = $this->setupAcademicStructure();

        $this->post('/register', [
            'name' => 'Falso Alumno',
            'email' => 'falso@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'alumno',
            'grupos' => [$grupo1->id],
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $studentUser = User::where('email', 'falso@example.com')->first();

        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.reject', $studentUser));

        $response->assertRedirect();
        $studentUser->refresh();
        $this->assertFalse($studentUser->isActive());
        $this->assertTrue($studentUser->isRegistrationRejected());
        $this->assertNotNull($studentUser->registration_rejected_at);
    }

    public function test_non_admin_cannot_approve_or_reject_registration(): void
    {
        $studentUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => false,
            'registration_status' => User::STATUS_PENDING,
        ]);

        $profesor = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
        ]);

        $responseApprove = $this->actingAs($profesor)->post(route('admin.users.approve', $studentUser));
        $responseApprove->assertStatus(403);

        $responseReject = $this->actingAs($profesor)->post(route('admin.users.reject', $studentUser));
        $responseReject->assertStatus(403);
    }
}
