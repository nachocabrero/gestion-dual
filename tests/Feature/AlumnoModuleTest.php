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

        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);
        $grupo = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'is_active' => true]);

        $alumno = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'grupo_id' => $grupo->id,
            'linkedin_url' => 'https://linkedin.com/in/test',
            'telefono' => '600123456',
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('alumnos.index'));
        $response->assertOk();
        $response->assertSee($alumno->user->name);
    }

    public function test_admin_can_create_alumno(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);
        $grupo = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'is_active' => true]);

        $this->actingAs($admin);
        $response = $this->post(route('alumnos.store'), [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'grupo_id' => $grupo->id,
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

        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);

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

        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);

        $grupo1 = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'tutor_id' => $profesor->id, 'is_active' => true]);
        $grupo2 = Grupo::create(['linea_id' => $linea->id, 'numero' => 2, 'nombre' => '2º DAW-Manana', 'is_active' => true]);

        $alumno1 = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'grupo_id' => $grupo1->id,
        ]);
        $alumno2 = Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'grupo_id' => $grupo2->id,
        ]);

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

        $familia = Familia::create(['codigo' => 'INFORMATICA', 'nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'DAW Mañana', 'turno' => 'manana', 'is_active' => true]);
        $grupo = Grupo::create(['linea_id' => $linea->id, 'numero' => 1, 'nombre' => '1º DAW-Manana', 'is_active' => true]);

        $this->actingAs($admin);
        $response = $this->post(route('alumnos.store'), [
            'name' => 'Juan Pérez',
            'email' => 'juan@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'grupo_id' => $grupo->id,
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
}