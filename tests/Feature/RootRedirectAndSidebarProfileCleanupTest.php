<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Empresa;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RootRedirectAndSidebarProfileCleanupTest extends TestCase
{
    use RefreshDatabase;

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

    private function createProfesor(): User
    {
        $user = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        Profesor::factory()->create([
            'user_id' => $user->id,
        ]);

        return $user;
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

        Alumno::factory()->create([
            'user_id' => $user->id,
        ]);

        return $user;
    }

    private function createEmpresa(): User
    {
        $empresa = Empresa::factory()->create();

        return User::factory()->create([
            'roles' => [User::ROLE_EMPRESA],
            'empresa_id' => $empresa->id,
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    /**
     * Visitante sin autenticar accede a la raíz y ve el portfolio público (HTTP 200).
     */
    public function test_guest_visiting_root_url_sees_portfolio(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('proyectos.portfolio');
    }

    /**
     * Usuario admin autenticado que accede a la raíz es redirigido directamente al dashboard.
     */
    public function test_authenticated_admin_visiting_root_url_redirects_to_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Usuario profesor autenticado que accede a la raíz es redirigido directamente al dashboard.
     */
    public function test_authenticated_profesor_visiting_root_url_redirects_to_dashboard(): void
    {
        $profesor = $this->createProfesor();

        $response = $this->actingAs($profesor)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Usuario alumno autenticado que accede a la raíz es redirigido directamente al dashboard.
     */
    public function test_authenticated_alumno_visiting_root_url_redirects_to_dashboard(): void
    {
        $alumno = $this->createAlumno();

        $response = $this->actingAs($alumno)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Usuario empresa autenticado que accede a la raíz es redirigido directamente al dashboard.
     */
    public function test_authenticated_empresa_visiting_root_url_redirects_to_dashboard(): void
    {
        $empresa = $this->createEmpresa();

        $response = $this->actingAs($empresa)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * El menú lateral (sidebar) no incluye los botones de perfil ni de salir al final.
     */
    public function test_sidebar_does_not_contain_profile_link_or_logout_button(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        // Extraer únicamente el bloque <aside id="sidebar">...</aside>
        preg_match('/<aside id="sidebar"[^>]*>(.*?)<\/aside>/s', $content, $matches);
        $this->assertNotEmpty($matches, 'El elemento <aside id="sidebar"> debe existir en la vista.');
        $sidebarContent = $matches[1];

        // Verificar que no contiene el enlace al perfil dentro del sidebar
        $this->assertStringNotContainsString(route('profile.edit'), $sidebarContent);
        // Verificar que no contiene el formulario o botón de logout dentro del sidebar
        $this->assertStringNotContainsString(route('logout'), $sidebarContent);
        $this->assertStringNotContainsString('Salir', $sidebarContent);
    }

    /**
     * La barra superior (header / topbar) conserva el dropdown con perfil y cierre de sesión.
     */
    public function test_top_navbar_contains_profile_dropdown_with_profile_link_and_logout(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        // Extraer la cabecera <header ...>...</header>
        preg_match('/<header[^>]*>(.*?)<\/header>/s', $content, $matches);
        $this->assertNotEmpty($matches, 'El elemento <header> debe existir en la vista.');
        $headerContent = $matches[1];

        // Verificar que la cabecera contiene la información del usuario
        $this->assertStringContainsString($admin->name, $headerContent);
        $this->assertStringContainsString($admin->email, $headerContent);

        // Verificar que contiene el enlace de edición de perfil
        $this->assertStringContainsString(route('profile.edit'), $headerContent);
        $this->assertStringContainsString('Perfil y Configuración', $headerContent);

        // Verificar que contiene el formulario de logout y botón de cerrar sesión
        $this->assertStringContainsString(route('logout'), $headerContent);
        $this->assertStringContainsString('Cerrar Sesión', $headerContent);
    }
}
