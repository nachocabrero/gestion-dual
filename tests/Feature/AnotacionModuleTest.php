<?php

namespace Tests\Feature;

use App\Models\Anotacion;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnotacionModuleTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * Crear un admin con perfil profesor vinculado.
     */
    private function createAdmin(): User
    {
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Admin',
            'es_tutor' => true,
        ]);
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
        // Vincular el admin al perfil profesor
        $profesor->update(['user_id' => $admin->id]);
        return $admin;
    }

    private function createCoordinador(): User
    {
        return User::factory()->create([
            'roles' => [User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    /**
     * Crear un profesor con user y devolver ambos.
     */
    private function createProfesorUser(): array
    {
        $profesor = Profesor::create([
            'user_id' => User::factory()->create([
                'roles' => [User::ROLE_PROFESOR],
                'is_active' => true,
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ])->id,
            'especialidad' => 'Desarrollo Web',
            'es_tutor' => true,
        ]);
        return ['profesor' => $profesor, 'user' => $profesor->user];
    }

    /**
     * Crear un alumno en un grupo. Reutiliza familia si existe.
     */
    private function createAlumnoEnGrupo(?Grupo $grupo = null): Alumno
    {
        if (!$grupo) {
            $familia = Familia::firstOrCreate(
                ['codigo' => 'INFORMATICA'],
                ['nombre' => 'Informática', 'is_active' => true]
            );
            $ciclo = Ciclo::firstOrCreate(
                ['codigo' => 'DAW'],
                ['familia_id' => $familia->id, 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]
            );
            $linea = Linea::firstOrCreate(
                ['nombre' => 'DAW Mañana'],
                ['ciclo_id' => $ciclo->id, 'turno' => 'manana', 'is_active' => true]
            );
            $grupo = Grupo::firstOrCreate(
                ['nombre' => '1º DAW-Manana'],
                ['linea_id' => $linea->id, 'numero' => 1, 'is_active' => true]
            );
        }
        $alumno = Alumno::create([
            'user_id' => User::factory()->create([
                'roles' => [User::ROLE_ALUMNO],
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ])->id,
            'telefono' => '600123456',
        ]);
        $alumno->grupos()->attach($grupo->id);
        return $alumno;
    }

    // ─────────────────────────────────────────────
    // 1. INDEX
    // ─────────────────────────────────────────────

    public function test_admin_can_view_anotaciones_index(): void
    {
        $admin = $this->createAdmin();
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test',
            'es_tutor' => false,
        ]);
        $alumno = $this->createAlumnoEnGrupo();
        Anotacion::create([
            'alumno_id' => $alumno->id,
            'profesor_id' => $profesor->id,
            'titulo' => 'Test',
            'contenido' => 'Test',
        ]);

        $response = $this->actingAs($admin)->get(route('anotaciones.index'));

        $response->assertOk();
        $response->assertViewIs('anotaciones.index');
    }

    public function test_coordinador_can_view_anotaciones_index(): void
    {
        $coord = $this->createCoordinador();
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test',
            'es_tutor' => false,
        ]);
        $alumno = $this->createAlumnoEnGrupo();
        Anotacion::create([
            'alumno_id' => $alumno->id,
            'profesor_id' => $profesor->id,
            'titulo' => 'Test',
            'contenido' => 'Test',
        ]);
        Anotacion::create([
            'alumno_id' => $alumno->id,
            'profesor_id' => $profesor->id,
            'titulo' => 'Test2',
            'contenido' => 'Test2',
        ]);

        $response = $this->actingAs($coord)->get(route('anotaciones.index'));

        $response->assertOk();
        $response->assertViewHas('anotaciones');
    }

    public function test_profesor_can_view_own_anotaciones(): void
    {
        $p1 = $this->createProfesorUser();
        $profesor1 = $p1['profesor'];

        $p2 = $this->createProfesorUser();
        $profesor2 = $p2['profesor'];

        $alumno = $this->createAlumnoEnGrupo();
        // profesor1 imparte el grupo del alumno (equipo educativo)
        $profesor1->gruposImpartidos()->attach($alumno->grupos->first()->id);

        // Anotación propia
        Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor1->id,
            'titulo' => 'Propia', 'contenido' => 'Propia',
        ]);
        // Anotación de otro profesor del mismo grupo (visible siempre para el equipo)
        Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor2->id,
            'titulo' => 'De otro profesor', 'contenido' => 'De otro profesor',
        ]);

        $response = $this->actingAs($p1['user'])->get(route('anotaciones.index'));

        $response->assertOk();
        $anotaciones = $response->viewData('anotaciones');
        $this->assertEquals(2, $anotaciones->total());
    }

    public function test_alumno_cannot_view_anotaciones(): void
    {
        $alumno = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $response = $this->actingAs($alumno)->get(route('anotaciones.index'));

        $response->assertForbidden();
    }

    public function test_anotaciones_index_can_filter_by_alumno(): void
    {
        $admin = $this->createAdmin();
        $alumno1 = $this->createAlumnoEnGrupo();
        $alumno1->user->update(['name' => 'Juan Pérez']);
        $alumno2 = $this->createAlumnoEnGrupo();
        $alumno2->user->update(['name' => 'María López']);
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        Anotacion::create(['alumno_id' => $alumno1->id, 'profesor_id' => $profesor->id, 'titulo' => 'T', 'contenido' => 'C']);
        Anotacion::create(['alumno_id' => $alumno2->id, 'profesor_id' => $profesor->id, 'titulo' => 'T', 'contenido' => 'C']);

        $response = $this->actingAs($admin)->get(route('anotaciones.index', ['alumno' => 'Juan']));

        $response->assertOk();
        $anotaciones = $response->viewData('anotaciones');
        $this->assertEquals(1, $anotaciones->total());
    }

    // ─────────────────────────────────────────────
    // 2. CREATE
    // ─────────────────────────────────────────────

    public function test_admin_can_see_create_form(): void
    {
        $admin = $this->createAdmin();
        $this->createAlumnoEnGrupo();

        $response = $this->actingAs($admin)->get(route('anotaciones.create'));

        $response->assertOk();
        $response->assertViewIs('anotaciones.create');
        $response->assertViewHas('alumnos');
    }

    public function test_coordinador_can_see_create_form(): void
    {
        $coord = $this->createCoordinador();
        $this->createAlumnoEnGrupo();

        $response = $this->actingAs($coord)->get(route('anotaciones.create'));

        $response->assertOk();
        $response->assertViewHas('alumnos');
    }

    public function test_profesor_cannot_see_create_form(): void
    {
        $p = $this->createProfesorUser();
        $response = $this->actingAs($p['user'])->get(route('anotaciones.create'));
        $response->assertForbidden();
    }

    // ─────────────────────────────────────────────
    // 3. STORE
    // ─────────────────────────────────────────────

    public function test_admin_can_create_anotacion(): void
    {
        $admin = $this->createAdmin();
        $profesor = Profesor::where('user_id', $admin->id)->first();
        $alumno = $this->createAlumnoEnGrupo();

        $data = [
            'alumno_id' => $alumno->id,
            'titulo' => 'Buen rendimiento en BD',
            'contenido' => 'Juan demuestra gran capacidad en bases de datos.',
        ];

        $response = $this->actingAs($admin)->post(route('anotaciones.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('anotaciones', [
            'alumno_id' => $alumno->id,
            'profesor_id' => $profesor->id,
            'titulo' => 'Buen rendimiento en BD',
        ]);
    }

    public function test_coordinador_can_create_anotacion(): void
    {
        $coord = $this->createCoordinador();
        $alumno = $this->createAlumnoEnGrupo();

        $data = [
            'alumno_id' => $alumno->id,
            'titulo' => 'Anotación de coordinador',
            'contenido' => 'Coordinador Dual anotación.',
        ];

        $response = $this->actingAs($coord)->post(route('anotaciones.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('anotaciones', [
            'alumno_id' => $alumno->id,
            'titulo' => 'Anotación de coordinador',
            'profesor_id' => null,
        ]);
    }

    public function test_anotacion_store_requires_validation(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->post(route('anotaciones.store'), []);
        $response->assertSessionHasErrors(['alumno_id', 'titulo', 'contenido']);
    }

    public function test_anotacion_store_requires_valid_alumno(): void
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->post(route('anotaciones.store'), [
            'alumno_id' => 99999,
            'titulo' => 'Test',
            'contenido' => 'Test',
        ]);
        $response->assertSessionHasErrors('alumno_id');
    }

    // ─────────────────────────────────────────────
    // 4. EDIT
    // ─────────────────────────────────────────────

    public function test_profesor_can_edit_own_anotacion(): void
    {
        $p = $this->createProfesorUser();
        $profesor = $p['profesor'];
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Original', 'contenido' => 'Original',
        ]);

        $response = $this->actingAs($p['user'])->get(route('anotaciones.edit', $anotacion));
        $response->assertOk();
        $response->assertViewIs('anotaciones.edit');
        $response->assertViewHas('anotacion', fn($a) => $a->id === $anotacion->id);
    }

    public function test_profesor_cannot_edit_other_anotacion(): void
    {
        $p1 = $this->createProfesorUser();
        $p2 = $this->createProfesorUser();
        $profesor1 = $p1['profesor'];
        $profesor2 = $p2['profesor'];
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor2->id,
            'titulo' => 'Otro', 'contenido' => 'Otro',
        ]);

        $response = $this->actingAs($p1['user'])->get(route('anotaciones.edit', $anotacion));
        $response->assertForbidden();
    }

    public function test_admin_can_edit_any_anotacion(): void
    {
        $admin = $this->createAdmin();
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Test', 'contenido' => 'Test',
        ]);

        $response = $this->actingAs($admin)->get(route('anotaciones.edit', $anotacion));
        $response->assertOk();
        $response->assertViewHas('anotacion', fn($a) => $a->id === $anotacion->id);
    }

    // ─────────────────────────────────────────────
    // 5. UPDATE
    // ─────────────────────────────────────────────

    public function test_profesor_can_update_own_anotacion(): void
    {
        $p = $this->createProfesorUser();
        $profesor = $p['profesor'];
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Original', 'contenido' => 'Original',
        ]);

        $data = ['titulo' => 'Actualizado', 'contenido' => 'Contenido actualizado.'];
        $response = $this->actingAs($p['user'])->put(route('anotaciones.update', $anotacion), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('anotaciones', [
            'id' => $anotacion->id,
            'titulo' => 'Actualizado',
            'contenido' => 'Contenido actualizado.',
        ]);
    }

    public function test_profesor_cannot_update_other_anotacion(): void
    {
        $p1 = $this->createProfesorUser();
        $p2 = $this->createProfesorUser();
        $profesor1 = $p1['profesor'];
        $profesor2 = $p2['profesor'];
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor2->id,
            'titulo' => 'Original', 'contenido' => 'Original',
        ]);

        $response = $this->actingAs($p1['user'])->put(route('anotaciones.update', $anotacion), [
            'titulo' => 'Hacked', 'contenido' => 'Hacked',
        ]);
        $response->assertForbidden();
        $this->assertDatabaseMissing('anotaciones', ['titulo' => 'Hacked']);
    }

    // ─────────────────────────────────────────────
    // 6. DESTROY
    // ─────────────────────────────────────────────

    public function test_admin_can_delete_anotacion(): void
    {
        $admin = $this->createAdmin();
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Test', 'contenido' => 'Test',
        ]);

        $response = $this->actingAs($admin)->delete(route('anotaciones.destroy', $anotacion));
        $response->assertRedirect();
        $this->assertDatabaseMissing('anotaciones', ['id' => $anotacion->id]);
    }

    public function test_profesor_cannot_delete_anotacion(): void
    {
        $p = $this->createProfesorUser();
        $profesor = $p['profesor'];
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Test', 'contenido' => 'Test',
        ]);

        $response = $this->actingAs($p['user'])->delete(route('anotaciones.destroy', $anotacion));
        $response->assertForbidden();
    }

    // ─────────────────────────────────────────────
    // 7. SHOW
    // ─────────────────────────────────────────────

    public function test_tutor_can_view_alumno_anotaciones(): void
    {
        $p = $this->createProfesorUser();
        $profesor = $p['profesor'];
        $alumno = $this->createAlumnoEnGrupo();
        // El tutor del grupo debe ser el user_id del profesor
        $grupo = $alumno->grupos->first();
        $grupo->update(['tutor_id' => $p['user']->id]);

        Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Test', 'contenido' => 'Test',
        ]);

        $response = $this->actingAs($p['user'])->get(route('anotaciones.show', $alumno->id));
        $response->assertOk();
        $response->assertViewIs('anotaciones.show');
        $response->assertViewHas('alumno', fn($a) => $a->id === $alumno->id);
        $response->assertViewHas('anotaciones');
    }

    public function test_profesor_cannot_view_other_group_alumno(): void
    {
        $p = $this->createProfesorUser();
        $profesor = $p['profesor'];

        // Crear otro grupo con otro tutor
        $familia = Familia::firstOrCreate(['codigo' => 'ADMIN'], ['nombre' => 'Administración', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'ASX'], ['familia_id' => $familia->id, 'nombre' => 'ASX', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::firstOrCreate(['nombre' => 'ASX Mañana'], ['ciclo_id' => $ciclo->id, 'turno' => 'manana', 'is_active' => true]);
        $grupo2 = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º ASX-Manana', 'is_active' => true]);

        // Asignar otro profesor como tutor del grupo2
        $p2 = $this->createProfesorUser();
        $grupo2->update(['tutor_id' => $p2['user']->id]);

        $alumno2 = Alumno::create([
            'user_id' => User::factory()->create([
                'roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now(),
            ])->id,
            'telefono' => '600999888',
        ]);
        $alumno2->grupos()->attach($grupo2->id);

        $response = $this->actingAs($p['user'])->get(route('anotaciones.show', $alumno2->id));
        $response->assertForbidden();
    }

    public function test_coordinador_can_view_any_alumno_anotaciones(): void
    {
        $coord = $this->createCoordinador();
        $alumno = $this->createAlumnoEnGrupo();
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Test', 'contenido' => 'Test',
        ]);

        $response = $this->actingAs($coord)->get(route('anotaciones.show', $alumno->id));
        $response->assertOk();
        $response->assertViewHas('alumno', fn($a) => $a->id === $alumno->id);
    }

    public function test_admin_can_view_any_alumno_anotaciones(): void
    {
        $admin = $this->createAdmin();
        $alumno = $this->createAlumnoEnGrupo();
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Test', 'contenido' => 'Test',
        ]);

        $response = $this->actingAs($admin)->get(route('anotaciones.show', $alumno->id));
        $response->assertOk();
        $response->assertViewHas('alumno', fn($a) => $a->id === $alumno->id);
    }

    // ─────────────────────────────────────────────
    // 8. MODEL
    // ─────────────────────────────────────────────

    public function test_anotacion_model_has_correct_table(): void
    {
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Test', 'contenido' => 'Test',
        ]);
        $this->assertEquals('anotaciones', $anotacion->getTable());
    }

    public function test_anotacion_scope_para_alumno(): void
    {
        $alumno1 = $this->createAlumnoEnGrupo();
        $alumno2 = $this->createAlumnoEnGrupo();
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        Anotacion::create(['alumno_id' => $alumno1->id, 'profesor_id' => $profesor->id, 'titulo' => 'T', 'contenido' => 'C']);
        Anotacion::create(['alumno_id' => $alumno1->id, 'profesor_id' => $profesor->id, 'titulo' => 'T', 'contenido' => 'C']);
        Anotacion::create(['alumno_id' => $alumno2->id, 'profesor_id' => $profesor->id, 'titulo' => 'T', 'contenido' => 'C']);

        $result = Anotacion::paraAlumno($alumno1->id)->get();
        $this->assertCount(2, $result);
    }

    public function test_anotacion_scope_creadas_por(): void
    {
        $profesor1 = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        $profesor2 = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        $alumno = $this->createAlumnoEnGrupo();
        Anotacion::create(['alumno_id' => $alumno->id, 'profesor_id' => $profesor1->id, 'titulo' => 'T', 'contenido' => 'C']);
        Anotacion::create(['alumno_id' => $alumno->id, 'profesor_id' => $profesor1->id, 'titulo' => 'T', 'contenido' => 'C']);
        Anotacion::create(['alumno_id' => $alumno->id, 'profesor_id' => $profesor2->id, 'titulo' => 'T', 'contenido' => 'C']);

        $result = Anotacion::creadasPor($profesor1->id)->get();
        $this->assertCount(2, $result);
    }

    public function test_anotacion_scope_visibles_para(): void
    {
        $profesor1 = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        $profesor2 = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        $alumno = $this->createAlumnoEnGrupo();
        // profesor1 imparte el grupo del alumno; profesor2 no
        $profesor1->gruposImpartidos()->attach($alumno->grupos->first()->id);

        Anotacion::create(['alumno_id' => $alumno->id, 'profesor_id' => $profesor1->id, 'titulo' => 'T', 'contenido' => 'C']);
        Anotacion::create(['alumno_id' => $alumno->id, 'profesor_id' => $profesor2->id, 'titulo' => 'T', 'contenido' => 'C']);
        Anotacion::create(['alumno_id' => $alumno->id, 'profesor_id' => $profesor2->id, 'titulo' => 'T', 'contenido' => 'C']);

        // profesor1 ve las suyas + las de otros profesores del grupo del alumno
        $result = Anotacion::visiblesPara($profesor1->id)->get();
        $this->assertCount(3, $result);

        // profesor2 solo ve las suyas (no imparte el grupo)
        $result = Anotacion::visiblesPara($profesor2->id)->get();
        $this->assertCount(2, $result);
    }

    public function test_anotacion_persiste_entre_cursos(): void
    {
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Test', 'es_tutor' => false,
        ]);
        $alumno = $this->createAlumnoEnGrupo();
        $anotacion = Anotacion::create([
            'alumno_id' => $alumno->id, 'profesor_id' => $profesor->id,
            'titulo' => 'Test', 'contenido' => 'Test',
        ]);

        $this->assertDatabaseHas('anotaciones', ['id' => $anotacion->id]);
        $this->assertNotNull($anotacion->created_at);
    }

    public function test_anotacion_titulo_max_255(): void
    {
        $admin = $this->createAdmin();
        $alumno = $this->createAlumnoEnGrupo();

        $response = $this->actingAs($admin)->post(route('anotaciones.store'), [
            'alumno_id' => $alumno->id,
            'titulo' => str_repeat('a', 256),
            'contenido' => 'Test',
        ]);

        $response->assertSessionHasErrors('titulo');
    }

    public function test_anotacion_contenido_max_2000(): void
    {
        $admin = $this->createAdmin();
        $alumno = $this->createAlumnoEnGrupo();

        $response = $this->actingAs($admin)->post(route('anotaciones.store'), [
            'alumno_id' => $alumno->id,
            'titulo' => 'Test',
            'contenido' => str_repeat('a', 2001),
        ]);

        $response->assertSessionHasErrors('contenido');
    }
}