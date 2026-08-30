<?php

namespace Tests\Feature;

use App\Models\Ciclo;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Notificacion;
use App\Models\Practica;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@ieshlanz.es',
            'password' => bcrypt('password'),
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
        ]);
    }

    /** @test */
    public function admin_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHasAll([
            'totalAlumnos', 'totalAlumnosActivos', 'totalAlumnosInactivos',
            'totalProfesores', 'totalProfesoresActivos', 'totalProfesoresInactivos',
            'totalEmpresas', 'totalEmpresasInactivas',
            'practicasEnCurso', 'practicasPendientes', 'practicasFinalizadas',
            'conveniosFirmados', 'conveniosNoFirmados',
            'convenios', 'proyectosDestacados',
            'destacadosPorCiclo', 'actividadReciente', 'ciclos',
        ]);
    }

    /** @test */
    public function non_admin_cannot_view_dashboard(): void
    {
        $alumno = User::factory()->create([
            'name' => 'Alumno',
            'email' => 'alumno@test.es',
            'password' => bcrypt('password'),
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
        ]);

        $response = $this->actingAs($alumno)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    /** @test */
    public function dashboard_shows_correct_user_counts(): void
    {
        User::factory()->count(3)->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
        ]);

        User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => false,
            'consent_rgpd' => true,
        ]);

        User::factory()->count(2)->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertViewHas('totalAlumnos', 4);
        $response->assertViewHas('totalAlumnosActivos', 3);
        $response->assertViewHas('totalAlumnosInactivos', 1);
        $response->assertViewHas('totalProfesores', 2);
        $response->assertViewHas('totalProfesoresActivos', 2);
        $response->assertViewHas('totalProfesoresInactivos', 0);
    }

    /** @test */
    public function dashboard_shows_practices_counts(): void
    {
        Practica::factory()->enCurso()->create();
        Practica::factory()->create([
            'fecha_inicio' => now()->addDays(30),
            'fecha_fin' => now()->addDays(60)
        ]);
        Practica::factory()->finalizada()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('practicasEnCurso', 1);
        $response->assertViewHas('practicasPendientes', 1);
        $response->assertViewHas('practicasFinalizadas', 1);
    }

    /** @test */
    public function dashboard_shows_convenios_counts(): void
    {
        $empresa = Empresa::factory()->create(['is_active' => true]);
        $ciclo = Ciclo::factory()->create();

        Convenio::factory()->create(['estado' => 'firmado']);
        Convenio::factory()->create(['estado' => 'no_firmado']);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertViewHas('conveniosFirmados', 1);
        $response->assertViewHas('conveniosNoFirmados', 1);
    }

    /** @test */
    public function dashboard_filters_convenios_by_familia(): void
    {
        $familia1 = Familia::factory()->create(['nombre' => 'Informática']);
        $familia2 = Familia::factory()->create(['nombre' => 'Administración']);
        $ciclo1 = Ciclo::factory()->create(['familia_id' => $familia1->id]);
        $ciclo2 = Ciclo::factory()->create(['familia_id' => $familia2->id]);
        $empresa = Empresa::factory()->create(['is_active' => true]);

        Convenio::factory()->create(['ciclo_id' => $ciclo1->id, 'empresa_id' => $empresa->id]);
        Convenio::factory()->create(['ciclo_id' => $ciclo2->id, 'empresa_id' => $empresa->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['convenio_familia' => $familia1->id]));

        $response->assertOk();
        // Verificar que solo se muestra 1 convenio (el de la familia filtrada)
        $view = $response->assertViewIs('admin.dashboard')->viewData('convenios');
        $this->assertEquals(1, $view->total());
    }

    /** @test */
    public function dashboard_filters_convenios_by_curso(): void
    {
        $empresa = Empresa::factory()->create(['is_active' => true]);
        $ciclo = Ciclo::factory()->create();

        Convenio::factory()->create([
            'ciclo_id' => $ciclo->id,
            'empresa_id' => $empresa->id,
            'curso_academico' => '2025/2026',
        ]);
        Convenio::factory()->create([
            'ciclo_id' => $ciclo->id,
            'empresa_id' => $empresa->id,
            'curso_academico' => '2024/2025',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['convenio_curso' => '2025/2026']));

        $response->assertOk();
        $response->assertSee('2025/2026');
    }

    /** @test */
    public function dashboard_shows_projects_by_cycle(): void
    {
        $ciclo1 = Ciclo::factory()->create(['nombre' => 'DAM']);
        $ciclo2 = Ciclo::factory()->create(['nombre' => 'SMR']);
        $alumno = User::factory()->alumno()->create(['is_active' => true]);
        $alumnoData = \App\Models\Alumno::factory()->create(['user_id' => $alumno->id]);

        Proyecto::factory()->create([
            'alumno_id' => $alumnoData->id,
            'ciclo_id' => $ciclo1->id,
            'es_destacado' => true,
            'calificacion' => 9.5,
        ]);
        Proyecto::factory()->create([
            'alumno_id' => $alumnoData->id,
            'ciclo_id' => $ciclo2->id,
            'es_destacado' => true,
            'calificacion' => 8.0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('DAM');
        $response->assertSee('SMR');
    }

    /** @test */
    public function dashboard_shows_cycles_with_student_counts(): void
    {
        $ciclo1 = Ciclo::factory()->create(['nombre' => 'DAM']);
        $ciclo2 = Ciclo::factory()->create(['nombre' => 'SMR']);

        $alumno1 = User::factory()->alumno()->create(['is_active' => true]);
        $alumno1Data = \App\Models\Alumno::factory()->create(['user_id' => $alumno1->id]);
        $alumno1Data->ciclosMatriculados()->attach($ciclo1->id, ['curso_academico' => '2025/2026']);

        $alumno2 = User::factory()->alumno()->create(['is_active' => true]);
        $alumno2Data = \App\Models\Alumno::factory()->create(['user_id' => $alumno2->id]);
        $alumno2Data->ciclosMatriculados()->attach($ciclo1->id, ['curso_academico' => '2025/2026']);

        $alumno3 = User::factory()->alumno()->create(['is_active' => true]);
        $alumno3Data = \App\Models\Alumno::factory()->create(['user_id' => $alumno3->id]);
        $alumno3Data->ciclosMatriculados()->attach($ciclo2->id, ['curso_academico' => '2025/2026']);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('ciclos');
    }

    /** @test */
    public function dashboard_shows_recent_activity(): void
    {
        $alumno = User::factory()->alumno()->create(['is_active' => true]);
        $alumnoData = \App\Models\Alumno::factory()->create(['user_id' => $alumno->id]);

        Notificacion::factory()->create([
            'usuario_id' => $alumno->id,
            'tipo' => 'empresa_asignada',
            'expira_en' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('actividadReciente');
    }
}