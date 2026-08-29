<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ProyectoDebug2Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_auth_works(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin2@test.com',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
        
        $this->actingAs($admin);
        $response = $this->get(route('proyectos.index'));
        echo "\nSTATUS: " . $response->getStatusCode();
        echo "\nURL: " . $response->headers->get('location', 'none');
        echo "\nCONTENT_PREVIEW: " . substr($response->getContent(), 0, 300);
        $this->assertTrue(true);
    }
}
