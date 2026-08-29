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

    public function test_new_users_can_register(): void
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

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        // Verificar que el usuario fue creado con RGPD consent
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasConsentedRgpd());
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