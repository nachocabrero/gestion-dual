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
use App\Support\StrSort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExclusionAlumnosRepiteYOdenacionTildesTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createProfesor(string $name = 'Profesor Test'): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => 'prof_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $profesor = Profesor::create([
            'user_id' => $user->id,
            'especialidad' => 'Informática',
            'es_tutor' => false,
        ]);

        return [$user, $profesor];
    }

    private function createAlumno(string $name, bool $repitiendo = false): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => 'alum_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
            'repitiendo_modulos_primero' => $repitiendo,
        ]);

        return [$user, $alumno];
    }

    private function createGrupo(string $nombre = '1º DAW A', ?User $tutor = null): Grupo
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'INF'],
            ['nombre' => 'Informática', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAW'],
            ['familia_id' => $familia->id, 'nombre' => 'DAW', 'grado' => 'superior', 'duracion_anos' => 2, 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAW Mañana', 'is_active' => true]
        );

        return Grupo::create([
            'nombre' => $nombre,
            'numero' => 1,
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'tutor_id' => $tutor?->id,
            'is_active' => true,
        ]);
    }

    public function test_strsort_normalizes_accents_correctly(): void
    {
        $this->assertEquals('angel', StrSort::normalize('Ángel'));
        $this->assertEquals('oscar', StrSort::normalize('Óscar'));
        $this->assertEquals('ursula', StrSort::normalize('Úrsula'));
        $this->assertEquals('iñigo', StrSort::normalize('Íñigo'));
        $this->assertEquals('erica', StrSort::normalize('Érica'));
        $this->assertEquals('guero', StrSort::normalize('Güero'));
    }

    public function test_strsort_compares_angel_before_beatriz(): void
    {
        // Ángel starts with 'A', so it must come BEFORE Beatriz ('B')
        $this->assertLessThan(0, StrSort::compare('Ángel', 'Beatriz'));
        // Alberto comes before Ángel
        $this->assertGreaterThan(0, StrSort::compare('Ángel', 'Alberto'));
        // Ángel comes before Antonio
        $this->assertLessThan(0, StrSort::compare('Ángel', 'Antonio'));
    }

    public function test_alumno_repitiendo_primero_is_excluded_from_oferta_enviar_form(): void
    {
        $admin = $this->createAdmin();
        $grupo = $this->createGrupo('1º DAW A');
        $curso = $grupo->cursoAcademico;

        [$uNormal, $aNormal] = $this->createAlumno('Carlos Normal', false);
        $grupo->alumnos()->attach($aNormal->id, ['curso_academico_id' => $curso->id]);

        [$uRepite, $aRepite] = $this->createAlumno('Roberto Repite', true);
        $grupo->alumnos()->attach($aRepite->id, ['curso_academico_id' => $curso->id]);

        $empresa = Empresa::create([
            'nombre' => 'Tech Solutions',
            'cif' => 'B12345678',
            'is_active' => true,
        ]);

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Desarrollo Web',
            'num_alumnos' => 2,
            'estado' => 'abierta',
            'creador_id' => $admin->id,
        ]);
        $oferta->grupos()->attach($grupo->id);

        $response = $this->actingAs($admin)->get(route('ofertas.enviar', $oferta));
        $response->assertOk();

        // Carlos Normal is present, but Roberto Repite is excluded
        $response->assertSee('Carlos Normal');
        $response->assertDontSee('Roberto Repite');
    }

    public function test_alumno_repitiendo_primero_is_excluded_from_grupo_practicas_show(): void
    {
        $admin = $this->createAdmin();
        $grupo = $this->createGrupo('1º DAM A');
        $curso = $grupo->cursoAcademico;

        [$uNormal, $aNormal] = $this->createAlumno('Manuel Normal', false);
        $grupo->alumnos()->attach($aNormal->id, ['curso_academico_id' => $curso->id]);

        [$uRepite, $aRepite] = $this->createAlumno('Sara Repite', true);
        $grupo->alumnos()->attach($aRepite->id, ['curso_academico_id' => $curso->id]);

        $response = $this->actingAs($admin)->get(route('practicas.grupos.show', $grupo));
        $response->assertOk();

        $response->assertSee('Manuel Normal');
        $response->assertDontSee('Sara Repite');
    }

    public function test_alumno_repitiendo_primero_is_excluded_from_practicas_create(): void
    {
        $admin = $this->createAdmin();

        [$uNormal, $aNormal] = $this->createAlumno('Elena Normal', false);
        [$uRepite, $aRepite] = $this->createAlumno('Lucas Repite', true);

        $response = $this->actingAs($admin)->get(route('practicas.create'));
        $response->assertOk();

        $alumnos = $response->viewData('alumnos');
        $this->assertTrue($alumnos->contains('id', $aNormal->id));
        $this->assertFalse($alumnos->contains('id', $aRepite->id));
    }

    public function test_alumno_repitiendo_primero_is_excluded_from_anotaciones_create(): void
    {
        [$profesorUser, $profesor] = $this->createProfesor();
        $grupo = $this->createGrupo('1º DAW B', $profesorUser);
        $curso = $grupo->cursoAcademico;

        [$uNormal, $aNormal] = $this->createAlumno('David Normal', false);
        $grupo->alumnos()->attach($aNormal->id, ['curso_academico_id' => $curso->id]);

        [$uRepite, $aRepite] = $this->createAlumno('Marcos Repite', true);
        $grupo->alumnos()->attach($aRepite->id, ['curso_academico_id' => $curso->id]);

        $response = $this->actingAs($profesorUser)->get(route('anotaciones.create', ['grupo_id' => $grupo->id]));
        $response->assertOk();

        $alumnosData = $response->viewData('alumnosData');
        $ids = $alumnosData->pluck('id')->all();

        $this->assertContains($aNormal->id, $ids);
        $this->assertNotContains($aRepite->id, $ids);
    }

    public function test_anotaciones_create_sorts_alumnos_with_accents_correctly(): void
    {
        [$profesorUser, $profesor] = $this->createProfesor();
        $grupo = $this->createGrupo('1º DAM B', $profesorUser);
        $curso = $grupo->cursoAcademico;

        [$uBeatriz, $aBeatriz] = $this->createAlumno('Beatriz Gómez', false);
        $grupo->alumnos()->attach($aBeatriz->id, ['curso_academico_id' => $curso->id]);

        [$uAngel, $aAngel] = $this->createAlumno('Ángel Castillo', false);
        $grupo->alumnos()->attach($aAngel->id, ['curso_academico_id' => $curso->id]);

        [$uAlberto, $aAlberto] = $this->createAlumno('Alberto Álvarez', false);
        $grupo->alumnos()->attach($aAlberto->id, ['curso_academico_id' => $curso->id]);

        $response = $this->actingAs($profesorUser)->get(route('anotaciones.create', ['grupo_id' => $grupo->id]));
        $response->assertOk();

        $alumnosData = $response->viewData('alumnosData');
        $nombres = $alumnosData->pluck('name')->all();

        // Alberto Álvarez < Ángel Castillo < Beatriz Gómez
        $this->assertEquals(['Alberto Álvarez', 'Ángel Castillo', 'Beatriz Gómez'], $nombres);
    }

    public function test_empresas_index_sorts_empresas_with_accents_correctly(): void
    {
        $admin = $this->createAdmin();

        Empresa::create(['nombre' => 'Beta Systems', 'cif' => 'B00000001', 'is_active' => true]);
        Empresa::create(['nombre' => 'Ágora Digital', 'cif' => 'B00000002', 'is_active' => true]);
        Empresa::create(['nombre' => 'Alfa Corp', 'cif' => 'B00000003', 'is_active' => true]);
        Empresa::create(['nombre' => 'Óptica Central', 'cif' => 'B00000004', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('empresas.index', ['per_page' => 50]));
        $response->assertOk();

        $empresas = $response->viewData('empresas');
        $nombres = $empresas->pluck('nombre')->all();

        // Ágora < Alfa < Beta < Óptica
        $this->assertEquals(['Ágora Digital', 'Alfa Corp', 'Beta Systems', 'Óptica Central'], $nombres);
    }

    public function test_empresas_index_sorts_profesores_dropdown_with_accents_correctly(): void
    {
        $admin = $this->createAdmin();

        [$uBeatriz] = $this->createProfesor('Beatriz Docente');
        [$uAngel] = $this->createProfesor('Ángel Tutor');
        [$uAlberto] = $this->createProfesor('Alberto Profesor');

        $response = $this->actingAs($admin)->get(route('empresas.index'));
        $response->assertOk();

        $profesores = $response->viewData('profesores');
        $nombres = $profesores->map(fn($p) => $p->user->name)->values()->all();

        // Filter for our 3 test profesores to assert their relative order
        $nombres = array_values(array_intersect($nombres, ['Alberto Profesor', 'Ángel Tutor', 'Beatriz Docente']));

        // Alberto Profesor < Ángel Tutor < Beatriz Docente
        $this->assertEquals(['Alberto Profesor', 'Ángel Tutor', 'Beatriz Docente'], $nombres);
    }

    public function test_profesores_index_sorts_profesores_with_accents_correctly(): void
    {
        $admin = $this->createAdmin();

        [$uBeatriz] = $this->createProfesor('Beatriz Docente');
        [$uAngel] = $this->createProfesor('Ángel Tutor');
        [$uAlberto] = $this->createProfesor('Alberto Profesor');

        $response = $this->actingAs($admin)->get(route('profesores.index', ['per_page' => 50]));
        $response->assertOk();

        $profesores = $response->viewData('profesores');
        $nombres = $profesores->map(fn($p) => $p->user->name)->values()->all();

        $nombres = array_values(array_intersect($nombres, ['Alberto Profesor', 'Ángel Tutor', 'Beatriz Docente']));

        $this->assertEquals(['Alberto Profesor', 'Ángel Tutor', 'Beatriz Docente'], $nombres);
    }

    public function test_alumnos_index_sorts_alumnos_with_accents_correctly(): void
    {
        $admin = $this->createAdmin();

        [$uBeatriz] = $this->createAlumno('Beatriz Gómez');
        [$uAngel] = $this->createAlumno('Ángel Castillo');
        [$uAlberto] = $this->createAlumno('Alberto Álvarez');

        $response = $this->actingAs($admin)->get(route('alumnos.index', ['per_page' => 50]));
        $response->assertOk();

        $alumnos = $response->viewData('alumnos');
        $nombres = $alumnos->map(fn($a) => $a->user->name)->values()->all();

        $nombres = array_values(array_intersect($nombres, ['Alberto Álvarez', 'Ángel Castillo', 'Beatriz Gómez']));

        $this->assertEquals(['Alberto Álvarez', 'Ángel Castillo', 'Beatriz Gómez'], $nombres);
    }
}
