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

class AlumnoListadoColumnasCursosPracticasTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicContext(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'INF_TEST_CURSOS'],
            ['nombre' => 'Informática Test Cursos', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAW_CURSOS'],
            ['familia_id' => $familia->id, 'nombre' => 'DAW Cursos', 'grado' => 'superior', 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAW Mañana Cursos', 'is_active' => true]
        );

        $grupo1 = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 1],
            ['nombre' => '1º DAW Mañana', 'is_active' => true]
        );

        $grupo2 = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 2],
            ['nombre' => '2º DAW Mañana', 'is_active' => true]
        );

        return compact('curso', 'familia', 'ciclo', 'linea', 'grupo1', 'grupo2');
    }

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

    public function test_alumnos_index_shows_headers_practicas_2025_2026_and_2026_2027(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('alumnos.index'));

        $response->assertOk();
        $response->assertSee('<th>Prácticas 2025-2026</th>', false);
        $response->assertSee('<th>Prácticas 2026-2027</th>', false);
        $response->assertDontSee('<th>Prácticas 1º</th>', false);
        $response->assertDontSee('<th>Prácticas 2º</th>', false);
    }

    public function test_first_year_only_student_has_empty_practicas_2025_2026_column(): void
    {
        $admin = $this->createAdmin();
        $ctx = $this->createAcademicContext();

        // Alumno sólo en 1º curso con datos que no deberían mostrarse en la columna 2025-2026
        $userPrimero = User::factory()->create(['name' => 'Ana Alumna Solo Primero', 'roles' => [User::ROLE_ALUMNO]]);
        $alumnoPrimero = Alumno::create([
            'user_id' => $userPrimero->id,
            'empresa_practicas_primero' => 'Empresa No Visible 1o',
            'horas_practicas_primero' => 100,
        ]);
        $alumnoPrimero->grupos()->attach($ctx['grupo1']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->assertTrue($alumnoPrimero->soloEnGruposPrimero());
        $this->assertFalse($alumnoPrimero->esSegundoCurso());

        $this->actingAs($admin);
        $response = $this->get(route('alumnos.index', ['search' => 'Ana Alumna Solo Primero']));

        $response->assertOk();
        $response->assertSee('Ana Alumna Solo Primero');
        $response->assertSee('1º DAW Mañana');
        // La columna de 2025-2026 debe estar vacía para el alumno de 1º (no muestra empresa ni "Sin empresa")
        $response->assertDontSee('Empresa No Visible 1o');
        $response->assertDontSee('100 horas');
        $response->assertDontSee('Sin empresa');
    }

    public function test_second_year_student_displays_practicas_2025_2026_column_data(): void
    {
        $admin = $this->createAdmin();
        $ctx = $this->createAcademicContext();

        // Alumno en 2º curso
        $userSegundo = User::factory()->create(['name' => 'Berto Alumno Segundo Curso', 'roles' => [User::ROLE_ALUMNO]]);
        $alumnoSegundo = Alumno::create([
            'user_id' => $userSegundo->id,
            'empresa_practicas_primero' => 'Empresa Pasada 2025 SL',
            'horas_practicas_primero' => 160,
        ]);
        $alumnoSegundo->grupos()->attach($ctx['grupo2']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->assertFalse($alumnoSegundo->soloEnGruposPrimero());
        $this->assertTrue($alumnoSegundo->esSegundoCurso());

        $this->actingAs($admin);
        $response = $this->get(route('alumnos.index', ['search' => 'Berto Alumno Segundo Curso']));

        $response->assertOk();
        $response->assertSee('Berto Alumno Segundo Curso');
        $response->assertSee('2º DAW Mañana');
        // Para alumnos en 2º, la columna de 2025-2026 muestra su empresa y horas
        $response->assertSee('Empresa Pasada 2025 SL');
        $response->assertSee('160 horas');
    }

    public function test_student_in_both_first_and_second_year_groups_is_not_solo_primero(): void
    {
        $admin = $this->createAdmin();
        $ctx = $this->createAcademicContext();

        // Alumno que está en 1º y 2º a la vez (ej. repitiendo módulos)
        $userMixto = User::factory()->create(['name' => 'Carlos Alumno Mixto Primero y Segundo', 'roles' => [User::ROLE_ALUMNO]]);
        $alumnoMixto = Alumno::create([
            'user_id' => $userMixto->id,
            'empresa_practicas_primero' => 'Empresa Mixta SL',
            'horas_practicas_primero' => 120,
        ]);
        $alumnoMixto->grupos()->attach([
            $ctx['grupo1']->id => ['curso_academico_id' => $ctx['curso']->id],
            $ctx['grupo2']->id => ['curso_academico_id' => $ctx['curso']->id],
        ]);

        $this->assertFalse($alumnoMixto->soloEnGruposPrimero());
        $this->assertTrue($alumnoMixto->esSegundoCurso());

        $this->actingAs($admin);
        $response = $this->get(route('alumnos.index', ['search' => 'Carlos Alumno Mixto Primero y Segundo']));

        $response->assertOk();
        $response->assertSee('Empresa Mixta SL');
        $response->assertSee('120 horas');
    }
}
