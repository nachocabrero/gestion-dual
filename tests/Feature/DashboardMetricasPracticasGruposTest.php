<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\OfertaPractica;
use App\Models\Practica;
use App\Models\Profesor;
use App\Models\SolicitudPractica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricasPracticasGruposTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createCoordinadorDual(): User
    {
        return User::factory()->create([
            'name' => 'Coordinador Dual User',
            'email' => 'coord_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createProfesor(): User
    {
        $user = User::factory()->create([
            'name' => 'Profesor Docente',
            'email' => 'profesor_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        Profesor::factory()->create([
            'user_id' => $user->id,
            'especialidad' => 'Informática',
        ]);

        return $user;
    }

    private function createAlumno(string $name = 'Alumno Test'): Alumno
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => 'alumno_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        return Alumno::factory()->create([
            'user_id' => $user->id,
        ]);
    }

    public function test_profesor_sees_metrics_for_assigned_groups_only(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']
        );
        $familia = Familia::firstOrCreate(['codigo' => 'INF'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['nombre' => 'DAW', 'grado' => 'superior', 'familia_id' => $familia->id, 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Mañana', 'turno' => 'manana', 'is_active' => true]);

        $profesor = $this->createProfesor();

        // Grupo 1 del profesor (impartido)
        $grupoPropio = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º DAW Propio',
            'is_active' => true,
        ]);
        $profesor->profesor->gruposImpartidos()->attach($grupoPropio->id);

        // Grupo 2 ajeno
        $grupoAjeno = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAW Ajeno',
            'is_active' => true,
        ]);

        $alumno1 = $this->createAlumno('Alumno Uno');
        $alumno2 = $this->createAlumno('Alumno Dos');
        $grupoPropio->alumnos()->attach([$alumno1->id, $alumno2->id], ['curso_academico_id' => $curso->id]);

        $empresa = Empresa::factory()->create(['is_active' => true]);
        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'curso_academico_id' => $curso->id,
        ]);

        // Alumno 1 tiene oferta asignada
        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno1->id,
            'estado' => 'aceptado',
        ]);

        $response = $this->actingAs($profesor)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Seguimiento de Prácticas por Grupo');
        $response->assertSee('1º DAW Propio');
        $response->assertDontSee('2º DAW Ajeno');

        // Métricas de alumnos
        $response->assertSee('2'); // Total alumnos
        $response->assertSee('1'); // Con oferta
    }

    public function test_coordinador_dual_sees_metrics_for_all_groups(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']
        );
        $familia = Familia::firstOrCreate(['codigo' => 'INF'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAM'], ['nombre' => 'DAM', 'grado' => 'superior', 'familia_id' => $familia->id, 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Tarde', 'turno' => 'tarde', 'is_active' => true]);

        $grupoA = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º DAM Grupo Alfa',
            'is_active' => true,
        ]);
        $grupoB = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAM Grupo Beta',
            'is_active' => true,
        ]);

        $coord = $this->createCoordinadorDual();

        $response = $this->actingAs($coord)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Seguimiento de Prácticas por Grupo');
        $response->assertSee('Todos los grupos activos del centro');
        $response->assertSee('1º DAM Grupo Alfa');
        $response->assertSee('2º DAM Grupo Beta');
    }

    public function test_admin_sees_metrics_for_all_groups(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']
        );
        $familia = Familia::firstOrCreate(['codigo' => 'INF'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'ASIR'], ['nombre' => 'ASIR', 'grado' => 'superior', 'familia_id' => $familia->id, 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Mañana', 'turno' => 'manana', 'is_active' => true]);

        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º ASIR Admin Check',
            'is_active' => true,
        ]);

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Seguimiento de Prácticas por Grupo');
        $response->assertSee('1º ASIR Admin Check');
    }

    public function test_metric_counts_accuracy(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']
        );
        $familia = Familia::firstOrCreate(['codigo' => 'INF'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['nombre' => 'DAW', 'grado' => 'superior', 'familia_id' => $familia->id, 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Mañana', 'turno' => 'manana', 'is_active' => true]);

        $profesor = $this->createProfesor();

        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'tutor_id' => $profesor->id,
            'numero' => 1,
            'nombre' => '2º DAW Métricas Exactas',
            'is_active' => true,
        ]);

        $alumno1 = $this->createAlumno('Alumno Uno');
        $alumno2 = $this->createAlumno('Alumno Dos');
        $alumno3 = $this->createAlumno('Alumno Tres');

        $grupo->alumnos()->attach([$alumno1->id, $alumno2->id, $alumno3->id], ['curso_academico_id' => $curso->id]);

        $empresa = Empresa::factory()->create(['is_active' => true]);
        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'curso_academico_id' => $curso->id,
        ]);

        // Alumno 1: con oferta y con práctica
        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno1->id,
            'estado' => 'aceptado',
        ]);
        Practica::create([
            'alumno_id' => $alumno1->id,
            'oferta_id' => $oferta->id,
            'empresa_id' => $empresa->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => '2027-03-01',
            'fecha_fin' => '2027-06-15',
            'horas_acumuladas' => 400,
        ]);

        // Alumno 2: con oferta, sin práctica
        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno2->id,
            'estado' => 'aceptado',
        ]);

        // Alumno 3: sin oferta, sin práctica

        $response = $this->actingAs($profesor)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('2º DAW Métricas Exactas');
        $response->assertSee('Tutoría');

        // Total alumnos: 3
        // Con oferta: 2 / 3
        // Con prácticas: 1 / 3
        $response->assertSee('3');
        $response->assertSee('2');
        $response->assertSee('1');
    }

    public function test_alumno_does_not_see_grupos_metrics_block(): void
    {
        $alumno = $this->createAlumno('Alumno Simple');

        $response = $this->actingAs($alumno->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Seguimiento de Prácticas por Grupo');
    }

    public function test_empresa_does_not_see_grupos_metrics_block(): void
    {
        $empresaUser = User::factory()->create([
            'name' => 'Empresa User',
            'email' => 'empresa_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_EMPRESA],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($empresaUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Seguimiento de Prácticas por Grupo');
    }

    public function test_profesor_without_groups_sees_empty_state(): void
    {
        $profesor = $this->createProfesor();

        $response = $this->actingAs($profesor)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Seguimiento de Prácticas por Grupo');
        $response->assertSee('No hay grupos asignados para mostrar en este curso.');
    }
}
