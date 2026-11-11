<?php

namespace Tests\Feature;

use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfesorEdicionGruposModulosPerfilTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicContext(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'INF_TEST_DOC'],
            ['nombre' => 'Informática y Comunicaciones Doc', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAW_DOC'],
            ['familia_id' => $familia->id, 'nombre' => 'Desarrollo de Aplicaciones Web Doc', 'grado' => 'superior', 'is_active' => true]
        );

        $asig1 = Asignatura::firstOrCreate(
            ['codigo' => 'DWES_DOC'],
            ['ciclo_id' => $ciclo->id, 'nombre' => 'Desarrollo Web en Entorno Servidor', 'curso' => 2, 'is_active' => true]
        );

        $asig2 = Asignatura::firstOrCreate(
            ['codigo' => 'DWEC_DOC'],
            ['ciclo_id' => $ciclo->id, 'nombre' => 'Desarrollo Web en Entorno Cliente', 'curso' => 2, 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAW Mañana Doc', 'is_active' => true]
        );

        $grupo = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 2],
            ['nombre' => '2º DAW Mañana Doc', 'is_active' => true]
        );

        return compact('curso', 'familia', 'ciclo', 'asig1', 'asig2', 'linea', 'grupo');
    }

    private function createTeacher(string $name = 'Profesor Docente Test', string $email = 'profe.docente@ieshlanz.es'): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $profesor = Profesor::create([
            'user_id' => $user->id,
            'especialidad' => 'Informática y Redes',
            'es_tutor' => false,
            'es_coordinador_dual' => false,
        ]);

        return [$user, $profesor];
    }

    public function test_profesor_can_view_edit_form_of_own_perfil_docente(): void
    {
        $ctx = $this->createAcademicContext();
        [$user, $profesor] = $this->createTeacher();
        $profesor->update(['familia_id' => $ctx['familia']->id]);

        $this->actingAs($user);
        $response = $this->get(route('profesores.edit', $profesor));

        $response->assertOk();
        $response->assertSee('Editar Perfil Docente');
        $response->assertSee('2º DAW Mañana Doc');
        $response->assertSee('Desarrollo Web en Entorno Servidor');
        // El checkbox de coordinador dual no debe mostrarse a un profesor raso
        $response->assertDontSee('name="es_coordinador_dual"', false);
    }

    public function test_profesor_can_update_own_teaching_groups_and_define_modules(): void
    {
        $ctx = $this->createAcademicContext();
        [$user, $profesor] = $this->createTeacher();
        $profesor->update(['familia_id' => $ctx['familia']->id]);

        $this->actingAs($user);
        $response = $this->put(route('profesores.update', $profesor), [
            'name' => 'Profesor Nombre Actualizado',
            'email' => 'profe.docente@ieshlanz.es',
            'especialidad' => 'Sistemas y Desarrollo',
            'familia_id' => $ctx['familia']->id,
            'grupos' => [
                $ctx['grupo']->id => [
                    'activo' => '1',
                    'asignatura_id' => $ctx['asig1']->id,
                ],
            ],
        ]);

        $response->assertRedirect(route('profesores.show', $profesor));
        $response->assertSessionHas('success');

        $profesor->refresh();
        $user->refresh();

        $this->assertEquals('Profesor Nombre Actualizado', $user->name);
        $this->assertEquals('Sistemas y Desarrollo', $profesor->especialidad);

        // Verificar que el grupo y módulo están asignados en el pivote grupo_profesor
        $this->assertDatabaseHas('grupo_profesor', [
            'profesor_id' => $profesor->id,
            'grupo_id' => $ctx['grupo']->id,
            'asignatura_id' => $ctx['asig1']->id,
        ]);

        // Verificar que la relación asignaturas se sincroniza
        $this->assertTrue($profesor->asignaturas->contains('id', $ctx['asig1']->id));
    }

    public function test_profesor_profile_show_displays_edit_button_and_assigned_groups_with_modules(): void
    {
        $ctx = $this->createAcademicContext();
        [$user, $profesor] = $this->createTeacher();
        $profesor->update(['familia_id' => $ctx['familia']->id]);
        $profesor->gruposImpartidos()->attach($ctx['grupo']->id, ['asignatura_id' => $ctx['asig1']->id]);
        $profesor->asignaturas()->sync([$ctx['asig1']->id]);

        $this->actingAs($user);
        $response = $this->get(route('profesores.show', $profesor));

        $response->assertOk();
        $response->assertSee('Editar perfil docente');
        $response->assertSee('Editar grupos y módulos');
        $response->assertSee('Grupos y módulos que imparte');
        $response->assertSee('2º DAW Mañana Doc');
        $response->assertSee('Desarrollo Web en Entorno Servidor');
    }

    public function test_profesor_cannot_edit_other_profesor_profile(): void
    {
        $this->createAcademicContext();
        [$user1, $profesor1] = $this->createTeacher('Profesor Uno', 'uno@ieshlanz.es');
        [$user2, $profesor2] = $this->createTeacher('Profesor Dos', 'dos@ieshlanz.es');

        $this->actingAs($user1);

        // Intentar ver el formulario de edición de otro profesor
        $responseGet = $this->get(route('profesores.edit', $profesor2));
        $responseGet->assertForbidden();

        // Intentar enviar actualización sobre otro profesor
        $responsePut = $this->put(route('profesores.update', $profesor2), [
            'name' => 'Hackeado',
            'email' => 'hack@ieshlanz.es',
        ]);
        $responsePut->assertForbidden();
    }

    public function test_profesor_cannot_self_assign_coordinador_dual_or_group_tutorship(): void
    {
        $ctx = $this->createAcademicContext();
        [$user, $profesor] = $this->createTeacher();

        $this->actingAs($user);

        // El profesor envía maliciosamente es_coordinador_dual y tutor_grupo_id
        $response = $this->put(route('profesores.update', $profesor), [
            'name' => 'Profesor Intento Admin',
            'email' => $user->email,
            'es_coordinador_dual' => 1,
            'tutor_grupo_id' => $ctx['grupo']->id,
            'grupos' => [
                $ctx['grupo']->id => [
                    'activo' => '1',
                    'asignatura_id' => $ctx['asig1']->id,
                ],
            ],
        ]);

        $response->assertRedirect(route('profesores.show', $profesor));

        $profesor->refresh();
        $user->refresh();

        // No debe ser coordinador dual ni tener el rol agregado
        $this->assertFalse((bool) $profesor->es_coordinador_dual);
        $this->assertNotContains(User::ROLE_COORDINADOR_DUAL, $user->roles ?? []);

        // El grupo no debe tener a este profesor como tutor
        $this->assertNotEquals($user->id, $ctx['grupo']->fresh()->tutor_id);
    }

    public function test_profesor_unchecking_group_detaches_it(): void
    {
        $ctx = $this->createAcademicContext();
        [$user, $profesor] = $this->createTeacher();
        $profesor->gruposImpartidos()->attach($ctx['grupo']->id, ['asignatura_id' => $ctx['asig1']->id]);
        $profesor->asignaturas()->sync([$ctx['asig1']->id]);

        $this->assertEquals(1, $profesor->gruposImpartidos()->count());

        $this->actingAs($user);
        // Enviamos el grupo desmarcado (sin 'activo')
        $response = $this->put(route('profesores.update', $profesor), [
            'name' => $user->name,
            'email' => $user->email,
            'grupos' => [
                $ctx['grupo']->id => [
                    'activo' => '0',
                    'asignatura_id' => '',
                ],
            ],
        ]);

        $response->assertRedirect(route('profesores.show', $profesor));
        $profesor->refresh();

        $this->assertEquals(0, $profesor->gruposImpartidos()->count());
        $this->assertEquals(0, $profesor->asignaturas()->count());
    }
}
