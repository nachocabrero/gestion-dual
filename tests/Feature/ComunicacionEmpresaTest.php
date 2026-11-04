<?php

namespace Tests\Feature;

use App\Models\ComunicacionEmpresa;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Profesor;
use App\Models\User;
use Tests\TestCase;

class ComunicacionEmpresaTest extends TestCase
{
    private function createAcademicData(): array
    {
        $cursoActual = CursoAcademico::active()->orderBy('fecha_inicio', 'desc')->first();
        if (!$cursoActual) {
            $cursoActual = CursoAcademico::create([
                'nombre' => '2026/2027',
                'fecha_inicio' => '2026-09-15',
                'fecha_fin' => '2027-06-25',
                'is_active' => true,
            ]);
        }

        $familia = Familia::create([
            'nombre' => 'Informática y Comunicaciones ' . uniqid(),
            'codigo' => 'IFC' . substr(uniqid(), 0, 4),
            'is_active' => true,
        ]);

        // Profesor Responsable
        $userProf1 = User::create([
            'name' => 'Profesor Responsable',
            'email' => 'prof.resp@ieshlanz.es',
            'password' => bcrypt('password'),
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
        $profesor1 = Profesor::create([
            'user_id' => $userProf1->id,
            'familia_id' => $familia->id,
            'especialidad' => 'Informática',
        ]);

        // Profesor Ajeno
        $userProf2 = User::create([
            'name' => 'Profesor Ajeno',
            'email' => 'prof.ajeno@ieshlanz.es',
            'password' => bcrypt('password'),
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
        $profesor2 = Profesor::create([
            'user_id' => $userProf2->id,
            'familia_id' => $familia->id,
            'especialidad' => 'Informática',
        ]);

        // Empresa con profesor1 asignado para seguimiento
        $empresa = Empresa::create([
            'profesor_id' => $profesor1->id,
            'nombre' => 'Tech Solutions SL ' . uniqid(),
            'cif' => 'B' . rand(10000000, 99999999),
            'email' => 'contacto@techsolutions.com',
            'is_active' => true,
        ]);

        return [$cursoActual, $userProf1, $profesor1, $userProf2, $profesor2, $empresa];
    }

    public function test_profesor_responsable_can_create_comunicacion(): void
    {
        [$curso, $userProf1, $profesor1, $userProf2, $profesor2, $empresa] = $this->createAcademicData();

        $response = $this->actingAs($userProf1)->post(route('empresas.comunicaciones.store', $empresa), [
            'tipo' => 'telefono',
            'motivo' => 'Llamada para coordinar plazas de FCT en DAW',
            'respuesta' => 'Nos indican que dispondrán de 2 vacantes a partir de marzo.',
        ]);

        $response->assertRedirect(route('empresas.show', $empresa));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('comunicaciones_empresa', [
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor1->id,
            'user_id' => $userProf1->id,
            'curso_academico_id' => $curso->id,
            'tipo' => 'telefono',
            'motivo' => 'Llamada para coordinar plazas de FCT en DAW',
            'respuesta' => 'Nos indican que dispondrán de 2 vacantes a partir de marzo.',
        ]);

        $comunicacion = ComunicacionEmpresa::first();
        $this->assertNotNull($comunicacion->fecha);
    }

    public function test_can_create_comunicacion_with_different_channels(): void
    {
        [$curso, $userProf1, $profesor1, , , $empresa] = $this->createAcademicData();

        $this->actingAs($userProf1)->post(route('empresas.comunicaciones.store', $empresa), [
            'tipo' => 'email',
            'motivo' => 'Envío de propuesta de convenio por correo',
            'respuesta' => 'Recibido y revisando con asesoría jurídica.',
        ])->assertRedirect(route('empresas.show', $empresa));

        $this->actingAs($userProf1)->post(route('empresas.comunicaciones.store', $empresa), [
            'tipo' => 'presencial',
            'motivo' => 'Visita a instalaciones de la empresa',
            'respuesta' => 'Reunión con el tutor laboral favorable.',
        ])->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseCount('comunicaciones_empresa', 2);
    }

    public function test_profesor_no_responsable_cannot_create_comunicacion(): void
    {
        [, , , $userProf2, , $empresa] = $this->createAcademicData();

        $response = $this->actingAs($userProf2)->post(route('empresas.comunicaciones.store', $empresa), [
            'tipo' => 'telefono',
            'motivo' => 'Intento de registro no autorizado',
            'respuesta' => 'Ninguna',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('comunicaciones_empresa', 0);
    }

    public function test_admin_and_coordinador_dual_can_create_comunicacion(): void
    {
        [$curso, , , , , $empresa] = $this->createAcademicData();

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin.com@ieshlanz.es',
            'password' => bcrypt('password'),
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $coord = User::create([
            'name' => 'Coordinador Dual',
            'email' => 'coord.com@ieshlanz.es',
            'password' => bcrypt('password'),
            'roles' => [User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('empresas.comunicaciones.store', $empresa), [
            'tipo' => 'telefono',
            'motivo' => 'Comunicación creada por admin',
            'respuesta' => 'Ok',
        ])->assertRedirect(route('empresas.show', $empresa));

        $this->actingAs($coord)->post(route('empresas.comunicaciones.store', $empresa), [
            'tipo' => 'email',
            'motivo' => 'Comunicación creada por coordinador dual',
            'respuesta' => 'Ok coord',
        ])->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseCount('comunicaciones_empresa', 2);
    }

    public function test_profesor_responsable_can_update_comunicacion(): void
    {
        [$curso, $userProf1, $profesor1, $userProf2, , $empresa] = $this->createAcademicData();

        $comunicacion = ComunicacionEmpresa::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor1->id,
            'user_id' => $userProf1->id,
            'curso_academico_id' => $curso->id,
            'tipo' => 'telefono',
            'motivo' => 'Motivo inicial',
            'respuesta' => 'Respuesta inicial',
            'fecha' => now()->subDay(),
        ]);

        $fechaOriginal = $comunicacion->fecha;

        $response = $this->actingAs($userProf1)->put(route('empresas.comunicaciones.update', [$empresa, $comunicacion]), [
            'tipo' => 'presencial',
            'motivo' => 'Motivo actualizado tras reunión',
            'respuesta' => 'Respuesta corregida y detallada',
        ]);

        $response->assertRedirect(route('empresas.show', $empresa));
        $response->assertSessionHas('success');

        $comunicacion->refresh();
        $this->assertEquals('presencial', $comunicacion->tipo);
        $this->assertEquals('Motivo actualizado tras reunión', $comunicacion->motivo);
        $this->assertEquals('Respuesta corregida y detallada', $comunicacion->respuesta);
        // La fecha original de registro se mantiene
        $this->assertEquals($fechaOriginal->toDateTimeString(), $comunicacion->fecha->toDateTimeString());
    }

    public function test_profesor_no_responsable_cannot_update_or_delete_comunicacion(): void
    {
        [$curso, $userProf1, $profesor1, $userProf2, , $empresa] = $this->createAcademicData();

        $comunicacion = ComunicacionEmpresa::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor1->id,
            'user_id' => $userProf1->id,
            'curso_academico_id' => $curso->id,
            'tipo' => 'telefono',
            'motivo' => 'Motivo original',
            'respuesta' => 'Respuesta original',
            'fecha' => now(),
        ]);

        // Intento de edición por profesor no asignado
        $respUpdate = $this->actingAs($userProf2)->put(route('empresas.comunicaciones.update', [$empresa, $comunicacion]), [
            'tipo' => 'email',
            'motivo' => 'Modificación prohibida',
        ]);
        $respUpdate->assertStatus(403);

        // Intento de eliminación por profesor no asignado
        $respDelete = $this->actingAs($userProf2)->delete(route('empresas.comunicaciones.destroy', [$empresa, $comunicacion]));
        $respDelete->assertStatus(403);

        $this->assertDatabaseHas('comunicaciones_empresa', ['id' => $comunicacion->id]);
    }

    public function test_profesor_responsable_can_delete_comunicacion(): void
    {
        [$curso, $userProf1, $profesor1, , , $empresa] = $this->createAcademicData();

        $comunicacion = ComunicacionEmpresa::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor1->id,
            'user_id' => $userProf1->id,
            'curso_academico_id' => $curso->id,
            'tipo' => 'email',
            'motivo' => 'A borrar',
            'respuesta' => 'Pronto eliminada',
            'fecha' => now(),
        ]);

        $response = $this->actingAs($userProf1)->delete(route('empresas.comunicaciones.destroy', [$empresa, $comunicacion]));

        $response->assertRedirect(route('empresas.show', $empresa));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('comunicaciones_empresa', ['id' => $comunicacion->id]);
    }

    public function test_alumno_and_empresa_cannot_create_or_view_comunicaciones(): void
    {
        [$curso, , , , , $empresa] = $this->createAcademicData();

        $alumnoUser = User::create([
            'name' => 'Alumno Test',
            'email' => 'alumno.test@ieshlanz.es',
            'password' => bcrypt('password'),
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $empresaUser = User::create([
            'name' => 'Empresa User',
            'email' => 'empresa.user@test.com',
            'password' => bcrypt('password'),
            'roles' => [User::ROLE_EMPRESA],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        // Alumno no puede ver ni crear
        $this->actingAs($alumnoUser)->get(route('empresas.show', $empresa))->assertStatus(403);
        $this->actingAs($alumnoUser)->post(route('empresas.comunicaciones.store', $empresa), [
            'tipo' => 'telefono',
            'motivo' => 'Intento alumno',
        ])->assertStatus(403);

        // Empresa no puede ver ni crear
        $this->actingAs($empresaUser)->get(route('empresas.show', $empresa))->assertStatus(403);
        $this->actingAs($empresaUser)->post(route('empresas.comunicaciones.store', $empresa), [
            'tipo' => 'telefono',
            'motivo' => 'Intento empresa',
        ])->assertStatus(403);
    }

    public function test_all_profesores_can_view_comunicaciones_in_empresa_show(): void
    {
        [$curso, $userProf1, $profesor1, $userProf2, , $empresa] = $this->createAcademicData();

        ComunicacionEmpresa::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor1->id,
            'user_id' => $userProf1->id,
            'curso_academico_id' => $curso->id,
            'tipo' => 'telefono',
            'motivo' => 'Llamada telefónica visible para todo el profesorado',
            'respuesta' => 'Confirmaron disponibilidad de plazas',
            'fecha' => now(),
        ]);

        // El profesor ajeno debe poder ver la ficha y las comunicaciones
        $response = $this->actingAs($userProf2)->get(route('empresas.show', $empresa));

        $response->assertStatus(200);
        $response->assertSee('Registro de Comunicaciones');
        $response->assertSee('Llamada telefónica visible para todo el profesorado');
        $response->assertSee('Confirmaron disponibilidad de plazas');
        $response->assertSee('Profesor Responsable');
        // No debe mostrar botón para añadir comunicación
        $response->assertDontSee('Añadir Comunicación');
    }

    public function test_empresa_show_separates_current_course_and_past_courses(): void
    {
        [$cursoActual, $userProf1, $profesor1, , , $empresa] = $this->createAcademicData();

        $cursoPasado = CursoAcademico::create([
            'nombre' => '2025/2026',
            'fecha_inicio' => '2025-09-15',
            'fecha_fin' => '2026-06-25',
            'is_active' => false,
        ]);

        $comActual = ComunicacionEmpresa::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor1->id,
            'user_id' => $userProf1->id,
            'curso_academico_id' => $cursoActual->id,
            'tipo' => 'telefono',
            'motivo' => 'Contacto curso actual',
            'fecha' => now(),
        ]);

        $comPasada = ComunicacionEmpresa::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor1->id,
            'user_id' => $userProf1->id,
            'curso_academico_id' => $cursoPasado->id,
            'tipo' => 'email',
            'motivo' => 'Contacto histórico del curso pasado',
            'fecha' => now()->subYear(),
        ]);

        $response = $this->actingAs($userProf1)->get(route('empresas.show', $empresa));

        $response->assertStatus(200);
        $response->assertViewHas('comunicacionesActual', function ($collection) use ($comActual) {
            return $collection->contains('id', $comActual->id);
        });
        $response->assertViewHas('comunicacionesPasadas', function ($grouped) use ($comPasada) {
            return $grouped->has('2025/2026') && $grouped->get('2025/2026')->contains('id', $comPasada->id);
        });

        // La vista muestra tanto el curso actual como el curso anterior colapsado
        $response->assertSee('Contacto curso actual');
        $response->assertSee('Comunicaciones de Cursos Anteriores');
        $response->assertSee('Curso 2025/2026');
        $response->assertSee('Contacto histórico del curso pasado');
    }

