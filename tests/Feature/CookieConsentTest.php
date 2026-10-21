<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    public function test_guest_without_cookie_sees_cookie_banner(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('Utilizamos cookies');
    }

    public function test_guest_can_accept_cookies_and_banner_is_hidden(): void
    {
        $response = $this->post(route('cookies.accept'));
        $response->assertPlainCookie('cookie_consent', 'true');
        $response->assertSessionHas('cookie_consent', true);

        // Visiting again with the cookie does not render the banner
        $followUp = $this->withCookie('cookie_consent', 'true')->get('/login');
        $followUp->assertOk();
        $followUp->assertDontSee('Utilizamos cookies');
    }

    public function test_guest_can_reject_non_essential_cookies_and_banner_is_hidden(): void
    {
        $response = $this->post(route('cookies.reject'));
        $response->assertPlainCookie('cookie_consent', 'true');
        $response->assertSessionHas('cookie_consent', true);

        $followUp = $this->withCookie('cookie_consent', 'true')->get('/login');
        $followUp->assertOk();
        $followUp->assertDontSee('Utilizamos cookies');
    }

    public function test_authenticated_user_with_consent_does_not_see_cookie_banner(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
            'consent_cookies_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('Utilizamos cookies');
    }

    public function test_login_synchronizes_cookie_consent_to_user_record(): void
    {
        $user = User::factory()->create([
            'email' => 'login.cookie@ieshlanz.es',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_cookies_at' => null, // Aún no registrado
        ]);

        $response = $this->withCookie('cookie_consent', 'true')->post('/login', [
            'email' => 'login.cookie@ieshlanz.es',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user->refresh();
        $this->assertNotNull($user->consent_cookies_at);

        // Visitar el dashboard no muestra el banner
        $dashResponse = $this->actingAs($user)->get(route('dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertDontSee('Utilizamos cookies');
    }
}
