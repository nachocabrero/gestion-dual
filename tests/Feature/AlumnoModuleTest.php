<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Familia;
use App\Models\Ciclo;
use App\Models\Linea;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_alumnos_index(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $familia = Familia::firstOrCreate(['codigo' => 'INFORMATICA'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['familia_id' => $familia->id, 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);
        $grupo = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'is_active' => true]);

        $alumno = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'linkedin_url' => 'https://linkedin.com/in/test',
            'telefono' => '600123456',
        ]);
        $alumno->grupos()->attach($grupo->id);

        $this->actingAs($admin);
        $response = $this->get(route('alumnos.index'));
        $response->assertOk();
        $response->assertSee($alumno->user->name);
    }

    public function test_admin_can_create_alumno(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $familia = Familia::firstOrCreate(['codigo' => 'INFORMATICA'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['familia_id' => $familia->id, 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);
        $grupo = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'is_active' => true]);

        $this->actingAs($admin);
        $response = $this->post(route('alumnos.store'), [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'grupos_ids' => [$grupo->id],
            'linkedin_url' => 'https://linkedin.com/in/juan',
            'telefono' => '600123456',
            'ciclo_ids' => [$ciclo->id],
        ]);

        $response->assertRedirect(route('alumnos.index'));
        $this->assertDatabaseHas('users', ['email' => 'juan@test.com', 'consent_rgpd' => true]);
        $this->assertDatabaseHas('alumnos', ['user_id' => User::where('email', 'juan@test.com')->first()->id]);
    }

    public function test_admin_can_edit_alumno(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $familia = Familia::firstOrCreate(['codigo' => 'INFORMATICA'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['familia_id' => $familia->id, 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);

        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);
        $alumno = Alumno::create([
            'user_id' => $user->id,
            'linkedin_url' => 'https://linkedin.com/in/old',
            'telefono' => '600111111',
        ]);

        $this->actingAs($admin);
        $response = $this->put(route('alumnos.update', $alumno), [
            'name' => 'Juan Pérez Actualizado',
            'email' => 'juan.updated@test.com',
            'linkedin_url' => 'https://linkedin.com/in/new',
            'telefono' => '600222222',
            'ciclo_ids' => [$ciclo->id],
        ]);

        $response->assertRedirect();
        $user->refresh();
        $alumno->refresh();
        $this->assertEquals('Juan Pérez Actualizado', $user->name);
        $this->assertEquals('https://linkedin.com/in/new', $alumno->linkedin_url);
    }

    public function test_admin_can_deactivate_alumno(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $alumno = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('alumnos.deactivate', $alumno));
        $response->assertRedirect();

        $alumno->refresh();
        $this->assertFalse($alumno->user->is_active);
    }

    public function test_admin_can_reactivate_alumno(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $alumno = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now(), 'is_active' => false])->id,
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('alumnos.reactivate', $alumno));
        $response->assertRedirect();

        $alumno->refresh();
        $this->assertTrue($alumno->user->is_active);
    }

    public function test_admin_can_delete_alumno(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $alumno = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);

        $this->actingAs($admin);
        $response = $this->delete(route('alumnos.destroy', $alumno));
        $response->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $alumno->user_id]);
        $this->assertDatabaseMissing('alumnos', ['id' => $alumno->id]);
    }

    public function test_profesor_can_only_see_own_group_alumnos(): void
    {
        $profesor = User::factory()->create(['roles' => [User::ROLE_PROFESOR]]);

        $familia = Familia::firstOrCreate(['codigo' => 'INFORMATICA'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['familia_id' => $familia->id, 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);

        $grupo1 = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'tutor_id' => $profesor->id, 'is_active' => true]);
        $grupo2 = Grupo::create(['linea_id' => $linea->id, 'numero' => 2, 'nombre' => '2º DAW-Manana', 'is_active' => true]);

        $alumno1 = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);
        $alumno1->grupos()->attach($grupo1->id);
        
        $alumno2 = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);
        $alumno2->grupos()->attach($grupo2->id);

        $this->actingAs($profesor);
        $response = $this->get(route('alumnos.index'));
        $response->assertOk();
        $response->assertSee($alumno1->user->name);
        $response->assertDontSee($alumno2->user->name);
    }

    public function test_alumno_can_only_see_own_profile(): void
    {
        $alumno1 = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);
        $alumno2 = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        $alumnoModel1 = Alumno::create(['user_id' => $alumno1->id]);
        $alumnoModel2 = Alumno::create(['user_id' => $alumno2->id]);

        $this->actingAs($alumno1);
        $response = $this->get(route('alumnos.show', $alumnoModel1));
        $response->assertOk();

        $response = $this->get(route('alumnos.show', $alumnoModel2));
        $response->assertForbidden();
    }

    public function test_profesor_can_view_alumno_profile_and_info(): void
    {
        $profesor = User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        $alumnoUser = User::factory()->create([
            'name' => 'Estudiante Prueba',
            'email' => 'estudiante@test.com',
            'roles' => [User::ROLE_ALUMNO],
            'cv_url' => 'cvs/prueba.pdf',
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $alumno = Alumno::create([
            'user_id' => $alumnoUser->id,
            'telefono' => '612345678',
            'domicilio' => 'Calle Mayor 1',
        ]);

        $this->actingAs($profesor);
        $response = $this->get(route('alumnos.show', $alumno));

        $response->assertOk();
        $response->assertSee('Perfil de Alumno');
        $response->assertSee('Estudiante Prueba');
        $response->assertSee('estudiante@test.com');
        $response->assertSee('612345678');
        $response->assertSee('Calle Mayor 1');
        $response->assertSee('Descargar CV');
        $response->assertDontSee(route('alumnos.edit', $alumno));
    }

    public function test_alumnos_index_shows_cv_download_button_when_cv_is_present(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        $alumnoConCv = Alumno::create([
            'user_id' => User::factory()->create([
                'name' => 'Alumno Con CV',
                'roles' => [User::ROLE_ALUMNO],
                'cv_url' => 'cvs/alumno_cv.pdf',
                'consent_rgpd' => true,
                'consent_rgpd_at' => now(),
            ])->id,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('alumnos.index', ['search' => 'Alumno Con CV']));

        $response->assertOk();
        $response->assertSee('Alumno Con CV');
        $response->assertSee('cvs/alumno_cv.pdf');
        $response->assertSee('Descargar CV');
    }

    public function test_non_admin_cannot_create_alumno(): void
    {
        $profesor = User::factory()->create(['roles' => [User::ROLE_PROFESOR]]);

        $this->actingAs($profesor);
        $response = $this->get(route('alumnos.create'));
        $response->assertForbidden();
    }

    public function test_alumno_creation_sets_rgpd_consent(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $familia = Familia::firstOrCreate(['codigo' => 'INFORMATICA'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['familia_id' => $familia->id, 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);
        $grupo = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'is_active' => true]);

        $this->actingAs($admin);
        $response = $this->post(route('alumnos.store'), [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'grupos_ids' => [$grupo->id],
            'linkedin_url' => 'https://linkedin.com/in/juan',
            'telefono' => '600123456',
            'ciclo_ids' => [$ciclo->id],
        ]);

        // El usuario se crea con consent_rgpd = true automáticamente
        $user = User::where('email', 'juan@test.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->consent_rgpd);
        $this->assertNotNull($user->consent_rgpd_at);
    }

    public function test_coordinador_dual_can_view_all_alumnos_and_access_files(): void
    {
        // Coordinador Dual (con rol en roles o con es_coordinador_dual en profesor)
        $coordUser = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR, User::ROLE_COORDINADOR_DUAL],
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
        $profesor = \App\Models\Profesor::create([
            'user_id' => $coordUser->id,
            'es_coordinador_dual' => true,
        ]);

        $this->assertTrue($coordUser->hasRole(User::ROLE_COORDINADOR_DUAL));

        // Estructura y dos grupos diferentes
        $familia = Familia::firstOrCreate(['codigo' => 'INFORMATICA'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['familia_id' => $familia->id, 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);
        $curso = \App\Models\CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-25',
            'is_active' => true,
        ]);

        $grupoOtro = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º DAW Sin Profesor Asignado',
            'is_active' => true,
        ]);

        // Alumno en un grupo ajeno con CV y proyecto con imágenes
        $alumnoUser = User::factory()->create([
            'name' => 'Alumno Dual Test',
            'roles' => [User::ROLE_ALUMNO],
            'cv_url' => 'cvs/alumno_dual_cv.pdf',
            'avatar_url' => 'avatars/avatar_test.webp',
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
        $alumno = Alumno::create(['user_id' => $alumnoUser->id]);
        $alumno->grupos()->attach($grupoOtro->id, ['curso_academico_id' => $curso->id]);

        $proyecto = \App\Models\Proyecto::create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $ciclo->id,
            'curso_academico_id' => $curso->id,
            'grupo_id' => $grupoOtro->id,
            'titulo' => 'Proyecto Innovador Dual',
            'descripcion' => 'Descripción detallada del proyecto para FCT y Dual.',
            'enlace_repositorio' => 'https://github.com/alumno/proyecto',
        ]);
        \App\Models\ProyectoImagen::create([
            'proyecto_id' => $proyecto->id,
            'url' => 'proyectos/captura_demo.webp',
        ]);

        $this->actingAs($coordUser);

        // 1. Puede ver a todos los alumnos en el listado general
        $indexResponse = $this->get(route('alumnos.index', ['search' => 'Alumno Dual Test']));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Alumno Dual Test');
        $indexResponse->assertSee('cvs/alumno_dual_cv.pdf');
        $indexResponse->assertSee('Nuevo Alumno');

        // 2. Puede ver el perfil completo con archivos subidos y proyectos
        $showResponse = $this->get(route('alumnos.show', $alumno));
        $showResponse->assertOk();
        $showResponse->assertSee('Alumno Dual Test');
        $showResponse->assertSee('cvs/alumno_dual_cv.pdf');
        $showResponse->assertSee('Proyecto Innovador Dual');
        $showResponse->assertSee('proyectos/captura_demo.webp');

        // 3. Puede acceder a creación y edición de alumnos
        $createResponse = $this->get(route('alumnos.create'));
        $createResponse->assertOk();

        $editResponse = $this->get(route('alumnos.edit', $alumno));
        $editResponse->assertOk();

        // 4. Puede desactivar al alumno
        $deactivateResponse = $this->post(route('alumnos.deactivate', $alumno));
        $deactivateResponse->assertRedirect();
        $alumno->refresh();
        $this->assertFalse($alumno->user->is_active);

        // 5. Puede ver todos los proyectos en el módulo de proyectos
        $proyectosResponse = $this->get(route('proyectos.index'));
        $proyectosResponse->assertOk();
        $proyectosResponse->assertSee('Proyecto Innovador Dual');
    }
}