<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Anotacion;
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

class OrdenacionAlumnosEvaluacionPracticasTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicEnvironment(): array
    {
        $curso = CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-25',
            'is_active' => true,
        ]);

        $familia = Familia::create([
            'nombre' => 'Informática ' . uniqid(),
            'codigo' => 'IFC' . rand(100, 999),
            'is_active' => true,
        ]);

        $ciclo = Ciclo::create([
            'familia_id' => $familia->id,
            'nombre' => 'Desarrollo de Aplicaciones Web',
            'codigo' => 'DAW' . rand(100, 999),
            'grado' => 'superior',
            'duracion_anos' => 2,
            'is_active' => true,
        ]);

        $linea = Linea::create([
            'ciclo_id' => $ciclo->id,
            'nombre' => 'Línea Mañana',
            'turno' => 'manana',
            'is_active' => true,
        ]);

        $grupoConEval = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAW Con Evaluaciones',
            'is_active' => true,
        ]);

        $grupoSinEval = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAW Sin Evaluaciones',
            'is_active' => true,
        ]);

        // Crear alumnos para grupoConEval:
        // Carlos (puesto 1 - mejor alumno)
        // Bea (puesto 2 - segundo mejor alumno)
        // Andrés (sin puesto)
        $userCarlos = User::factory()->create(['name' => 'Carlos Zapata', 'roles' => [User::ROLE_ALUMNO], 'is_active' => true]);
        $alumnoCarlos = Alumno::create(['user_id' => $userCarlos->id]);
        $alumnoCarlos->grupos()->attach($grupoConEval->id, ['curso_academico_id' => $curso->id]);

        $userBea = User::factory()->create(['name' => 'Beatriz Gómez', 'roles' => [User::ROLE_ALUMNO], 'is_active' => true]);
        $alumnoBea = Alumno::create(['user_id' => $userBea->id]);
        $alumnoBea->grupos()->attach($grupoConEval->id, ['curso_academico_id' => $curso->id]);

        $userAndres = User::factory()->create(['name' => 'Andrés Álvarez', 'roles' => [User::ROLE_ALUMNO], 'is_active' => true]);
        $alumnoAndres = Alumno::create(['user_id' => $userAndres->id]);
        $alumnoAndres->grupos()->attach($grupoConEval->id, ['curso_academico_id' => $curso->id]);

        // Crear alumnos para grupoSinEval
        $userDavid = User::factory()->create(['name' => 'David Morales', 'roles' => [User::ROLE_ALUMNO], 'is_active' => true]);
        $alumnoDavid = Alumno::create(['user_id' => $userDavid->id]);
        $alumnoDavid->grupos()->attach($grupoSinEval->id, ['curso_academico_id' => $curso->id]);

        $userElena = User::factory()->create(['name' => 'Elena Romero', 'roles' => [User::ROLE_ALUMNO], 'is_active' => true]);
        $alumnoElena = Alumno::create(['user_id' => $userElena->id]);
        $alumnoElena->grupos()->attach($grupoSinEval->id, ['curso_academico_id' => $curso->id]);

        // Crear profesor
        $userProf = User::factory()->create(['name' => 'Profesor Juan', 'roles' => [User::ROLE_PROFESOR], 'is_active' => true]);
        $profesor = Profesor::create(['user_id' => $userProf->id]);
        $grupoConEval->profesores()->attach($profesor->id);
        $grupoSinEval->profesores()->attach($profesor->id);

        // Crear anotaciones de evaluación del profesor para grupoConEval:
        // Carlos puesto 1
        Anotacion::create([
            'alumno_id' => $alumnoCarlos->id,
            'profesor_id' => $profesor->id,
            'titulo' => 'Excelente rendimiento en proyectos',
            'contenido' => 'El mejor alumno de la clase con gran autonomía técnica.',
            'puesto' => 1,
        ]);

        // Bea puesto 2
        Anotacion::create([
            'alumno_id' => $alumnoBea->id,
            'profesor_id' => $profesor->id,
            'titulo' => 'Muy buen trabajo en equipo',
            'contenido' => 'Constante y con gran capacidad de aprendizaje.',
            'puesto' => 2,
        ]);

        // Crear Coordinador Dual
        $userCoord = User::factory()->create([
            'name' => 'Coordinadora Marta',
            'roles' => [User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
        ]);

        // Crear Empresa y Oferta
        $empresa = Empresa::factory()->create(['nombre' => 'Tech Solutions']);
        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor->id,
            'titulo' => 'Desarrollador Web Junior',
            'descripcion' => 'Prácticas para DAW',
            'especialidad_requerida' => 'Desarrollo Web',
            'num_alumnos' => 2,
            'estado' => 'abierta',
        ]);
        $oferta->grupos()->attach([$grupoConEval->id, $grupoSinEval->id]);

        return [
            'curso' => $curso,
            'grupoConEval' => $grupoConEval,
            'grupoSinEval' => $grupoSinEval,
            'alumnoCarlos' => $alumnoCarlos,
            'alumnoBea' => $alumnoBea,
            'alumnoAndres' => $alumnoAndres,
            'alumnoDavid' => $alumnoDavid,
            'alumnoElena' => $alumnoElena,
            'profesor' => $profesor,
            'userProf' => $userProf,
            'userCoord' => $userCoord,
            'oferta' => $oferta,
        ];
    }

    public function test_practicas_por_grupo_shows_ranking_and_sorting_when_evaluations_exist(): void
    {
        $env = $this->createAcademicEnvironment();

        // 1. Petición por defecto (orden por evaluación)
        $response = $this->actingAs($env['userCoord'])->get(route('practicas.grupos.show', [
            'grupo' => $env['grupoConEval']->id,
            'orden' => 'evaluacion',
        ]));

        $response->assertOk();
        $response->assertSee('Ordenar por:');
        $response->assertSee('Nombre (A-Z)');
        $response->assertSee('Evaluación (Puestos)');
        $response->assertSee('Posición / Evaluación');

        // Carlos es #1 (mejor alumno)
        $response->assertSee('#1 Mejor alumno');
        // Bea es #2 (segundo mejor alumno)
        $response->assertSee('#2 Segundo mejor');
        // Andrés no tiene puesto -> Sin evaluar
        $response->assertSee('Sin evaluar');

        // En orden evaluación: Carlos debe aparecer antes que Beatriz, y Beatriz antes que Andrés
        $nombresEval = array_map(fn($item) => $item['nombre'], $response->viewData('alumnosSinPractica'));
        $this->assertEquals(['Carlos Zapata', 'Beatriz Gómez', 'Andrés Álvarez'], $nombresEval);

        $tableContentEval = strstr($response->getContent(), 'Alumnos sin práctica asignada');
        $carlosPos = strpos($tableContentEval, 'Carlos Zapata');
        $beaPos = strpos($tableContentEval, 'Beatriz Gómez');
        $andresPos = strpos($tableContentEval, 'Andrés Álvarez');

        $this->assertTrue($carlosPos < $beaPos, 'Carlos (#1) debe aparecer antes que Beatriz (#2) en orden por evaluación');
        $this->assertTrue($beaPos < $andresPos, 'Beatriz (#2) debe aparecer antes que Andrés (sin evaluar) en orden por evaluación');

        // 2. Petición ordenada por nombre
        $responseNombre = $this->actingAs($env['userCoord'])->get(route('practicas.grupos.show', [
            'grupo' => $env['grupoConEval']->id,
            'orden' => 'nombre',
        ]));

        $responseNombre->assertOk();
        $nombresNom = array_map(fn($item) => $item['nombre'], $responseNombre->viewData('alumnosSinPractica'));
        $this->assertEquals(['Andrés Álvarez', 'Beatriz Gómez', 'Carlos Zapata'], $nombresNom);

        $tableContentNom = strstr($responseNombre->getContent(), 'Alumnos sin práctica asignada');
        $carlosPosNom = strpos($tableContentNom, 'Carlos Zapata');
        $beaPosNom = strpos($tableContentNom, 'Beatriz Gómez');
        $andresPosNom = strpos($tableContentNom, 'Andrés Álvarez');

        $this->assertTrue($andresPosNom < $beaPosNom, 'Andrés debe aparecer antes que Beatriz en orden alfabético');
        $this->assertTrue($beaPosNom < $carlosPosNom, 'Beatriz debe aparecer antes que Carlos en orden alfabético');

        // Pero los números de posición siguen visibles para orientar la asignación
        $responseNombre->assertSee('#1 Mejor alumno');
        $responseNombre->assertSee('#2 Segundo mejor');
    }

    public function test_practicas_por_grupo_omits_evaluation_when_no_evaluations_exist(): void
    {
        $env = $this->createAcademicEnvironment();

        $response = $this->actingAs($env['userCoord'])->get(route('practicas.grupos.show', [
            'grupo' => $env['grupoSinEval']->id,
        ]));

        $response->assertOk();
        // Cuando no hay evaluaciones en el grupo, este aspecto se obvia
        $response->assertDontSee('Posición / Evaluación');
        $response->assertDontSee('Evaluación (Puestos)');
        $response->assertDontSee('#1 Mejor alumno');

        // Los alumnos se muestran normalmente
        $response->assertSee('David Morales');
        $response->assertSee('Elena Romero');
    }

    public function test_oferta_enviar_view_includes_evaluation_positions_and_sorting_controls(): void
    {
        $env = $this->createAcademicEnvironment();

        $response = $this->actingAs($env['userProf'])->get(route('ofertas.enviar-form', [
            'oferta' => $env['oferta']->id,
        ]));

        $response->assertOk();
        // Verificar que los datos del grupo con evaluación contienen las posiciones y ordenación
        $gruposData = $response->viewData('grupos');
        $grupoConEvalData = $gruposData->firstWhere('id', $env['grupoConEval']->id);
        $grupoSinEvalData = $gruposData->firstWhere('id', $env['grupoSinEval']->id);

        $this->assertTrue($grupoConEvalData['tiene_evaluaciones']);
        $this->assertFalse($grupoSinEvalData['tiene_evaluaciones']);

        // Carlos es #1
        $this->assertEquals(1, $grupoConEvalData['alumnos'][0]['posicion']);
        $this->assertEquals('Carlos Zapata', $grupoConEvalData['alumnos'][0]['nombre']);
        // Bea es #2
        $this->assertEquals(2, $grupoConEvalData['alumnos'][1]['posicion']);
        $this->assertEquals('Beatriz Gómez', $grupoConEvalData['alumnos'][1]['nombre']);
        // Andrés no tiene posición
        $this->assertNull($grupoConEvalData['alumnos'][2]['posicion']);

        // Debe contener los controles de ordenación para el grupo que tiene evaluaciones
        $response->assertSee('Ordenar por:');
        $response->assertSee('Nombre (A-Z)');
        $response->assertSee('Evaluación (Puestos)');

        // Debe contener los distintivos de posición calculados
        $response->assertSee('#1 Mejor alumno');
        $response->assertSee('#2 Segundo');

        // Debe contener la modal de observaciones
        $response->assertSee('Observaciones de evaluación del profesorado');
    }

    public function test_coordinador_dual_can_view_group_observations_in_practicas_por_grupo(): void
    {
        $env = $this->createAcademicEnvironment();

        $response = $this->actingAs($env['userCoord'])->get(route('practicas.grupos.show', [
            'grupo' => $env['grupoConEval']->id,
        ]));

        $response->assertOk();
        // Debe mostrar el bloque con las observaciones de profesores para este grupo
        $response->assertSee('Observaciones del profesorado de este grupo');
        $response->assertSee('Prof. Profesor Juan');
        $response->assertSee('Excelente rendimiento en proyectos');
        $response->assertSee('El mejor alumno de la clase con gran autonomía técnica.');
        $response->assertSee('Puesto #1');
        $response->assertSee('Puesto #2');
    }

    public function test_coordinador_dual_can_filter_observations_by_group_in_anotaciones_index(): void
    {
        $env = $this->createAcademicEnvironment();

        // 1. Filtrar por grupoConEval
        $response = $this->actingAs($env['userCoord'])->get(route('anotaciones.index', [
            'grupo_id' => $env['grupoConEval']->id,
        ]));

        $response->assertOk();
        $response->assertSee('Excelente rendimiento en proyectos');
        $response->assertSee('Muy buen trabajo en equipo');
        $response->assertSee('Carlos Zapata');
        $response->assertSee('Beatriz Gómez');

        // 2. Filtrar por grupoSinEval (debe salir vacío)
        $responseVacio = $this->actingAs($env['userCoord'])->get(route('anotaciones.index', [
            'grupo_id' => $env['grupoSinEval']->id,
        ]));

        $responseVacio->assertOk();
        $responseVacio->assertSee('Sin observaciones');
        $responseVacio->assertDontSee('Excelente rendimiento en proyectos');
    }

    public function test_coordinador_dual_sees_teacher_observations_in_anotaciones_create(): void
    {
        $env = $this->createAcademicEnvironment();

        $response = $this->actingAs($env['userCoord'])->get(route('anotaciones.create', [
            'grupo_id' => $env['grupoConEval']->id,
        ]));

        $response->assertOk();
        // El coordinador dual puede ver las observaciones que los profesores han registrado para ese grupo
        $response->assertSee('Observaciones del profesorado registradas para este grupo');
        $response->assertSee('Excelente rendimiento en proyectos');
        $response->assertSee('El mejor alumno de la clase con gran autonomía técnica.');
    }
}
