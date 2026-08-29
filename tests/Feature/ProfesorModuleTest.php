<?php

namespace Tests\Feature;

use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfesorModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_profesores_index(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'especialidad' => 'Desarrollo Web',
            'es_tutor' => true,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('profesores.index'));
        $response->assertOk();
        $response->assertSee($profesor->user->name);
    }

    public function test_admin_can_create_profesor(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $familia = \App\Models\Familia::firstOrCreate(['codigo' => 'INFORMATICA'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $asignatura = Asignatura::create(['ciclo_id' => $ciclo->id, 'codigo' => 'DAWES', 'nombre' => 'Desarrollo Servidor', 'is_active' => true]);

        $this->actingAs($admin);
        $response = $this->post(route('profesores.store'), [
            'name' => 'Profesor Test',
            'email' => 'profesor@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'especialidad' => 'Desarrollo Web',
            'es_tutor' => true,
            'asignatura_ids' => [$asignatura->id],
        ]);

        $response->assertRedirect(route('profesores.index'));
        $this->assertDatabaseHas('users', ['email' => 'profesor@test.com', 'consent_rgpd' => true]);
        $this->assertDatabaseHas('profesores', ['user_id' => User::where('email', 'profesor@test.com')->first()->id]);
    }

    public function test_admin_can_edit_profesor(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $user = User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);
        $profesor = Profesor::create([
            'user_id' => $user->id,
            'especialidad' => 'Antigua',
            'es_tutor' => false,
        ]);

        $this->actingAs($admin);
        $response = $this->put(route('profesores.update', $profesor), [
            'name' => 'Profesor Actualizado',
            'email' => 'profesor.updated@test.com',
            'especialidad' => 'Nueva Especialidad',
            'es_tutor' => true,
        ]);

        $response->assertRedirect();
        $user->refresh();
        $profesor->refresh();
        $this->assertEquals('Profesor Actualizado', $user->name);
        $this->assertEquals('Nueva Especialidad', $profesor->especialidad);
        $this->assertTrue($profesor->es_tutor);
    }

    public function test_admin_can_deactivate_profesor(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('profesores.deactivate', $profesor));
        $response->assertRedirect();

        $profesor->refresh();
        $this->assertFalse($profesor->user->is_active);
    }

    public function test_admin_can_delete_profesor(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);

        $this->actingAs($admin);
        $response = $this->delete(route('profesores.destroy', $profesor));
        $response->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $profesor->user_id]);
        $this->assertDatabaseMissing('profesores', ['id' => $profesor->id]);
    }

    public function test_profesor_can_view_own_profile(): void
    {
        $profesor = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);

        $this->actingAs($profesor->user);
        $response = $this->get(route('profesores.show', $profesor));
        $response->assertOk();
    }

    public function test_profesor_can_view_other_profesores(): void
    {
        $profesor1 = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);
        $profesor2 = Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
        ]);

        $this->actingAs($profesor1->user);
        $response = $this->get(route('profesores.show', $profesor2));
        $response->assertOk();
    }

    public function test_non_admin_cannot_create_profesor(): void
    {
        $profesor = User::factory()->create(['roles' => [User::ROLE_PROFESOR]]);

        $this->actingAs($profesor);
        $response = $this->get(route('profesores.create'));
        $response->assertForbidden();
    }

    public function test_profesor_creation_sets_rgpd_consent(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $this->actingAs($admin);
        $response = $this->post(route('profesores.store'), [
            'name' => 'Juan Profesor',
            'email' => 'juan.prof@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'especialidad' => 'Desarrollo',
        ]);

        $user = User::where('email', 'juan.prof@test.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->consent_rgpd);
        $this->assertNotNull($user->consent_rgpd_at);
    }

    public function test_profesor_can_be_assigned_to_asignaturas(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $familia = \App\Models\Familia::firstOrCreate(['codigo' => 'INFORMATICA'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::create(['familia_id' => $familia->id, 'codigo' => 'DAW', 'nombre' => 'DAW', 'grado' => 'superior', 'is_active' => true]);
        $asig1 = Asignatura::create(['ciclo_id' => $ciclo->id, 'codigo' => 'DAWES', 'nombre' => 'Desarrollo Servidor', 'is_active' => true]);
        $asig2 = Asignatura::create(['ciclo_id' => $ciclo->id, 'codigo' => 'DAWEC', 'nombre' => 'Desarrollo Cliente', 'is_active' => true]);

        $this->actingAs($admin);
        $this->post(route('profesores.store'), [
            'name' => 'Profesor Asignaturas',
            'email' => 'prof.asig@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'asignatura_ids' => [$asig1->id, $asig2->id],
        ]);

        $profesor = Profesor::whereHas('user', fn($q) => $q->where('email', 'prof.asig@test.com'))->first();
        $this->assertEquals(2, $profesor->asignaturas()->count());
    }
}