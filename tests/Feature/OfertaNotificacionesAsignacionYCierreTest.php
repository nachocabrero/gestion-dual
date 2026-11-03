<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Notificacion;
use App\Models\OfertaPractica;
use App\Models\Practica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OfertaNotificacionesAsignacionYCierreTest extends TestCase
{
    private function createProfesor(): User
    {
        return User::factory()->create([
            'name' => 'Profesor Test ' . uniqid(),
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
            'name' => 'Alumno Test ' . uniqid(),
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

    public function test_asignar_nuevo_alumno_no_reenvia_notificacion_a_alumnos_previamente_asignados(): void
    {
        Mail::fake();

        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $cursoActual = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2025/2026', 'fecha_inicio' => now()->startOfYear(), 'fecha_fin' => now()->endOfYear()]
        );

        $grupo = Grupo::factory()->create([
            'curso_academico_id' => $cursoActual->id,
            'tutor_id' => $profesor->id,
            'is_active' => true,
        ]);

        [$userA, $alumnoA] = $this->createAlumno();
        [$userB, $alumnoB] = $this->createAlumno();
        $alumnoA->grupos()->attach($grupo->id, ['curso_academico_id' => $cursoActual->id]);
        $alumnoB->grupos()->attach($grupo->id, ['curso_academico_id' => $cursoActual->id]);

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'pendiente',
            'num_alumnos' => 2,
        ]);
        $oferta->grupos()->attach($grupo->id);

        // 1. Asignar primero solo al alumno A
        $response1 = $this->actingAs($profesor)->post(route('ofertas.enviar', $oferta), [
            'alumno_ids' => [$alumnoA->id],
        ]);
        $response1->assertRedirect();

        // Alumno A ha recibido su notificación
        $notifsA_Despues1 = Notificacion::where('usuario_id', $userA->id)->where('tipo', 'oferta_nueva')->count();
        $this->assertEquals(1, $notifsA_Despues1);
        $this->assertEquals(0, Notificacion::where('usuario_id', $userB->id)->where('tipo', 'oferta_nueva')->count());

        // 2. Modificar la oferta añadiendo al alumno B (se envían [alumnoA, alumnoB])
        $response2 = $this->actingAs($profesor)->post(route('ofertas.enviar', $oferta), [
            'alumno_ids' => [$alumnoA->id, $alumnoB->id],
        ]);
        $response2->assertRedirect();

        // Alumno A NO debe haber recibido una segunda notificación (sigue teniendo 1)
        $notifsA_Despues2 = Notificacion::where('usuario_id', $userA->id)->where('tipo', 'oferta_nueva')->count();
        $this->assertEquals(1, $notifsA_Despues2, 'El alumno A ya estaba asignado y no debe recibir otra notificación cuando se asigna al alumno B.');

        // Alumno B es recién asignado, por lo que SÍ recibe su primera notificación
        $notifsB_Despues2 = Notificacion::where('usuario_id', $userB->id)->where('tipo', 'oferta_nueva')->count();
        $this->assertEquals(1, $notifsB_Despues2, 'El alumno B es recién asignado y debe recibir su notificación.');

        // Verificar emails enviados a través de NotificacionMailable
        Mail::assertSent(\App\Mail\NotificacionMailable::class, function ($mail) use ($userA) {
            return $mail->hasTo($userA->email);
        });
        Mail::assertSent(\App\Mail\NotificacionMailable::class, function ($mail) use ($userB) {
            return $mail->hasTo($userB->email);
        });
    }

    public function test_cambio_de_estado_a_cerrada_manual_notifica_a_alumnos_implicados(): void
    {
        Mail::fake();

        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userA, $alumnoA] = $this->createAlumno();
        [$userB, $alumnoB] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
            'num_alumnos' => 2,
            'especialidad_requerida' => 'Desarrollo Frontend',
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoA->id,
            'estado' => 'aceptado',
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoB->id,
            'estado' => 'aceptado',
        ]);

        // Cambiar estado a cerrada mediante update de oferta
        $response = $this->actingAs($profesor)->put(route('ofertas.update', $oferta), [
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Desarrollo Frontend',
            'num_alumnos' => 2,
            'estado' => 'cerrada',
        ]);
        $response->assertRedirect();

        $oferta->refresh();
        $this->assertSame('cerrada', $oferta->estado);

        // Ambos alumnos implicados deben recibir la notificación de oferta_cerrada
        $this->assertEquals(1, Notificacion::where('usuario_id', $userA->id)->where('tipo', 'oferta_cerrada')->count());
        $this->assertEquals(1, Notificacion::where('usuario_id', $userB->id)->where('tipo', 'oferta_cerrada')->count());

        Mail::assertSent(\App\Mail\NotificacionMailable::class, function ($mail) use ($userA) {
            return $mail->hasTo($userA->email) && str_contains($mail->titulo, 'cerrada');
        });
        Mail::assertSent(\App\Mail\NotificacionMailable::class, function ($mail) use ($userB) {
            return $mail->hasTo($userB->email) && str_contains($mail->titulo, 'cerrada');
        });
    }

    public function test_cierre_automatico_al_completar_plazas_en_practicas_notifica_a_alumnos_implicados(): void
    {
        Mail::fake();

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
            'estado' => 'solicitada',
            'num_alumnos' => 1,
            'curso_academico_id' => $cursoActual->id,
        ]);
        $oferta->grupos()->attach($grupo->id);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        // Registrar práctica completando la única plaza de la oferta
        $response = $this->actingAs($profesor)->post(route('practicas.store'), [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'oferta_id' => $oferta->id,
            'curso_academico_id' => $cursoActual->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonths(3)->toDateString(),
            'horas_acumuladas' => 380,
            'convenio_firmado' => true,
        ]);
        $response->assertSessionHasNoErrors();

        $oferta->refresh();
        $this->assertSame('cerrada', $oferta->estado);

        // El alumno debe haber recibido la notificación de oferta_cerrada
        $this->assertEquals(1, Notificacion::where('usuario_id', $userAlumno->id)->where('tipo', 'oferta_cerrada')->count());

        Mail::assertSent(\App\Mail\NotificacionMailable::class, function ($mail) use ($userAlumno) {
            return $mail->hasTo($userAlumno->email) && str_contains($mail->titulo, 'cerrada');
        });
    }

    public function test_modificar_oferta_sin_cerrarla_no_envia_notificacion_de_cierre(): void
    {
        Mail::fake();

        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        [$userAlumno, $alumno] = $this->createAlumno();

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
            'num_alumnos' => 1,
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        // Modificar solo la descripción manteniendo el estado solicitada
        $response = $this->actingAs($profesor)->put(route('ofertas.update', $oferta), [
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => $oferta->especialidad_requerida,
            'num_alumnos' => 1,
            'estado' => 'solicitada',
            'descripcion' => 'Nueva descripción editada',
        ]);
        $response->assertRedirect();

        $this->assertEquals(0, Notificacion::where('usuario_id', $userAlumno->id)->where('tipo', 'oferta_cerrada')->count());
    }

    public function test_alumno_con_estado_pendiente_no_recibe_reenvio_de_mail_al_ser_asignado_o_al_asignar_a_otro(): void
    {
        Mail::fake();

        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $cursoActual = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2025/2026', 'fecha_inicio' => now()->startOfYear(), 'fecha_fin' => now()->endOfYear()]
        );

        $grupo = Grupo::factory()->create([
            'curso_academico_id' => $cursoActual->id,
            'tutor_id' => $profesor->id,
            'is_active' => true,
        ]);

        [$userA, $alumnoA] = $this->createAlumno();
        [$userB, $alumnoB] = $this->createAlumno();
        $alumnoA->grupos()->attach($grupo->id, ['curso_academico_id' => $cursoActual->id]);
        $alumnoB->grupos()->attach($grupo->id, ['curso_academico_id' => $cursoActual->id]);

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'solicitada',
            'num_alumnos' => 2,
        ]);
        $oferta->grupos()->attach($grupo->id);

        // Alumno A ya tiene solicitud con estado 'pendiente'
        $solicitudA = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoA->id,
            'estado' => 'pendiente',
        ]);

        // Profesor asigna a Alumno A y a Alumno B
        $response = $this->actingAs($profesor)->post(route('ofertas.enviar', $oferta), [
            'alumno_ids' => [$alumnoA->id, $alumnoB->id],
        ]);
        $response->assertRedirect();

        // Alumno A ya estaba vinculado con solicitud pendiente, por lo que NO recibe ofertaEnviada
        $this->assertEquals(0, Notificacion::where('usuario_id', $userA->id)->where('tipo', 'oferta_nueva')->count());

        // Alumno B es recién asignado (no tenía solicitud), por lo que SÍ recibe ofertaEnviada
        $this->assertEquals(1, Notificacion::where('usuario_id', $userB->id)->where('tipo', 'oferta_nueva')->count());

        // La solicitud de Alumno A ahora queda como aceptada
        $this->assertSame('aceptado', $solicitudA->fresh()->estado);

        Mail::assertNotSent(\App\Mail\NotificacionMailable::class, function ($mail) use ($userA) {
            return $mail->hasTo($userA->email);
        });
        Mail::assertSent(\App\Mail\NotificacionMailable::class, function ($mail) use ($userB) {
            return $mail->hasTo($userB->email);
        });
    }
}
