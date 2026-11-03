<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\OfertaPractica;
use App\Models\Practica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Tests\TestCase;

class DesasignarAlumnoOfertaTest extends TestCase
{
    private function createProfesor(): User
    {
        return User::factory()->create([
            'name' => 'Profesor Prueba',
            'email' => 'profesor_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createAlumno(): array
    {
        $user = User::factory()->create([
            'name' => 'Alumno Prueba ' . uniqid(),
            'email' => 'alumno_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $alumno = Alumno::factory()->create([
            'user_id' => $user->id,
        ]);

        return [$user, $alumno];
    }

    public function test_profesor_can_desasignar_alumno_when_oferta_is_pendiente(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'pendiente',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $response = $this->actingAs($profesor)
            ->from(route('ofertas.show', $oferta))
            ->post(route('ofertas.solicitudes.desasignar', $solicitud));

        $response->assertRedirect(route('ofertas.show', $oferta));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('solicitudes_practicas', [
            'id' => $solicitud->id,
        ]);

        $oferta->refresh();
        $this->assertSame('pendiente', $oferta->estado);
    }

    public function test_profesor_can_desasignar_alumno_when_oferta_is_solicitada(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userA, $alumnoA] = $this->createAlumno();
        [$userB, $alumnoB] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'num_alumnos' => 2,
            'estado' => 'solicitada',
        ]);

        $solicitudA = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoA->id,
            'estado' => 'aceptado',
        ]);

        $solicitudB = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoB->id,
            'estado' => 'aceptado',
        ]);

        // Desasignar solo a alumno A
        $response = $this->actingAs($profesor)
            ->from(route('ofertas.show', $oferta))
            ->post(route('ofertas.solicitudes.desasignar', $solicitudA));

        $response->assertRedirect(route('ofertas.show', $oferta));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('solicitudes_practicas', [
            'id' => $solicitudA->id,
        ]);
        $this->assertDatabaseHas('solicitudes_practicas', [
            'id' => $solicitudB->id,
        ]);

        // Como todavía queda el alumno B asignado, la oferta sigue en estado solicitada
        $oferta->refresh();
        $this->assertSame('solicitada', $oferta->estado);
    }

    public function test_oferta_solicitada_reverts_to_pendiente_when_all_alumnos_are_desasignados(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $response = $this->actingAs($profesor)
            ->from(route('ofertas.show', $oferta))
            ->post(route('ofertas.solicitudes.desasignar', $solicitud));

        $response->assertRedirect(route('ofertas.show', $oferta));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('solicitudes_practicas', [
            'id' => $solicitud->id,
        ]);

        // Al no quedar alumnos asignados, revierte a pendiente
        $oferta->refresh();
        $this->assertSame('pendiente', $oferta->estado);
    }

    public function test_profesor_cannot_desasignar_when_oferta_is_abierta(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'abierta',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($profesor)
            ->from(route('ofertas.show', $oferta))
            ->post(route('ofertas.solicitudes.desasignar', $solicitud));

        $response->assertRedirect(route('ofertas.show', $oferta));
        $response->assertSessionHasErrors('error');

        // La solicitud no debe haber sido borrada
        $this->assertDatabaseHas('solicitudes_practicas', [
            'id' => $solicitud->id,
        ]);

        $oferta->refresh();
        $this->assertSame('abierta', $oferta->estado);
    }

    public function test_profesor_cannot_desasignar_when_oferta_is_cerrada(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'cerrada',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $response = $this->actingAs($profesor)
            ->from(route('ofertas.show', $oferta))
            ->post(route('ofertas.solicitudes.desasignar', $solicitud));

        $response->assertRedirect(route('ofertas.show', $oferta));
        $response->assertSessionHasErrors('error');

        $this->assertDatabaseHas('solicitudes_practicas', [
            'id' => $solicitud->id,
        ]);

        $oferta->refresh();
        $this->assertSame('cerrada', $oferta->estado);
    }

    public function test_profesor_cannot_desasignar_when_alumno_has_practica_created(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        // Crear una práctica formal para este alumno y oferta
        Practica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
        ]);

        $response = $this->actingAs($profesor)
            ->from(route('ofertas.show', $oferta))
            ->post(route('ofertas.solicitudes.desasignar', $solicitud));

        $response->assertRedirect(route('ofertas.show', $oferta));
        $response->assertSessionHasErrors('error');

        $this->assertDatabaseHas('solicitudes_practicas', [
            'id' => $solicitud->id,
        ]);
    }

    public function test_alumno_cannot_desasignar(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $response = $this->actingAs($userAlumno)
            ->post(route('ofertas.solicitudes.desasignar', $solicitud));

        $response->assertForbidden();

        $this->assertDatabaseHas('solicitudes_practicas', [
            'id' => $solicitud->id,
        ]);
    }

    public function test_desasignar_by_oferta_and_alumno_route(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $response = $this->actingAs($profesor)
            ->from(route('ofertas.show', $oferta))
            ->post(route('ofertas.desasignar-alumno', [$oferta, $alumno]));

        $response->assertRedirect(route('ofertas.show', $oferta));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('solicitudes_practicas', [
            'id' => $solicitud->id,
        ]);
    }

    public function test_ui_shows_desasignar_button_only_when_oferta_is_pendiente_or_solicitada(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        // 1. Oferta solicitada -> SI muestra botón Desasignar
        $ofertaSolicitada = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
        ]);
        SolicitudPractica::create([
            'oferta_id' => $ofertaSolicitada->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $responseSolicitada = $this->actingAs($profesor)->get(route('ofertas.show', $ofertaSolicitada));
        $responseSolicitada->assertOk();
        $responseSolicitada->assertSee('Desasignar');

        // 2. Oferta pendiente -> SI muestra botón Desasignar si tiene solicitud
        $ofertaPendiente = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'pendiente',
        ]);
        SolicitudPractica::create([
            'oferta_id' => $ofertaPendiente->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $responsePendiente = $this->actingAs($profesor)->get(route('ofertas.show', $ofertaPendiente));
        $responsePendiente->assertOk();
        $responsePendiente->assertSee('Desasignar');

        // 3. Oferta abierta -> NO muestra botón Desasignar
        $ofertaAbierta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'abierta',
        ]);
        SolicitudPractica::create([
            'oferta_id' => $ofertaAbierta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'pendiente',
        ]);

        $responseAbierta = $this->actingAs($profesor)->get(route('ofertas.show', $ofertaAbierta));
        $responseAbierta->assertOk();
        $responseAbierta->assertDontSee('Desasignar');

        // 4. Oferta cerrada -> NO muestra botón Desasignar
        $ofertaCerrada = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'cerrada',
        ]);
        SolicitudPractica::create([
            'oferta_id' => $ofertaCerrada->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $responseCerrada = $this->actingAs($profesor)->get(route('ofertas.show', $ofertaCerrada));
        $responseCerrada->assertOk();
        $responseCerrada->assertDontSee('Desasignar');
    }

    public function test_admin_and_coordinador_dual_can_desasignar(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
        ]);

        $coordinador = User::factory()->create([
            'name' => 'Coord Test',
            'email' => 'coord_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
        ]);

        $empresa = Empresa::factory()->create();
        [$userA, $alumnoA] = $this->createAlumno();
        [$userB, $alumnoB] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => 'solicitada',
            'num_alumnos' => 2,
        ]);

        $solicitudA = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoA->id,
            'estado' => 'aceptado',
        ]);
        $solicitudB = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoB->id,
            'estado' => 'aceptado',
        ]);

        // Coordinador desasigna a Alumno A
        $responseCoord = $this->actingAs($coordinador)->post(route('ofertas.solicitudes.desasignar', $solicitudA));
        $responseCoord->assertRedirect();
        $responseCoord->assertSessionHas('success');
        $this->assertDatabaseMissing('solicitudes_practicas', ['id' => $solicitudA->id]);

        // Admin desasigna a Alumno B
        $responseAdmin = $this->actingAs($admin)->post(route('ofertas.solicitudes.desasignar', $solicitudB));
        $responseAdmin->assertRedirect();
        $responseAdmin->assertSessionHas('success');
        $this->assertDatabaseMissing('solicitudes_practicas', ['id' => $solicitudB->id]);

        $oferta->refresh();
        $this->assertSame('pendiente', $oferta->estado);
    }

    public function test_desasignado_alumno_cannot_access_solicitada_oferta_anymore(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        // Mientras está asignado, puede ver la oferta
        $responseBefore = $this->actingAs($userAlumno)->get(route('ofertas.show', $oferta));
        $responseBefore->assertOk();

        // Profesor desasigna al alumno
        $this->actingAs($profesor)->post(route('ofertas.solicitudes.desasignar', $solicitud));

        // Tras ser desasignado, ya no puede acceder a la oferta (403)
        $responseAfter = $this->actingAs($userAlumno)->get(route('ofertas.show', $oferta));
        $responseAfter->assertForbidden();
    }

    public function test_desasignado_alumno_not_visible_to_empresa_via_token(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
        ]);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        // Con token la empresa ve al alumno
        $responseBefore = $this->get(route('ofertas.compartida', $oferta->token));
        $responseBefore->assertOk();
        $responseBefore->assertSee($userAlumno->name);

        // Profesor desasigna al alumno
        $this->actingAs($profesor)->post(route('ofertas.solicitudes.desasignar', $solicitud));

        // Limpiar sesión para simular a la empresa accediendo como invitada sin flash messages
        $this->flushSession();

        // Con token la empresa ya NO ve al alumno desasignado
        $responseAfter = $this->get(route('ofertas.compartida', $oferta->token));
        $responseAfter->assertOk();
        $responseAfter->assertDontSee($userAlumno->name);
    }

    public function test_desasignar_from_practicas_grupos_show_and_ui_button(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $cursoActual = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2025/2026', 'fecha_inicio' => now()->startOfYear(), 'fecha_fin' => now()->endOfYear()]
        );

        $grupo = Grupo::factory()->create([
            'curso_academico_id' => $cursoActual->id,
            'tutor_id' => $profesor->id,
            'is_active' => true,
        ]);
        $alumno->grupos()->attach($grupo->id, ['curso_academico_id' => $cursoActual->id]);

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'curso_academico_id' => $cursoActual->id,
            'estado' => 'solicitada',
        ]);
        $oferta->grupos()->attach($grupo->id);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        // Ver que en practicas.grupos.show aparece el botón Desasignar
        $responseView = $this->actingAs($profesor)->get(route('practicas.grupos.show', $grupo));
        $responseView->assertOk();
        $responseView->assertSee('Desasignar');

        // Ejecutar la desasignación con referer
        $responseAction = $this->actingAs($profesor)
            ->from(route('practicas.grupos.show', $grupo))
            ->post(route('ofertas.solicitudes.desasignar', $solicitud));

        $responseAction->assertRedirect(route('practicas.grupos.show', $grupo));
        $responseAction->assertSessionHas('success');

        $this->assertDatabaseMissing('solicitudes_practicas', ['id' => $solicitud->id]);
    }

    public function test_desasignado_alumno_can_be_reassigned_afterwards(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $cursoActual = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2025/2026', 'fecha_inicio' => now()->startOfYear(), 'fecha_fin' => now()->endOfYear()]
        );

        $grupo = Grupo::factory()->create([
            'curso_academico_id' => $cursoActual->id,
            'tutor_id' => $profesor->id,
            'is_active' => true,
        ]);
        $alumno->grupos()->attach($grupo->id, ['curso_academico_id' => $cursoActual->id]);

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'curso_academico_id' => $cursoActual->id,
            'estado' => 'solicitada',
        ]);
        $oferta->grupos()->attach($grupo->id);

        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        // Desasignar
        $this->actingAs($profesor)->post(route('ofertas.solicitudes.desasignar', $solicitud));
        $this->assertDatabaseMissing('solicitudes_practicas', ['id' => $solicitud->id]);

        // Reasignar mediante enviarAAlumnos
        $responseReasignar = $this->actingAs($profesor)->post(route('ofertas.enviar', $oferta), [
            'alumno_ids' => [$alumno->id],
        ]);
        $responseReasignar->assertRedirect();

        $this->assertDatabaseHas('solicitudes_practicas', [
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);
    }
}
