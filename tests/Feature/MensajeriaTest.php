<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Mensaje;
use App\Models\Notificacion;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MensajeriaTest extends TestCase
{
    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createCoordinadorDual(): User
    {
        $user = User::factory()->create([
            'name' => 'Coordinador Dual',
            'email' => 'coord_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        Profesor::factory()->create([
            'user_id' => $user->id,
            'es_coordinador_dual' => true,
        ]);

        return $user;
    }

    private function createProfesor(): User
    {
        $user = User::factory()->create([
            'name' => 'Profesor Normal',
            'email' => 'profesor_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        Profesor::factory()->create([
            'user_id' => $user->id,
            'es_coordinador_dual' => false,
        ]);

        return $user;
    }

    private function createAlumno(): User
    {
        return User::factory()->create([
            'name' => 'Alumno User ' . uniqid(),
            'email' => 'alumno_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    public function test_admin_and_coordinador_dual_can_view_mensajes_index(): void
    {
        $admin = $this->createAdmin();
        $coord = $this->createCoordinadorDual();

        $responseAdmin = $this->actingAs($admin)->get(route('mensajes.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Mensajería y Comunicados');

        $responseCoord = $this->actingAs($coord)->get(route('mensajes.index'));
        $responseCoord->assertOk();
        $responseCoord->assertSee('Mensajería y Comunicados');
    }

    public function test_regular_profesor_and_alumno_cannot_access_mensajes_management(): void
    {
        $profesor = $this->createProfesor();
        $alumno = $this->createAlumno();

        $this->actingAs($profesor)->get(route('mensajes.index'))->assertForbidden();
        $this->actingAs($profesor)->get(route('mensajes.create'))->assertForbidden();

        $this->actingAs($alumno)->get(route('mensajes.index'))->assertForbidden();
        $this->actingAs($alumno)->get(route('mensajes.create'))->assertForbidden();
    }

    public function test_coordinador_dual_can_view_create_form(): void
    {
        $coord = $this->createCoordinadorDual();

        $response = $this->actingAs($coord)->get(route('mensajes.create'));
        $response->assertOk();
        $response->assertSee('Nuevo Comunicado Oficial');
        $response->assertSee('Grupos completos');
        $response->assertSee('Alumnos seleccionados');
        $response->assertSee('Profesorado de ciclos');
    }

    public function test_send_message_to_full_groups_creates_notifications_and_emails(): void
    {
        Mail::fake();

        $coord = $this->createCoordinadorDual();

        $grupo = Grupo::factory()->create(['nombre' => '2º DAW Especial']);

        $u1 = $this->createAlumno();
        $a1 = Alumno::factory()->create(['user_id' => $u1->id]);
        $grupo->alumnos()->attach($a1->id);

        $u2 = $this->createAlumno();
        $a2 = Alumno::factory()->create(['user_id' => $u2->id]);
        $grupo->alumnos()->attach($a2->id);

        $payload = [
            'tipo_destinatario' => 'grupos',
            'grupo_ids' => [$grupo->id],
            'asunto' => 'Convocatoria a reunión de orientación DUAL',
            'cuerpo' => 'Estimados alumnos, se convoca a reunión informativa el próximo lunes a las 10:00.',
        ];

        $response = $this->actingAs($coord)->post(route('mensajes.store'), $payload);

        $response->assertRedirect();

        $mensaje = Mensaje::where('asunto', 'Convocatoria a reunión de orientación DUAL')->first();
        $this->assertNotNull($mensaje);
        $this->assertEquals(2, $mensaje->total_destinatarios);
        $this->assertStringContainsString('2º DAW Especial', $mensaje->resumen_destinatarios);
        $this->assertEquals(2, $mensaje->destinatarios()->count());

        // Comprobar notificaciones in-app creadas
        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $u1->id,
            'tipo' => 'mensaje_recibido',
            'titulo' => 'Convocatoria a reunión de orientación DUAL',
            'es_leida' => false,
        ]);
        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $u2->id,
            'tipo' => 'mensaje_recibido',
            'titulo' => 'Convocatoria a reunión de orientación DUAL',
            'es_leida' => false,
        ]);

        // Comprobar envío de correos institucionales con asunto oficial
        Mail::assertSent(\App\Mail\NotificacionMailable::class, 2);
    }

    public function test_send_message_to_selected_students_in_group(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();

        $grupo = Grupo::factory()->create(['nombre' => '1º ASIR']);

        $u1 = $this->createAlumno();
        $a1 = Alumno::factory()->create(['user_id' => $u1->id]);
        $grupo->alumnos()->attach($a1->id);

        $u2 = $this->createAlumno();
        $a2 = Alumno::factory()->create(['user_id' => $u2->id]);
        $grupo->alumnos()->attach($a2->id);

        // Enviamos solo al alumno 1
        $payload = [
            'tipo_destinatario' => 'alumnos_grupo',
            'grupo_id' => $grupo->id,
            'alumno_ids' => [$a1->id],
            'asunto' => 'Revisión individual de documentación',
            'cuerpo' => 'Por favor pasa por el departamento a firmar la adenda.',
        ];

        $response = $this->actingAs($admin)->post(route('mensajes.store'), $payload);
        $response->assertRedirect();

        $mensaje = Mensaje::where('asunto', 'Revisión individual de documentación')->first();
        $this->assertNotNull($mensaje);
        $this->assertEquals(1, $mensaje->total_destinatarios);
        $this->assertTrue($mensaje->destinatarios->contains('id', $u1->id));
        $this->assertFalse($mensaje->destinatarios->contains('id', $u2->id));

        // Solo u1 debe tener notificación
        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $u1->id,
            'tipo' => 'mensaje_recibido',
            'titulo' => 'Revisión individual de documentación',
        ]);
        $this->assertDatabaseMissing('notificaciones', [
            'usuario_id' => $u2->id,
            'titulo' => 'Revisión individual de documentación',
        ]);
    }

    public function test_send_message_to_teachers_of_cycles(): void
    {
        Mail::fake();

        $coord = $this->createCoordinadorDual();

        $familia = Familia::factory()->create();
        $ciclo = Ciclo::factory()->create(['familia_id' => $familia->id, 'nombre' => 'Desarrollo de Apps Multiplataforma']);
        $asignatura = Asignatura::factory()->create(['ciclo_id' => $ciclo->id, 'nombre' => 'Acceso a Datos']);

        $profUser = $this->createProfesor();
        $profModel = $profUser->profesor;
        $profModel->asignaturas()->attach($asignatura->id);

        $payload = [
            'tipo_destinatario' => 'profesores_ciclos',
            'ciclo_ids' => [$ciclo->id],
            'asunto' => 'Coordinación trimestral DUAL DAM',
            'cuerpo' => 'Adjunto calendario de evaluaciones conjuntas.',
        ];

        $response = $this->actingAs($coord)->post(route('mensajes.store'), $payload);
        $response->assertRedirect();

        $mensaje = Mensaje::where('asunto', 'Coordinación trimestral DUAL DAM')->first();
        $this->assertNotNull($mensaje);
        $this->assertTrue($mensaje->destinatarios->contains('id', $profUser->id));

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $profUser->id,
            'tipo' => 'mensaje_recibido',
            'titulo' => 'Coordinación trimestral DUAL DAM',
        ]);
    }

    public function test_recipient_viewing_message_marks_it_and_notification_as_read(): void
    {
        $coord = $this->createCoordinadorDual();
        $alumnoUser = $this->createAlumno();

        $mensaje = Mensaje::create([
            'remitente_id' => $coord->id,
            'asunto' => 'Aviso de prueba lectura',
            'cuerpo' => 'Contenido del mensaje de prueba.',
            'tipo_destinatario' => 'alumnos_grupo',
            'resumen_destinatarios' => '1 alumno',
            'total_destinatarios' => 1,
        ]);

        $mensaje->destinatarios()->attach($alumnoUser->id, ['leido_at' => null]);

        $notif = Notificacion::create([
            'usuario_id' => $alumnoUser->id,
            'tipo' => 'mensaje_recibido',
            'titulo' => 'Aviso de prueba lectura',
            'mensaje' => 'Contenido del mensaje de prueba.',
            'enlace' => route('mensajes.show', $mensaje),
            'datos' => ['mensaje_id' => $mensaje->id],
            'es_leida' => false,
        ]);

        $this->assertFalse($mensaje->esLeidoPor($alumnoUser->id));
        $this->assertFalse($notif->fresh()->es_leida);

        // El alumno accede al mensaje
        $response = $this->actingAs($alumnoUser)->get(route('mensajes.show', $mensaje));
        $response->assertOk();
        $response->assertSee('Aviso de prueba lectura');
        $response->assertSee('Contenido del mensaje de prueba.');

        // Se marca como leído
        $this->assertTrue($mensaje->fresh()->esLeidoPor($alumnoUser->id));
        $this->assertTrue((bool)$notif->fresh()->es_leida);
    }

    public function test_unauthorized_user_cannot_view_others_message(): void
    {
        $coord = $this->createCoordinadorDual();
        $alumnoAutorizado = $this->createAlumno();
        $alumnoNoAutorizado = $this->createAlumno();

        $mensaje = Mensaje::create([
            'remitente_id' => $coord->id,
            'asunto' => 'Mensaje confidencial',
            'cuerpo' => 'Solo para el alumno autorizado.',
            'tipo_destinatario' => 'alumnos_grupo',
            'resumen_destinatarios' => '1 alumno',
            'total_destinatarios' => 1,
        ]);
        $mensaje->destinatarios()->attach($alumnoAutorizado->id);

        // Alumno no autorizado recibe 403 Forbidden
        $this->actingAs($alumnoNoAutorizado)->get(route('mensajes.show', $mensaje))->assertForbidden();

        // Alumno autorizado puede verlo
        $this->actingAs($alumnoAutorizado)->get(route('mensajes.show', $mensaje))->assertOk();
    }

    public function test_bloque_seguimiento_lecturas_aparece_solo_a_admin_y_coordinador_dual(): void
    {
        $coord = $this->createCoordinadorDual();
        $admin = $this->createAdmin();
        $alumno = $this->createAlumno();
        $profesor = $this->createProfesor();

        $mensaje = Mensaje::create([
            'remitente_id' => $coord->id,
            'asunto' => 'Aviso General de Prácticas',
            'cuerpo' => 'Cuerpo del aviso para todos.',
            'tipo_destinatario' => 'grupos',
            'resumen_destinatarios' => 'Todos',
            'total_destinatarios' => 2,
        ]);
        $mensaje->destinatarios()->attach([$alumno->id, $profesor->id]);

        // Coordinador Dual lo ve
        $responseCoord = $this->actingAs($coord)->get(route('mensajes.show', $mensaje));
        $responseCoord->assertOk();
        $responseCoord->assertSee('Seguimiento de Entrega y Lecturas');

        // Admin lo ve
        $responseAdmin = $this->actingAs($admin)->get(route('mensajes.show', $mensaje));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Seguimiento de Entrega y Lecturas');

        // Alumno destinatario NO ve el bloque de seguimiento
        $responseAlumno = $this->actingAs($alumno)->get(route('mensajes.show', $mensaje));
        $responseAlumno->assertOk();
        $responseAlumno->assertDontSee('Seguimiento de Entrega y Lecturas');

        // Profesor normal destinatario NO ve el bloque de seguimiento
        $responseProfesor = $this->actingAs($profesor)->get(route('mensajes.show', $mensaje));
        $responseProfesor->assertOk();
        $responseProfesor->assertDontSee('Seguimiento de Entrega y Lecturas');
    }
}

