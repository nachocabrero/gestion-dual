<?php

namespace Tests\Feature\Auth;

use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Ciclo;
use App\Models\Linea;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function createGrupo(?CursoAcademico $curso = null, bool $isActive = true): Grupo
    {
        if (!$curso) {
            $curso = CursoAcademico::create([
                'nombre' => '2026/2027',
                'fecha_inicio' => '2026-09-15',
                'fecha_fin' => '2027-06-25',
                'is_active' => true,
            ]);
        }

        $familia = Familia::create([
            'nombre' => 'Informática y Comunicaciones ' . uniqid(),
            'codigo' => 'IFC' . substr(uniqid(), 0, 4),
            'is_active' => true,
        ]);

        $ciclo = Ciclo::create([
            'familia_id' => $familia->id,
            'nombre' => 'Desarrollo de Aplicaciones Web ' . uniqid(),
            'codigo' => 'DAW' . substr(uniqid(), 0, 4),
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

        return Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º DAW Mañana',
            'is_active' => $isActive,
        ]);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_students_register_in_pending_state(): void
    {
        $grupo = $this->createGrupo();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'alumno',
            'grupos' => [$grupo->id],
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        // Verificar que el usuario fue creado en estado de espera
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->isActive());
        $this->assertTrue($user->isPendingApproval());
        $this->assertTrue($user->hasConsentedRgpd());
        $this->assertTrue($user->alumno->grupos->contains($grupo));
    }

    public function test_student_registration_fails_without_group(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User Sin Grupo',
            'email' => 'singrupo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'alumno',
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $response->assertSessionHasErrors(['grupos']);
        $this->assertNull(User::where('email', 'singrupo@example.com')->first());
    }

    public function test_student_registration_fails_with_empty_groups_array(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User Array Vacio',
            'email' => 'arrayvacio@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'alumno',
            'grupos' => [],
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $response->assertSessionHasErrors(['grupos']);
        $this->assertNull(User::where('email', 'arrayvacio@example.com')->first());
    }

    public function test_student_registration_fails_with_group_from_different_inactive_academic_year(): void
    {
        $cursoActivo = CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-25',
            'is_active' => true,
        ]);

        $cursoPasado = CursoAcademico::create([
            'nombre' => '2025/2026',
            'fecha_inicio' => '2025-09-15',
            'fecha_fin' => '2026-06-25',
            'is_active' => false,
        ]);

        $grupoInactivo = $this->createGrupo($cursoPasado);

        $response = $this->post('/register', [
            'name' => 'Test User Grupo Invalido',
            'email' => 'grupoinvalido@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'alumno',
            'grupos' => [$grupoInactivo->id],
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $response->assertSessionHasErrors(['grupos.0']);
        $this->assertNull(User::where('email', 'grupoinvalido@example.com')->first());
    }

    public function test_non_students_cannot_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Empresa',
            'email' => 'empresa@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'empresa',
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertGuest();
        $this->assertNull(User::where('email', 'empresa@example.com')->first());

        // Verificar también que profesor no se puede registrar
        $responseProf = $this->post('/register', [
            'name' => 'Test Profesor',
            'email' => 'profesor@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'profesor',
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $responseProf->assertSessionHasErrors(['role']);
        $this->assertGuest();
        $this->assertNull(User::where('email', 'profesor@example.com')->first());
    }

    public function test_registration_fails_without_rgpd_consent(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'alumno',
            // Sin consent_rgpd ni privacy_policy
        ]);

        $response->assertSessionHasErrors(['consent_rgpd', 'privacy_policy']);
        $this->assertNull(User::where('email', 'test2@example.com')->first());
    }
}