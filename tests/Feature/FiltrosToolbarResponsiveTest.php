<?php

namespace Tests\Feature;

use App\Models\CursoAcademico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiltrosToolbarResponsiveTest extends TestCase
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

    private function createCurso(): CursoAcademico
    {
        return CursoAcademico::create([
            'nombre' => '2026-2027',
            'is_active' => true,
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-30',
        ]);
    }

    /**
     * Verificar que la barra de filtros de alumnos index tiene la estructura en 2 filas
     * para ventanas pequeñas y medianas (flex-col 2xl:flex-row).
     */
    public function test_alumnos_index_toolbar_uses_two_row_responsive_layout(): void
    {
        $admin = $this->createAdmin();
        $this->createCurso();

        $response = $this->actingAs($admin)->get(route('alumnos.index'));
        $response->assertOk();

        // Estructura en 2 filas cuando la resolución es inferior a 2xl
        $response->assertSee('flex flex-col 2xl:flex-row items-stretch 2xl:items-center gap-3', false);
        // Fila 1: Buscador a ancho completo en pantallas reducidas
        $response->assertSee('relative w-full 2xl:flex-1 min-w-[240px]', false);
        // Fila 2: Filtros y botones de acción
        $response->assertSee('w-full 2xl:w-auto 2xl:shrink-0', false);
        // Botones protegidos contra desbordamiento de texto
        $response->assertSee('stitch-btn-primary text-xs py-2 px-4 whitespace-nowrap', false);
        $response->assertSee('Ampliar opciones de filtro', false);
    }

    /**
     * Verificar que las barras de búsqueda y filtros de otros listados (empresas, profesores, ofertas, practicas)
     * también cuentan con protección contra desbordamientos.
     */
    public function test_other_index_toolbars_prevent_overflow(): void
    {
        $admin = $this->createAdmin();
        $this->createCurso();

        // Empresas
        $resEmpresas = $this->actingAs($admin)->get(route('empresas.index'));
        $resEmpresas->assertOk();
        $resEmpresas->assertSee('flex flex-col 2xl:flex-row items-stretch 2xl:items-center gap-3', false);

        // Profesores
        $resProfesores = $this->actingAs($admin)->get(route('profesores.index'));
        $resProfesores->assertOk();
        $resProfesores->assertSee('flex flex-col xl:flex-row items-stretch xl:items-center gap-3', false);

        // Ofertas
        $resOfertas = $this->actingAs($admin)->get(route('ofertas.index'));
        $resOfertas->assertOk();
        $resOfertas->assertSee('flex flex-col xl:flex-row items-stretch xl:items-center gap-3', false);

        // Prácticas
        $resPracticas = $this->actingAs($admin)->get(route('practicas.index'));
        $resPracticas->assertOk();
        $resPracticas->assertSee('flex flex-col 2xl:flex-row items-stretch 2xl:items-center gap-3', false);
    }
}
