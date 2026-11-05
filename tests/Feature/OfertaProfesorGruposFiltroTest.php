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
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfertaProfesorGruposFiltroTest extends TestCase
{
    use RefreshDatabase;

    public function test_profesor_solo_ve_ofertas_que_afectan_a_sus_grupos(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']
        );

        $familia = Familia::firstOrCreate(['codigo' => 'INF'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['nombre' => 'DAW', 'grado' => 'superior', 'familia_id' => $familia->id, 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Mañana', 'turno' => 'manana', 'is_active' => true]);

        // Grupo 1: El profesor da clase
        $grupoProfesor = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º DAW mañana',
            'is_active' => true,
        ]);

        // Grupo 2: Otro grupo al que no da clase
        $grupoOtro = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAW mañana',
            'is_active' => true,
        ]);

        $profesorUser = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'consent_rgpd' => true,
        ]);
        $profesor = Profesor::create([
            'user_id' => $profesorUser->id,
            'especialidad' => 'Informática',
        ]);
        $profesor->gruposImpartidos()->attach($grupoProfesor->id);

        $empresa = Empresa::factory()->create();

        // Oferta 1: Dirigida al grupo del profesor
        $ofertaMia = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesorUser->id,
            'creador_type' => User::class,
            'especialidad_requerida' => 'Desarrollo Web Frontend',
            'descripcion' => 'Oferta dirigida a mi grupo 1º DAW',
        ]);
        $ofertaMia->grupos()->attach($grupoProfesor->id);

        // Oferta 2: Dirigida a otro grupo
        $ofertaAjena = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesorUser->id,
            'creador_type' => User::class,
            'especialidad_requerida' => 'Sistemas y Redes',
            'descripcion' => 'Oferta para otro grupo 2º DAW',
        ]);
        $ofertaAjena->grupos()->attach($grupoOtro->id);

        $response = $this->actingAs($profesorUser)->get(route('ofertas.index'));

        $response->assertOk();
        $response->assertSee('Oferta dirigida a mi grupo 1º DAW');
        $response->assertDontSee('Oferta para otro grupo 2º DAW');
        // El selector de grupos contiene su grupo
        $response->assertSee('1º DAW mañana');
        $response->assertDontSee('2º DAW mañana');
    }

    public function test_profesor_puede_filtrar_por_grupo_especifico_de_sus_grupos(): void
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
            'nombre' => '1º DAM tarde',
            'is_active' => true,
        ]);
        $grupoB = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAM tarde',
            'is_active' => true,
        ]);

        $profesorUser = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'consent_rgpd' => true,
        ]);
        $profesor = Profesor::create([
            'user_id' => $profesorUser->id,
            'especialidad' => 'Informática',
        ]);
        $profesor->gruposImpartidos()->attach([$grupoA->id, $grupoB->id]);

        $empresa = Empresa::factory()->create();

        $ofertaA = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Java Apps',
            'descripcion' => 'Oferta Exclusiva Grupo A',
        ]);
        $ofertaA->grupos()->attach($grupoA->id);

        $ofertaB = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Android Apps',
            'descripcion' => 'Oferta Exclusiva Grupo B',
        ]);
        $ofertaB->grupos()->attach($grupoB->id);

        // Sin filtro de grupo, ve ambas porque da clase a ambos grupos
        $responseAll = $this->actingAs($profesorUser)->get(route('ofertas.index'));
        $responseAll->assertOk();
        $responseAll->assertSee('Oferta Exclusiva Grupo A');
        $responseAll->assertSee('Oferta Exclusiva Grupo B');

        // Filtrando por grupo A
        $responseA = $this->actingAs($profesorUser)->get(route('ofertas.index', ['grupo_id' => $grupoA->id]));
        $responseA->assertOk();
        $responseA->assertSee('Oferta Exclusiva Grupo A');
        $responseA->assertDontSee('Oferta Exclusiva Grupo B');
    }

    public function test_admin_y_coordinador_dual_ven_todas_las_ofertas_y_todos_los_grupos(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']
        );

        $familia = Familia::firstOrCreate(['codigo' => 'INF'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'SMR'], ['nombre' => 'SMR', 'grado' => 'medio', 'familia_id' => $familia->id, 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Mañana', 'turno' => 'manana', 'is_active' => true]);

        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º SMR mañana',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN], 'consent_rgpd' => true]);
        $empresa = Empresa::factory()->create();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Microinformática',
            'descripcion' => 'Oferta de SMR para Admin',
        ]);
        $oferta->grupos()->attach($grupo->id);

        $response = $this->actingAs($admin)->get(route('ofertas.index'));
        $response->assertOk();
        $response->assertSee('Oferta de SMR para Admin');
        $response->assertSee('1º SMR mañana');
        $response->assertSee('Grupos (Todos)');
    }

    public function test_alumnos_index_filtro_por_defecto_curso_actual_y_opciones_ampliadas(): void
    {
        $cursoActual = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']
        );
        $cursoPasado = CursoAcademico::create([
            'nombre' => '2025-2026',
            'fecha_inicio' => '2025-09-01',
            'fecha_fin' => '2026-06-30',
            'is_active' => false,
        ]);

        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN], 'consent_rgpd' => true]);

        $familia = Familia::firstOrCreate(['codigo' => 'INF'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['nombre' => 'DAW', 'grado' => 'superior', 'familia_id' => $familia->id, 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Mañana', 'turno' => 'manana', 'is_active' => true]);

        $grupoActual = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $cursoActual->id,
            'numero' => 1,
            'nombre' => '1º DAW Mañana 26-27',
            'is_active' => true,
        ]);

        $grupoPasado = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $cursoPasado->id,
            'numero' => 1,
            'nombre' => '1º DAW Mañana 25-26',
            'is_active' => true,
        ]);

        $userActual = User::factory()->create(['name' => 'Alumno Curso Actual', 'roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true]);
        $alumnoActual = Alumno::create(['user_id' => $userActual->id]);
        $alumnoActual->grupos()->attach($grupoActual->id, ['curso_academico_id' => $cursoActual->id]);

        $userPasado = User::factory()->create(['name' => 'Alumno Curso Pasado', 'roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true]);
        $alumnoPasado = Alumno::create(['user_id' => $userPasado->id]);
        $alumnoPasado->grupos()->attach($grupoPasado->id, ['curso_academico_id' => $cursoPasado->id]);

        // Carga inicial sin parámetros de curso (con búsqueda específica para aislar de la paginación):
        // filtra por defecto por el curso académico actual
        $response = $this->actingAs($admin)->get(route('alumnos.index', ['search' => 'Alumno Curso']));
        $response->assertOk();
        $response->assertSee('Alumno Curso Actual');
        $response->assertDontSee('Alumno Curso Pasado');

        // Botón de ampliar opciones de filtro y buscador amplio
        $response->assertSee('Ampliar opciones de filtro');
        $response->assertSee('Buscar por nombre o email de alumno...');

        // Filtrar explícitamente por el curso pasado
        $responsePasado = $this->actingAs($admin)->get(route('alumnos.index', [
            'search' => 'Alumno Curso',
            'curso_academico_id' => $cursoPasado->id,
        ]));
        $responsePasado->assertOk();
        $responsePasado->assertSee('Alumno Curso Pasado');
        $responsePasado->assertDontSee('Alumno Curso Actual');

        // Ver todos los cursos enviando curso_academico_id vacío
        $responseTodos = $this->actingAs($admin)->get(route('alumnos.index', [
            'search' => 'Alumno Curso',
            'curso_academico_id' => '',
        ]));
        $responseTodos->assertOk();
        $responseTodos->assertSee('Alumno Curso Actual');
        $responseTodos->assertSee('Alumno Curso Pasado');
    }
}