    public function test_empresas_index_does_not_contain_cif_column(): void
    {
        [, $userProf1, , , , ] = $this->createAcademicData();

        $response = $this->actingAs($userProf1)->get(route('empresas.index'));

        $response->assertStatus(200);
        $response->assertDontSee('<th>CIF</th>', false);
    }

    public function test_empresas_index_contact_column_has_mailto_and_tel_links(): void
    {
        [, $userProf1, , , , $empresa] = $this->createAcademicData();

        $empresa->update([
            'email' => 'directo@empresa.com',
            'telefono' => '654987321',
        ]);

        $response = $this->actingAs($userProf1)->get(route('empresas.index'));

        $response->assertStatus(200);
        $response->assertSee('href="mailto:directo@empresa.com"', false);
        $response->assertSee('href="tel:654987321"', false);
    }

    public function test_empresas_index_seguimiento_column_displays_last_communication_date(): void
    {
        [$curso, $userProf1, $profesor1, , , $empresa] = $this->createAcademicData();

        // Sin comunicaciones
        $response = $this->actingAs($userProf1)->get(route('empresas.index'));
        $response->assertStatus(200);
        $response->assertSee('Sin comunicaciones');

        // Con comunicación
        ComunicacionEmpresa::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor1->id,
            'user_id' => $userProf1->id,
            'curso_academico_id' => $curso->id,
            'tipo' => 'telefono',
            'motivo' => 'Contacto de prueba',
            'fecha' => '2026-11-04 10:30:00',
        ]);

        $responseConCom = $this->actingAs($userProf1)->get(route('empresas.index'));
        $responseConCom->assertStatus(200);
        $responseConCom->assertSee('Última com:');
        $responseConCom->assertSee('04/11/2026');
    }

    public function test_empresas_index_shows_add_communication_button_only_for_assigned_profesor(): void
    {
        [, $userProf1, $profesor1, $userProf2, $profesor2, $empresa1] = $this->createAcademicData();

        $empresa2 = Empresa::create([
            'profesor_id' => $profesor2->id,
            'nombre' => 'Segunda Empresa SL',
            'cif' => 'B' . rand(10000000, 99999999),
            'email' => 'info@segunda.com',
            'is_active' => true,
        ]);

        // Profesor 1 solo debe poder ver el botón de añadir comunicación en empresa1, no en empresa2
        $response1 = $this->actingAs($userProf1)->get(route('empresas.index'));
        $response1->assertStatus(200);
        $response1->assertSee('abrirNuevaCom(' . $empresa1->id . ',', false);
        $response1->assertDontSee('abrirNuevaCom(' . $empresa2->id . ',', false);

        // Profesor 2 solo debe poder ver el botón en empresa2, no en empresa1
        $response2 = $this->actingAs($userProf2)->get(route('empresas.index'));
        $response2->assertStatus(200);
        $response2->assertSee('abrirNuevaCom(' . $empresa2->id . ',', false);
        $response2->assertDontSee('abrirNuevaCom(' . $empresa1->id . ',', false);
    }

    public function test_profesor_can_create_communication_from_index_with_redirect_back(): void
    {
        [, $userProf1, $profesor1, , , $empresa] = $this->createAcademicData();

        $response = $this->actingAs($userProf1)
            ->from(route('empresas.index'))
            ->post(route('empresas.comunicaciones.store', $empresa), [
                'tipo' => 'telefono',
                'motivo' => 'Llamada desde el listado de empresas',
                'respuesta' => 'Respondieron inmediatamente',
            ]);

        $response->assertRedirect(route('empresas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('comunicaciones_empresa', [
            'empresa_id' => $empresa->id,
            'motivo' => 'Llamada desde el listado de empresas',
        ]);
    }
}
