<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $profesor = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($profesor)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $target = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$target->id}/deactivate");
        $response->assertRedirect();

        $target->refresh();
        $this->assertFalse($target->is_active);
    }

    public function test_admin_can_reactivate_user(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $target = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => false,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$target->id}/reactivate");
        $response->assertRedirect();

        $target->refresh();
        $this->assertTrue($target->is_active);
    }

    public function test_deactivated_user_cannot_access_anything(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => false,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        // Intentar acceder al dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/login');

        // Intentar acceder a perfil
        $response = $this->actingAs($user)->get('/profile');
        $response->assertRedirect('/login');

        // Verificar que está deslogueado
        $this->assertGuest();
    }

    public function test_user_can_request_data_deletion(): void
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/profile/deletion-request', [
            'password' => 'password',
        ]);

        $user->refresh();
        $this->assertNotNull($user->data_deletion_requested_at);
        $response->assertRedirect('/login');
    }

    public function test_user_cannot_deactivate_themselves_via_admin(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/deactivate");
        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_themselves_via_admin(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");
        $response->assertStatus(403);
    }
}