<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Practica;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationAndFilterTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicBase(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'PAG_IFC'],
            ['nombre' => 'Paginacion Familia', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'PAG_DAW'],
            ['familia_id' => $familia->id, 'nombre' => 'Paginacion DAW', 'grado' => 'superior', 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'Paginacion Linea', 'is_active' => true]
        );

        $grupoA = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 1],
            ['nombre' => '1º DAW Mañana Grupo A', 'is_active' => true]
        );

        $grupoB = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 2],
            ['nombre' => '2º DAW Mañana Grupo B', 'is_active' => true]
        );

        return compact('curso', 'familia', 'ciclo', 'linea', 'grupoA', 'grupoB');
    }

    public function test_alumnos_list_is_paginated_to_15_per_page(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $base = $this->createAcademicBase();
        $initialCount = Alumno::count();

        // Crear 18 alumnos
        for ($i = 1; $i <= 18; $i++) {
            $user = User::factory()->create([
                'name' => sprintf('Alumno Z%02d', $i),
                'roles' => [User::ROLE_ALUMNO],
            ]);
            $alumno = Alumno::create(['user_id' => $user->id]);
            $alumno->grupos()->attach($base['grupoA']->id, ['curso_academico_id' => $base['curso']->id]);
        }

        $totalExpected = $initialCount + 18;

        $this->actingAs($admin);

        // Página 1
        $response1 = $this->get(route('alumnos.index'));
        $response1->assertOk();
        $alumnosP1 = $response1->viewData('alumnos');
        $this->assertEquals(15, $alumnosP1->perPage());
        $this->assertEquals($totalExpected, $alumnosP1->total());
        $this->assertCount(15, $alumnosP1);
        $this->assertTrue($alumnosP1->hasPages());

        // Página 2
        $response2 = $this->get(route('alumnos.index', ['page' => 2]));
        $response2->assertOk();
        $alumnosP2 = $response2->viewData('alumnos');
        $this->assertEquals(2, $alumnosP2->currentPage());
        $this->assertCount(min(15, $totalExpected - 15), $alumnosP2);
    }

    public function test_alumnos_can_be_filtered_by_grupo(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $base = $this->createAcademicBase();

        // Alumno en Grupo A
        $userA = User::factory()->create(['name' => 'Estudiante En Grupo A', 'roles' => [User::ROLE_ALUMNO]]);
        $alumnoA = Alumno::create(['user_id' => $userA->id]);
        $alumnoA->grupos()->attach($base['grupoA']->id, ['curso_academico_id' => $base['curso']->id]);

        // Alumno en Grupo B
        $userB = User::factory()->create(['name' => 'Estudiante En Grupo B', 'roles' => [User::ROLE_ALUMNO]]);
        $alumnoB = Alumno::create(['user_id' => $userB->id]);
        $alumnoB->grupos()->attach($base['grupoB']->id, ['curso_academico_id' => $base['curso']->id]);

        $this->actingAs($admin);

        // Filtrar por Grupo A
        $responseA = $this->get(route('alumnos.index', ['grupo_id' => $base['grupoA']->id]));
        $responseA->assertOk();
        $responseA->assertSee('Estudiante En Grupo A');
        $responseA->assertDontSee('Estudiante En Grupo B');

        // Filtrar por Grupo B
        $responseB = $this->get(route('alumnos.index', ['grupo_id' => $base['grupoB']->id]));
        $responseB->assertOk();
        $responseB->assertSee('Estudiante En Grupo B');
        $responseB->assertDontSee('Estudiante En Grupo A');
    }

    public function test_profesores_list_is_paginated_to_15_per_page(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $initialCount = Profesor::count();

        // Crear 17 profesores
        for ($i = 1; $i <= 17; $i++) {
            $user = User::factory()->create([
                'name' => sprintf('Profesor Z%02d', $i),
                'roles' => [User::ROLE_PROFESOR],
            ]);
            Profesor::create([
                'user_id' => $user->id,
                'especialidad' => 'Informática',
            ]);
        }

        $totalExpected = $initialCount + 17;

        $this->actingAs($admin);

        $response1 = $this->get(route('profesores.index'));
        $response1->assertOk();
        $profesoresP1 = $response1->viewData('profesores');
        $this->assertEquals(15, $profesoresP1->perPage());
        $this->assertEquals($totalExpected, $profesoresP1->total());
        $this->assertCount(15, $profesoresP1);

        $response2 = $this->get(route('profesores.index', ['page' => 2]));
        $response2->assertOk();
        $profesoresP2 = $response2->viewData('profesores');
        $this->assertEquals(2, $profesoresP2->currentPage());
        $this->assertCount(min(15, $totalExpected - 15), $profesoresP2);
    }

    public function test_practicas_list_is_paginated_to_15_per_page(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $base = $this->createAcademicBase();

        $empresa = Empresa::firstOrCreate(
            ['cif' => 'B77665544'],
            [
                'nombre' => 'Empresa Paginacion Test',
                'direccion' => 'Granada',
                'is_active' => true,
            ]
        );

        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO]]);
        $alumno = Alumno::create(['user_id' => $user->id]);

        for ($i = 1; $i <= 16; $i++) {
            Practica::create([
                'alumno_id' => $alumno->id,
                'empresa_id' => $empresa->id,
                'curso_academico_id' => $base['curso']->id,
                'fecha_inicio' => now()->subDays($i)->toDateString(),
                'fecha_fin' => now()->addDays(30)->toDateString(),
                'horas_acumuladas' => 10,
                'convenio_firmado' => true,
            ]);
        }

        $this->actingAs($admin);

        $response1 = $this->get(route('practicas.index'));
        $response1->assertOk();
        $this->assertCount(15, $response1->viewData('practicas'));

        $response2 = $this->get(route('practicas.index', ['page' => 2]));
        $response2->assertOk();
        $this->assertCount(1, $response2->viewData('practicas'));
    }

    public function test_empresas_list_is_paginated_to_15_per_page(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $initialCount = Empresa::count();

        for ($i = 1; $i <= 17; $i++) {
            Empresa::firstOrCreate(
                ['cif' => sprintf('A%08d', $i + 1000)],
                [
                    'nombre' => sprintf('Empresa Z%02d', $i),
                    'direccion' => 'Calle Test',
                    'is_active' => true,
                ]
            );
        }

        $totalExpected = $initialCount + 17;

        $this->actingAs($admin);

        $response1 = $this->get(route('empresas.index'));
        $response1->assertOk();
        $empresasP1 = $response1->viewData('empresas');
        $this->assertEquals(15, $empresasP1->perPage());
        $this->assertEquals($totalExpected, $empresasP1->total());
        $this->assertCount(15, $empresasP1);

        $response2 = $this->get(route('empresas.index', ['page' => 2]));
        $response2->assertOk();
        $empresasP2 = $response2->viewData('empresas');
        $this->assertEquals(2, $empresasP2->currentPage());
        $this->assertCount(min(15, $totalExpected - 15), $empresasP2);
    }
}
