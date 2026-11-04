<?php

namespace Tests\Feature;

use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Ciclo;
use App\Models\Linea;
use App\Models\Grupo;
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
        $curso = CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-25',
            'is_active' => true,
        ]);
        $familia = Familia::create([
            'nombre' => 'Informática y Comunicaciones ' . uniqid(),
            'codigo' => 'IFC' . substr(uniqid(), 0, 4),
            'is_active' => true,
        ]);
        $ciclo = Ciclo::create([
            'familia_id' => $familia->id,
            'nombre' => 'DAW ' . uniqid(),
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
        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º DAW Mañana',
            'is_active' => true,
        ]);

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

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasConsentedRgpd());
    }
}