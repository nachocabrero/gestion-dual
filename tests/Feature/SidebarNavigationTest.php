<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    private function createAdmin(): User
    {
        return User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createAlumno(): User
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        \App\Models\Alumno::factory()->create([
            'user_id' => $user->id,
        ]);

        return $user;
    }

    /**
     * El layout renderiza el menú lateral fijo con scroll interno (sin pie de perfil/logout redundante).
     */
    public function test_sidebar_fijo_con_scroll_interno_en_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        // Clases de menú fijo en pantalla completa y scroll independiente
        $response->assertSee('id="sidebar"', false);
        $response->assertSee('h-screen max-h-screen', false);
        $response->assertSee('custom-scrollbar min-h-0', false);
        $response->assertSee('toggleSidebar()', false);
        $response->assertSee('toggleSeccion(', false);

        // El menú lateral ya no contiene el bloque inferior de perfil/salir
        $content = $response->getContent();
        preg_match('/<aside id="sidebar"[^>]*>(.*?)<\/aside>/s', $content, $matches);
        $sidebarHtml = $matches[1] ?? '';
        $this->assertNotEmpty($sidebarHtml);
        $this->assertStringNotContainsString('route(\'profile.edit\')', $sidebarHtml);
        $this->assertStringNotContainsString(route('profile.edit'), $sidebarHtml);
        $this->assertStringNotContainsString('Salir', $sidebarHtml);
    }

    /**
     * En admin se renderizan los 5 grupos de navegación plegables.
     */
    public function test_sidebar_renderiza_secciones_plegables_para_admin(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('toggleSeccion(\'principal\')', false);
        $response->assertSee('toggleSeccion(\'academica\')', false);
        $response->assertSee('toggleSeccion(\'practicas\')', false);
        $response->assertSee('toggleSeccion(\'proyectos\')', false);
        $response->assertSee('toggleSeccion(\'admin\')', false);

        $response->assertSee('Menú Principal');
        $response->assertSee('Gestión Académica');
        $response->assertSee('Prácticas y FCT');
        $response->assertSee('Proyectos e Innovación');
        $response->assertSee('Administración');
    }

    /**
     * En alumno no se muestra la sección de administración pero sí sus prácticas y proyectos.
     */
    public function test_sidebar_alumno_muestra_secciones_propias_sin_administracion(): void
    {
        $alumno = $this->createAlumno();

        $response = $this->actingAs($alumno)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Menú Principal');
        $response->assertSee('Prácticas y FCT');
        $response->assertSee('Mis Prácticas y Horas');
        $response->assertSee('Proyectos e Innovación');
        $response->assertDontSee('toggleSeccion(\'admin\')', false);
    }

    /**
     * El botón de alternancia en header y botón de colapso en sidebar están presentes.
     */
    public function test_botones_ocultar_y_mostrar_menu_presentes(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('toggleSidebar()', false);
        $response->assertSee('sidebarDesktopOpen = false', false);
        $response->assertSee('Ocultar menú lateral', false);
    }
}

