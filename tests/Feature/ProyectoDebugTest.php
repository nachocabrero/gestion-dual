<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProyectoDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_admin_view(): void
    {
        $admin = $this->createAdmin();
        $response = $this->get(route('proyectos.index'));
        echo "\nSTATUS: " . $response->getStatusCode();
        echo "\nCONTENT: " . substr($response->getContent(), 0, 500);
        $this->assertTrue(true);
    }

    protected function createAdmin()
    {
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);
        $user->assignRole('admin');
        return $user;
    }
}
