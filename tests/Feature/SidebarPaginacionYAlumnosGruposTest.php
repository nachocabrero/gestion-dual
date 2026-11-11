<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarPaginacionYAlumnosGruposTest extends TestCase
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

    private function createAcademicStructure(): array
    {
        $curso = CursoAcademico::create([
            'nombre' => '2026-2027',
            'is_active' => true,
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-30',
        ]);

        $familia = Familia::create([
            'codigo' => 'IFC_TEST',
            'nombre' => 'Informática IFC',
            'is_active' => true,
        ]);

        $ciclo = Ciclo::create([
            'familia_id' => $familia->id,
            'codigo' => 'DAW_AUTO',
            'nombre' => 'Desarrollo de Aplicaciones Web Auto',
            'grado' => 'superior',
            'is_active' => true,
        ]);

        $linea = Linea::create([
            'ciclo_id' => $ciclo->id,
            'turno' => 'manana',
            'nombre' => 'DAW Mañana Auto',
            'is_active' => true,
        ]);

        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAW Mañana Auto',
            'is_active' => true,
        ]);

        return compact('curso', 'familia', 'ciclo', 'linea', 'grupo');
    }

    /**
     * Verificar que el layout incluye la lógica de cierre automático del menú lateral para <1050px.
     */
    public function test_sidebar_includes_auto_close_under_1050px_logic(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('window.innerWidth < 1050', $content);
        $this->assertStringContainsString('this.sidebarDesktopOpen = false', $content);
        $this->assertStringContainsString('resize', $content);
    }

    /**
     * Verificar que los textos de paginación están en español ("Mostrando ... a ... de ... resultados").
     */
    public function test_pagination_displays_spanish_translations(): void
    {
        $admin = $this->createAdmin();
        $ctx = $this->createAcademicStructure();

        // Crear 18 alumnos para forzar más de 1 página (paginación suele ser 15)
        for ($i = 1; $i <= 18; $i++) {
            $u = User::factory()->create([
                'name' => sprintf('Alumno Paginado %02d', $i),
                'roles' => [User::ROLE_ALUMNO],
            ]);
            $a = Alumno::create(['user_id' => $u->id]);
            $a->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);
        }

        $response = $this->actingAs($admin)->get(route('alumnos.index'));
        $response->assertOk();

        // Verificar que la paginación muestra los textos en español
        $response->assertSeeText('Mostrando');
        $response->assertSeeText('a');
        $response->assertSeeText('de');
        $response->assertSeeText('resultados');
        $response->assertDontSeeText('Showing 1 to');
    }

    /**
     * Verificar que en el listado de alumnos la columna se titula "Grupos" y no muestra badges de ciclo.
     */
    public function test_alumnos_index_shows_grupos_column_and_omits_ciclo_badges(): void
    {
        $admin = $this->createAdmin();
        $ctx = $this->createAcademicStructure();

        $u = User::factory()->create([
            'name' => 'Carlos Estudiante Sin Ciclo Badge',
            'roles' => [User::ROLE_ALUMNO],
        ]);
        $a = Alumno::create(['user_id' => $u->id]);
        $a->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $response = $this->actingAs($admin)->get(route('alumnos.index'));
        $response->assertOk();

        // Cabecera "Grupos"
        $response->assertSee('<th>Grupos</th>', false);
        $response->assertDontSee('<th>Ciclos y Grupos</th>', false);

        // Se muestra el grupo en la fila
        $response->assertSee($ctx['grupo']->nombre);

        // No debe haber badge de ciclo con stitch-badge-info en la columna
        $content = $response->getContent();
        $this->assertStringNotContainsString('<span class="stitch-badge-info text-xs">' . $ctx['ciclo']->codigo . '</span>', $content);
        $this->assertStringNotContainsString('<span class="stitch-badge-info text-[10px]">' . $ctx['ciclo']->codigo . '</span>', $content);
    }
}

