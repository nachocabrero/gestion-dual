<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RgpdTest extends TestCase
{
    use RefreshDatabase;

    public function test_rgpd_consent_page_is_accessible(): void
    {
        $response = $this->get('/rgpd/consent');
        $response->assertStatus(200);
    }

    public function test_privacy_page_is_accessible(): void
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200);
    }

    public function test_user_must_consent_rgpd_before_accessing_dashboard(): void
    {
        $user = User::factory()->create([
            'consent_rgpd' => false,
            'consent_rgpd_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/rgpd/consent');
    }

    public function test_user_can_consent_rgpd(): void
    {
        $user = User::factory()->create([
            'consent_rgpd' => false,
            'consent_rgpd_at' => null,
        ]);

        $response = $this->actingAs($user)->post('/rgpd/accept');

        $user->refresh();
        $this->assertTrue($user->hasConsentedRgpd());
        $this->assertNotNull($user->consent_rgpd_at);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_registered_user_has_rgpd_consent(): void
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

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasConsentedRgpd());
    }
}