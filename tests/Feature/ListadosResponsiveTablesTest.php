<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Ciclo;
use App\Models\Familia;
use App\Models\OfertaPractica;
use App\Models\Practica;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListadosResponsiveTablesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $profesorUser;
    private Profesor $profesor;
    private Grupo $grupo;
    private CursoAcademico $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->curso = CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-30',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
        ]);

        $this->profesorUser = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);

        $familia = Familia::create([
            'codigo' => 'IFC',
            'nombre' => 'Informática',
            'is_active' => true,
        ]);

        $ciclo = Ciclo::create([
            'familia_id' => $familia->id,
            'codigo' => 'DAM',
            'nombre' => 'Desarrollo de Aplicaciones Multiplataforma',
            'grado' => 'superior',
            'duracion_anos' => 2,
            'is_active' => true,
        ]);

        $linea = Linea::create([
            'ciclo_id' => $ciclo->id,
            'turno' => 'manana',
            'nombre' => 'Mañana',
            'is_active' => true,
        ]);

        $this->profesor = Profesor::create([
            'user_id' => $this->profesorUser->id,
            'especialidad' => 'Informática',
            'es_tutor' => true,
            'es_coordinador_dual' => false,
        ]);

        $this->grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $this->curso->id,
            'numero' => 2,
            'nombre' => '2º DAM',
            'tutor_id' => $this->profesorUser->id,
            'is_active' => true,
        ]);
    }

    public function test_alumnos_index_incluye_tabla_desktop_con_scroll_min_width_y_tarjetas_moviles(): void
    {
        $alumnoUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'name' => 'Carlos Responsivo',
        ]);
        $alumno = Alumno::create([
            'user_id' => $alumnoUser->id,
        ]);
        $alumno->grupos()->attach($this->grupo->id, ['curso_academico_id' => $this->curso->id]);

        $response = $this->actingAs($this->admin)->get(route('alumnos.index'));

        $response->assertOk();
        // Contenedor desktop con protección de ancho mínimo y clase hidden md:block
        $response->assertSee('stitch-table-container hidden md:block', false);
        $response->assertSee('stitch-table min-w-[920px]', false);
        // Vista responsiva para móviles/tablets con md:hidden
        $response->assertSee('md:hidden', false);
        $response->assertSee('Carlos Responsivo');
    }

    public function test_practicas_grupos_show_incluye_tablas_con_min_width_y_tarjetas_moviles(): void
    {
        $alumnoUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'name' => 'Lucía Alumna',
        ]);
        $alumno = Alumno::create([
            'user_id' => $alumnoUser->id,
        ]);
        $alumno->grupos()->attach($this->grupo->id, ['curso_academico_id' => $this->curso->id]);

        $empresa = Empresa::create([
            'cif' => 'B12345678',
            'nombre' => 'Tech Corp Responsiva',
            'is_active' => true,
            'admite_alumnos_curso_actual' => true,
        ]);

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => $this->profesorUser->id,
            'especialidad_requerida' => 'DAM',
            'num_alumnos' => 2,
            'estado' => 'activa',
        ]);
        $oferta->grupos()->attach($this->grupo->id);

        $response = $this->actingAs($this->profesorUser)->get(route('practicas.grupos.show', $this->grupo));

        $response->assertOk();
        // Tabla de alumnos sin práctica con min-w-[700px] y versión móvil md:hidden
        $response->assertSee('stitch-table min-w-[700px]', false);
        // Tabla de plazas disponibles con min-w-[720px] y versión móvil md:hidden
        $response->assertSee('stitch-table min-w-[720px]', false);
        $response->assertSee('md:hidden', false);
        $response->assertSee('Lucía Alumna');
        $response->assertSee('Tech Corp Responsiva');
    }

    public function test_empresas_index_incluye_tabla_con_min_width_y_tarjetas_moviles(): void
    {
        Empresa::create([
            'cif' => 'A98765432',
            'nombre' => 'Empresa Test Responsive',
            'is_active' => true,
            'admite_alumnos_curso_actual' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('empresas.index'));

        $response->assertOk();
        $response->assertSee('stitch-table-container hidden md:block', false);
        $response->assertSee('stitch-table min-w-[850px]', false);
        $response->assertSee('md:hidden', false);
        $response->assertSee('Empresa Test Responsive');
    }

    public function test_profesores_index_incluye_tabla_con_min_width_y_tarjetas_moviles(): void
    {
        $response = $this->actingAs($this->admin)->get(route('profesores.index'));

        $response->assertOk();
        $response->assertSee('stitch-table-container hidden md:block', false);
        $response->assertSee('stitch-table min-w-[760px]', false);
        $response->assertSee('md:hidden', false);
    }

    public function test_practicas_index_incluye_tabla_con_min_width_y_tarjetas_moviles(): void
    {
        $response = $this->actingAs($this->admin)->get(route('practicas.index'));

        $response->assertOk();
        $response->assertSee('stitch-table-container hidden md:block', false);
        $response->assertSee('stitch-table min-w-[850px]', false);
        $response->assertSee('md:hidden', false);
    }

    public function test_anotaciones_create_incluye_tabla_con_min_width_y_tarjetas_moviles(): void
    {
        $alumnoUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'name' => 'Alumno Para Anotacion',
        ]);
        $alumno = Alumno::create([
            'user_id' => $alumnoUser->id,
        ]);
        $alumno->grupos()->attach($this->grupo->id, ['curso_academico_id' => $this->curso->id]);

        $response = $this->actingAs($this->profesorUser)->get(route('anotaciones.create', ['grupo_id' => $this->grupo->id]));

        $response->assertOk();
        $response->assertSee('stitch-table-container hidden md:block', false);
        $response->assertSee('stitch-table min-w-[720px]', false);
        $response->assertSee('md:hidden', false);
        $response->assertSee('Alumno Para Anotacion');
    }
}
