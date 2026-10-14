<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_students_register_in_pending_state(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'alumno',
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