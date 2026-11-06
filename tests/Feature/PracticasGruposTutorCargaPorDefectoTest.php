<?php

namespace Tests\Feature;

use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Ciclo;
use App\Models\Linea;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\User;
use Tests\TestCase;

class PracticasGruposTutorCargaPorDefectoTest extends TestCase
{
    private function createAcademicContext(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'IFC_TUT_DEF'],
            ['nombre' => 'Informática Test Tutor Defecto', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAW_TUT_DEF'],
            ['familia_id' => $familia->id, 'nombre' => 'DAW Test Tutor Defecto', 'grado' => 'superior', 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAW Mañana Test Tutor Defecto', 'is_active' => true]
        );

        // Usuario profesor tutor
        $userTutor = User::factory()->create([
            'name' => 'Profesor Tutor Grupo Defecto',
            'email' => 'tutor_defecto_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);
        $profesorTutor = Profesor::firstOrCreate(
            ['user_id' => $userTutor->id],
            ['familia_id' => $familia->id, 'especialidad' => 'Informática', 'es_tutor' => true]
        );

        // Usuario profesor no tutor
        $userNoTutor = User::factory()->create([
            'name' => 'Profesor No Tutor Test',
            'email' => 'no_tutor_defecto_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);
        $profesorNoTutor = Profesor::firstOrCreate(
            ['user_id' => $userNoTutor->id],
            ['familia_id' => $familia->id, 'especialidad' => 'Informática', 'es_tutor' => false]
        );

        // Grupo del tutor
        $grupoTutor = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 2,
            'nombre' => '2º DAW - Grupo Tutorado ' . uniqid(),
            'tutor_id' => $userTutor->id,
            'is_active' => true,
        ]);
        $grupoTutor->profesores()->attach($profesorTutor->id);
        $grupoTutor->profesores()->attach($profesorNoTutor->id);

        // Otro grupo donde el no tutor da clase
        $grupoOtro = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '1º DAW - Grupo Sin Tutor Asignado ' . uniqid(),
            'tutor_id' => null,
            'is_active' => true,
        ]);
        $grupoOtro->profesores()->attach($profesorNoTutor->id);

        return compact('curso', 'familia', 'ciclo', 'linea', 'userTutor', 'profesorTutor', 'userNoTutor', 'profesorNoTutor', 'grupoTutor', 'grupoOtro');
    }

    public function test_tutor_accessing_practicas_grupos_redirects_to_tutored_group(): void
    {
        $ctx = $this->createAcademicContext();

        // El tutor accede a /practicas/grupos
        $response = $this->actingAs($ctx['userTutor'])->get(route('practicas.grupos'));

        // Redirige automáticamente al grupo tutorado
        $response->assertRedirect(route('practicas.grupos.show', $ctx['grupoTutor']));

        // Seguir redirección y comprobar que carga directamente la vista del grupo
        $followResponse = $this->actingAs($ctx['userTutor'])->get(route('practicas.grupos.show', $ctx['grupoTutor']));
        $followResponse->assertStatus(200);
        $followResponse->assertSee($ctx['grupoTutor']->nombre);
        $followResponse->assertSee('(Tutoría)');
    }

    public function test_non_tutor_accessing_practicas_grupos_shows_index_selector(): void
    {
        $ctx = $this->createAcademicContext();

        // El profesor no tutor accede a /practicas/grupos
        $response = $this->actingAs($ctx['userNoTutor'])->get(route('practicas.grupos'));

        // No redirige, muestra la vista de selección
        $response->assertStatus(200);
        $response->assertSee('Prácticas por Grupo');
        $response->assertSee('-- Elegir grupo --');
        $response->assertSee($ctx['grupoOtro']->nombre);
    }

    public function test_admin_who_is_tutor_redirects_to_tutored_group(): void
    {
        $ctx = $this->createAcademicContext();

        $admin = User::factory()->create([
            'name' => 'Admin Y Tutor Test',
            'roles' => [User::ROLE_ADMIN, User::ROLE_PROFESOR],
            'is_active' => true,
        ]);
        $ctx['grupoTutor']->update(['tutor_id' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('practicas.grupos'));

        $response->assertRedirect(route('practicas.grupos.show', $ctx['grupoTutor']));
    }

    public function test_admin_who_is_not_tutor_shows_index_selector(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Solo',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('practicas.grupos'));

        $response->assertStatus(200);
        $response->assertSee('Prácticas por Grupo');
        $response->assertSee('-- Elegir grupo --');
    }

    public function test_accessing_with_explicit_grupo_param_redirects_to_that_group(): void
    {
        $ctx = $this->createAcademicContext();

        $response = $this->actingAs($ctx['userNoTutor'])->get(route('practicas.grupos', ['grupo' => $ctx['grupoOtro']->id]));

        $response->assertRedirect(route('practicas.grupos.show', $ctx['grupoOtro']));
    }

    public function test_show_view_has_group_dropdown_with_tutoria_indicator(): void
    {
        $ctx = $this->createAcademicContext();

        $response = $this->actingAs($ctx['userTutor'])->get(route('practicas.grupos.show', $ctx['grupoTutor']));

        $response->assertStatus(200);
        $response->assertSee('(Tutoría)');
        $response->assertSee($ctx['grupoTutor']->id);
    }
}
