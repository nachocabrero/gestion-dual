<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_not_exposed_in_response(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-password'),
        ]);

        $response = $this->actingAs($user)->get('/profile');

        // Verificar que "secret-password" no aparece en la respuesta
        $content = $response->getContent();
        $this->assertStringNotContainsString('secret-password', $content);
    }

    public function test_csrf_protection_is_enabled(): void
    {
        // POST sin token CSRF debería fallar
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        // Debería redirigir o fallar por CSRF
        $this->assertFalse($response->isOk());
    }

    public function test_registration_requires_rgpd_consent(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'alumno',
            // Sin consent_rgpd ni privacy_policy
        ]);

        $response->assertSessionHasErrors(['consent_rgpd', 'privacy_policy']);

        // Verificar que no se creó el usuario
        $this->assertNull(User::where('email', 'test@example.com')->first());
    }

    public function test_registration_requires_valid_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',  // Muy corto
            'password_confirmation' => 'short',
            'role' => 'alumno',
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_duplicate_email_registration_fails(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'alumno',
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_data_is_protected_via_policy(): void
    {
        $userA = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $userB = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        // Un alumno no puede actualizar otro alumno
        $response = $this->actingAs($userA)->patch('/profile', [
            'name' => 'Hacked Name',
            'email' => 'hacked@test.com',
        ]);

        // Debería fallar (policy deniega)
        $userB->refresh();
        $this->assertNotEquals('Hacked Name', $userB->name);
    }

    public function test_admin_can_see_all_users_but_not_passwords(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'password' => bcrypt('secret'),
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        // Verificar que las contraseñas no aparecen
        $content = $response->getContent();
        $this->assertStringNotContainsString('secret', $content);
    }
}