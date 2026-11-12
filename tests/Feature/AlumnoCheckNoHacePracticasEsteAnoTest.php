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
use App\Models\Practica;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoCheckNoHacePracticasEsteAnoTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicContext(): array
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'IFC_TEST'],
            ['nombre' => 'Informática Test', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAM_TEST'],
            ['familia_id' => $familia->id, 'nombre' => 'DAM Test', 'grado' => 'superior', 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAM Mañana Test', 'is_active' => true]
        );

        $grupo = Grupo::firstOrCreate(
            ['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 1],
            ['nombre' => '1º DAM Test', 'is_active' => true]
        );

        $profesorUser = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);
        $profesor = Profesor::firstOrCreate(
            ['user_id' => $profesorUser->id],
            ['departamento' => 'Informática', 'is_active' => true]
        );
        $grupo->update(['tutor_id' => $profesorUser->id]);

        $empresa = Empresa::firstOrCreate(
            ['cif' => 'B99999999'],
            ['nombre' => 'Tech Corp Test', 'email' => 'tech@test.es', 'is_active' => true]
        );

        return compact('curso', 'familia', 'ciclo', 'linea', 'grupo', 'profesorUser', 'profesor', 'empresa');
    }

    public function test_profesor_can_mark_and_unmark_no_hace_practicas_este_ano_via_patch_route(): void
    {
        $ctx = $this->createAcademicContext();

        $studentUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $studentUser->id,
            'no_hace_practicas_este_ano' => false,
        ]);
        $alumno->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($ctx['profesorUser']);

        // Marcar como que no hace prácticas este año
        $res = $this->patch(route('alumnos.update-situacion-practicas', $alumno), [
            'no_hace_practicas_este_ano' => '1',
        ]);
        $res->assertSessionHas('success');
        $alumno->refresh();
        $this->assertTrue($alumno->no_hace_practicas_este_ano);
        $this->assertFalse($alumno->puedeHacerPracticas());

        // Desmarcar
        $res2 = $this->patch(route('alumnos.update-situacion-practicas', $alumno), [
            'no_hace_practicas_este_ano' => '0',
        ]);
        $res2->assertSessionHas('success');
        $alumno->refresh();
        $this->assertFalse($alumno->no_hace_practicas_este_ano);
        $this->assertTrue($alumno->puedeHacerPracticas());
    }

    public function test_alumno_cannot_update_situacion_practicas_or_modify_check_in_profile(): void
    {
        $ctx = $this->createAcademicContext();

        $studentUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $studentUser->id,
            'no_hace_practicas_este_ano' => false,
        ]);
        $alumno->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($studentUser);

        // Alumno intenta llamar a la ruta patch de situación de prácticas -> 403
        $res = $this->patch(route('alumnos.update-situacion-practicas', $alumno), [
            'no_hace_practicas_este_ano' => '1',
        ]);
        $res->assertForbidden();

        // Alumno intenta enviar el campo a través del perfil
        $this->patch(route('profile.update'), [
            'name' => $studentUser->name,
            'email' => $studentUser->email,
            'no_hace_practicas_este_ano' => '1',
        ]);

        $alumno->refresh();
        $this->assertFalse($alumno->no_hace_practicas_este_ano);
    }

    public function test_alumno_with_check_marked_cannot_postularse_to_oferta(): void
    {
        $ctx = $this->createAcademicContext();

        $studentUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $studentUser->id,
            'no_hace_practicas_este_ano' => true,
        ]);
        $alumno->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $oferta = OfertaPractica::create([
            'empresa_id' => $ctx['empresa']->id,
            'titulo' => 'Oferta Desarrollo Web',
            'descripcion' => 'Prácticas para DAM/DAW',
            'especialidad_requerida' => 'Desarrollo Web',
            'num_alumnos' => 2,
            'curso_academico_id' => $ctx['curso']->id,
            'estado' => 'activa',
        ]);
        $oferta->grupos()->attach($ctx['grupo']->id);

        $this->actingAs($studentUser);
        $res = $this->post(route('ofertas.postularse', $oferta));
        $res->assertSessionHasErrors(['error' => 'No puedes postularte a ofertas de prácticas este curso.']);
    }

    public function test_alumno_with_check_marked_is_excluded_from_oferta_enviar_candidates_and_enviar_a_alumnos(): void
    {
        $ctx = $this->createAcademicContext();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $studentUserEligible = User::factory()->create([
            'name' => 'Alumno Aprobado PRL',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoEligible = Alumno::create([
            'user_id' => $studentUserEligible->id,
            'no_hace_practicas_este_ano' => false,
        ]);
        $alumnoEligible->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $studentUserBlocked = User::factory()->create([
            'name' => 'Alumno Sin PRL o Menor de 16',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoBlocked = Alumno::create([
            'user_id' => $studentUserBlocked->id,
            'no_hace_practicas_este_ano' => true,
        ]);
        $alumnoBlocked->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $oferta = OfertaPractica::create([
            'empresa_id' => $ctx['empresa']->id,
            'titulo' => 'Oferta Redes y Sistemas',
            'descripcion' => 'Prácticas de soporte',
            'especialidad_requerida' => 'Soporte y Redes',
            'num_alumnos' => 2,
            'curso_academico_id' => $ctx['curso']->id,
            'estado' => 'activa',
        ]);
        $oferta->grupos()->attach($ctx['grupo']->id);

        $this->actingAs($admin);

        // Enviar form muestra al alumno elegible pero NO al bloqueado
        $formRes = $this->get(route('ofertas.enviar-form', $oferta));
        $formRes->assertOk();
        $formRes->assertSee('Alumno Aprobado PRL');
        $formRes->assertDontSee('Alumno Sin PRL o Menor de 16');

        // Intentar enviar al bloqueado falla
        $postRes = $this->post(route('ofertas.enviar', $oferta), [
            'alumno_ids' => [$alumnoBlocked->id],
        ]);
        $postRes->assertSessionHasErrors('alumno_ids');
    }

    public function test_alumno_with_check_marked_is_excluded_from_practica_create_and_rejected_in_store(): void
    {
        $ctx = $this->createAcademicContext();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $studentUserBlocked = User::factory()->create([
            'name' => 'Alumno Bloqueado Practica',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoBlocked = Alumno::create([
            'user_id' => $studentUserBlocked->id,
            'no_hace_practicas_este_ano' => true,
        ]);
        $alumnoBlocked->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($admin);

        // En create no aparece
        $createRes = $this->get(route('practicas.create'));
        $createRes->assertOk();
        $createRes->assertDontSee('Alumno Bloqueado Practica');

        // En store se rechaza explícitamente
        $storeRes = $this->post(route('practicas.store'), [
            'alumno_id' => $alumnoBlocked->id,
            'empresa_id' => $ctx['empresa']->id,
            'curso_academico_id' => $ctx['curso']->id,
            'fecha_inicio' => '2027-03-01',
            'fecha_fin' => '2027-06-15',
            'horas_acumuladas' => 0,
            'convenio_firmado' => true,
        ]);
        $storeRes->assertSessionHasErrors(['alumno_id' => 'El alumno seleccionado no puede realizar prácticas este curso.']);
    }

    public function test_alumno_with_check_marked_is_excluded_from_practicas_grupos_show(): void
    {
        $ctx = $this->createAcademicContext();

        $studentUserBlocked = User::factory()->create([
            'name' => 'Alumno Sin Practicas Grupo',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoBlocked = Alumno::create([
            'user_id' => $studentUserBlocked->id,
            'no_hace_practicas_este_ano' => true,
        ]);
        $alumnoBlocked->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($ctx['profesorUser']);

        $res = $this->get(route('practicas.grupos.show', $ctx['grupo']));
        $res->assertOk();
        $res->assertDontSee('Alumno Sin Practicas Grupo');
    }

    public function test_alumnos_show_and_index_display_warning_and_badges(): void
    {
        $ctx = $this->createAcademicContext();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $studentUser = User::factory()->create([
            'name' => 'Carmen Menor Dieciseis',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create([
            'user_id' => $studentUser->id,
            'no_hace_practicas_este_ano' => true,
        ]);
        $alumno->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($admin);

        // Show muestra la card y texto explicativo
        $showRes = $this->get(route('alumnos.show', $alumno));
        $showRes->assertOk();
        $showRes->assertSee('Situación de Prácticas para este Curso');
        $showRes->assertSee('Este año no hace prácticas');
        $showRes->assertSee('Tiene menos de 16 años en el momento de comenzar las prácticas');
        $showRes->assertSee('No ha superado la Formación en Riesgos Laborales');

        // Index muestra el badge correspondiente
        $indexRes = $this->get(route('alumnos.index'));
        $indexRes->assertOk();
        $indexRes->assertSee('No hace prácticas');
    }

    public function test_admin_can_set_check_in_create_and_edit_forms(): void
    {
        $ctx = $this->createAcademicContext();
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN]]);

        $this->actingAs($admin);

        // Create alumno con el check
        $postRes = $this->post(route('alumnos.store'), [
            'name' => 'Nuevo Alumno Menor',
            'email' => 'nuevo_menor@test.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'grupos_ids' => [$ctx['grupo']->id],
            'no_hace_practicas_este_ano' => '1',
        ]);
        $postRes->assertRedirect(route('alumnos.index'));

        $alumnoCreado = Alumno::whereHas('user', fn($q) => $q->where('email', 'nuevo_menor@test.es'))->first();
        $this->assertNotNull($alumnoCreado);
        $this->assertTrue($alumnoCreado->no_hace_practicas_este_ano);

        // Edit alumno quitando el check
        $putRes = $this->put(route('alumnos.update', $alumnoCreado), [
            'name' => 'Nuevo Alumno Menor Editado',
            'email' => 'nuevo_menor@test.es',
            'grupos_ids' => [$ctx['grupo']->id],
            'no_hace_practicas_este_ano' => '0',
        ]);
        $putRes->assertRedirect(route('alumnos.show', $alumnoCreado));

        $alumnoCreado->refresh();
        $this->assertFalse($alumnoCreado->no_hace_practicas_este_ano);
    }
}
