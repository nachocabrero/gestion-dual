<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Mensaje;
use App\Models\Notificacion;
use App\Models\OfertaPractica;
use App\Models\Practica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Tests\TestCase;

class OfertaEstadosAndNotificacionesFixTest extends TestCase
{
    /**
     * Item 1: La audiencia del comunicado solo aparece en la tarjeta del mensaje y no duplicada en la cabecera.
     */
    public function test_mensaje_audiencia_appears_only_in_card_and_not_in_header(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
        ]);

        $mensaje = Mensaje::create([
            'remitente_id' => $admin->id,
            'asunto' => 'Asunto de Prueba Audiencia Unica',
            'cuerpo' => 'Cuerpo del mensaje de prueba.',
            'tipo_destinatario' => 'grupos',
            'resumen_destinatarios' => 'Grupos completos: 1º DAW Mañana',
            'total_destinatarios' => 10,
        ]);

        $response = $this->actingAs($admin)->get(route('mensajes.show', $mensaje));

        $response->assertOk();
        // Aparece la insignia Audiencia dentro de la tarjeta
        $response->assertSee('Audiencia:');
        $response->assertSee('Grupos completos: 1º DAW Mañana');

        // La cabecera solo debe mostrar el asunto sin párrafo de audiencia bajo él
        $content = $response->getContent();
        $this->assertEquals(1, substr_count($content, 'Grupos completos: 1º DAW Mañana'));
    }

    /**
     * Item 2: Cuando un alumno le da a ver detalles de una notificación, se marca como leída automáticamente.
     */
    public function test_alumno_clicking_ver_detalles_marks_notificacion_as_read_automatically(): void
    {
        $userAlumno = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);

        $notificacion = Notificacion::create([
            'usuario_id' => $userAlumno->id,
            'tipo' => 'empresa_asignada',
            'titulo' => 'Empresa Asignada',
            'mensaje' => 'Se te ha asignado la empresa Google.',
            'enlace' => '/alumnos',
            'es_leida' => false,
        ]);

        $this->assertFalse($notificacion->es_leida);

        // El alumno accede a la ruta de abrir la notificación
        $response = $this->actingAs($userAlumno)->get(route('notificaciones.abrir', $notificacion));

        $response->assertRedirect('/alumnos');
        $notificacion->refresh();
        $this->assertTrue($notificacion->es_leida);
    }

    /**
     * Item 3.1: Oferta 'pendiente' la ven solo profesores y se refleja en ofertas, gestión y prácticas por grupos.
     */
    public function test_oferta_pendiente_visible_only_for_profesores_and_hidden_for_alumnos(): void
    {
        $profesor = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);

        $alumnoUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumno = Alumno::create(['user_id' => $alumnoUser->id]);

        $cursoActual = \App\Models\CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2025/2026', 'fecha_inicio' => now()->startOfYear(), 'fecha_fin' => now()->endOfYear()]
        );

        $empresa = Empresa::factory()->create();
        $grupo = Grupo::factory()->create([
            'nombre' => '1º DAW Mañana',
            'numero' => 1,
            'curso_academico_id' => $cursoActual->id,
            'tutor_id' => $profesor->id,
            'is_active' => true,
        ]);
        $alumno->grupos()->attach($grupo->id);

        $ofertaPendiente = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Desarrollo Web',
            'num_alumnos' => 2,
            'estado' => 'pendiente',
            'creador_id' => $profesor->id,
            'creador_type' => User::class,
        ]);
        $ofertaPendiente->grupos()->attach($grupo->id);

        // Profesor ve la oferta pendiente en ofertas.index
        $responseProfesorIndex = $this->actingAs($profesor)->get(route('ofertas.index'));
        $responseProfesorIndex->assertOk();
        $responseProfesorIndex->assertSee($empresa->nombre);

        // Profesor ve la oferta en practicas por grupo
        $responseProfesorGrupo = $this->actingAs($profesor)->get(route('practicas.grupos.show', $grupo));
        $responseProfesorGrupo->assertOk();
        $responseProfesorGrupo->assertSee($empresa->nombre);

        // Alumno NO ve la oferta pendiente en ofertas.index
        $responseAlumnoIndex = $this->actingAs($alumnoUser)->get(route('ofertas.index'));
        $responseAlumnoIndex->assertOk();
        $responseAlumnoIndex->assertDontSee($empresa->nombre);

        // Alumno recibe 403 si intenta acceder a show de oferta pendiente
        $responseAlumnoShow = $this->actingAs($alumnoUser)->get(route('ofertas.show', $ofertaPendiente));
        $responseAlumnoShow->assertForbidden();
    }

    /**
     * Item 3.2: Oferta 'abierta' la ven profesores y alumnos del grupo asignado, pudiendo solicitar plaza.
     */
    public function test_oferta_abierta_visible_for_assigned_group_students_who_can_apply(): void
    {
        $profesor = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);

        $alumnoUserEnGrupo = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoEnGrupo = Alumno::create(['user_id' => $alumnoUserEnGrupo->id]);

        $alumnoUserOtroGrupo = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoOtroGrupo = Alumno::create(['user_id' => $alumnoUserOtroGrupo->id]);

        $empresa = Empresa::factory()->create();
        $grupoA = Grupo::factory()->create([
            'nombre' => '2º DAW Grupo A',
            'numero' => 2,
            'is_active' => true,
        ]);
        $grupoB = Grupo::factory()->create([
            'nombre' => '2º ASIR Grupo B',
            'numero' => 2,
            'is_active' => true,
        ]);

        $alumnoEnGrupo->grupos()->attach($grupoA->id);
        $alumnoOtroGrupo->grupos()->attach($grupoB->id);

        $ofertaAbierta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Desarrollo Frontend',
            'num_alumnos' => 2,
            'estado' => 'abierta',
            'creador_id' => $profesor->id,
            'creador_type' => User::class,
        ]);
        $ofertaAbierta->grupos()->attach($grupoA->id);

        // Alumno del grupo asignado VE la oferta abierta y botón de postularse
        $responseAlumnoIndex = $this->actingAs($alumnoUserEnGrupo)->get(route('ofertas.index'));
        $responseAlumnoIndex->assertOk();
        $responseAlumnoIndex->assertSee($empresa->nombre);

        $responseAlumnoShow = $this->actingAs($alumnoUserEnGrupo)->get(route('ofertas.show', $ofertaAbierta));
        $responseAlumnoShow->assertOk();
        $responseAlumnoShow->assertSee('Solicitar esta plaza');

        // Alumno puede solicitar plaza
        $responsePostular = $this->actingAs($alumnoUserEnGrupo)->post(route('ofertas.postularse', $ofertaAbierta));
        $responsePostular->assertSessionHas('success');

        $this->assertDatabaseHas('solicitudes_practicas', [
            'oferta_id' => $ofertaAbierta->id,
            'alumno_id' => $alumnoEnGrupo->id,
            'estado' => 'pendiente',
        ]);

        // Alumno de otro grupo NO ve la oferta abierta
        $responseOtroIndex = $this->actingAs($alumnoUserOtroGrupo)->get(route('ofertas.index'));
        $responseOtroIndex->assertOk();
        $responseOtroIndex->assertDontSee($empresa->nombre);

        $responseOtroShow = $this->actingAs($alumnoUserOtroGrupo)->get(route('ofertas.show', $ofertaAbierta));
        $responseOtroShow->assertForbidden();
    }

    /**
     * Item 3.3: Oferta 'solicitada' visible para profesores y solo alumnos asignados por el profesor.
     */
    public function test_oferta_solicitada_visible_only_for_assigned_alumnos(): void
    {
        $profesor = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);

        $alumnoAsignadoUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoAsignado = Alumno::create(['user_id' => $alumnoAsignadoUser->id]);

        $alumnoNoAsignadoUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoNoAsignado = Alumno::create(['user_id' => $alumnoNoAsignadoUser->id]);

        $empresa = Empresa::factory()->create();
        $grupo = Grupo::factory()->create([
            'nombre' => '2º DAM Común',
            'numero' => 2,
            'is_active' => true,
        ]);

        $alumnoAsignado->grupos()->attach($grupo->id);
        $alumnoNoAsignado->grupos()->attach($grupo->id);

        $ofertaSolicitada = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Backend Laravel',
            'num_alumnos' => 1,
            'estado' => 'solicitada',
            'creador_id' => $profesor->id,
            'creador_type' => User::class,
        ]);
        $ofertaSolicitada->grupos()->attach($grupo->id);

        // Asignar alumno por el profesor
        SolicitudPractica::create([
            'oferta_id' => $ofertaSolicitada->id,
            'alumno_id' => $alumnoAsignado->id,
            'estado' => 'aceptado',
        ]);

        // Alumno asignado VE la oferta
        $responseAsignadoIndex = $this->actingAs($alumnoAsignadoUser)->get(route('ofertas.index'));
        $responseAsignadoIndex->assertOk();
        $responseAsignadoIndex->assertSee($empresa->nombre);

        $responseAsignadoShow = $this->actingAs($alumnoAsignadoUser)->get(route('ofertas.show', $ofertaSolicitada));
        $responseAsignadoShow->assertOk();

        // Alumno NO asignado NO ve la oferta solicitada (aunque sea del mismo grupo)
        $responseNoAsignadoIndex = $this->actingAs($alumnoNoAsignadoUser)->get(route('ofertas.index'));
        $responseNoAsignadoIndex->assertOk();
        $responseNoAsignadoIndex->assertDontSee($empresa->nombre);

        $responseNoAsignadoShow = $this->actingAs($alumnoNoAsignadoUser)->get(route('ofertas.show', $ofertaSolicitada));
        $responseNoAsignadoShow->assertForbidden();
    }

    /**
     * Item 3.4: Oferta 'cerrada' visible para profesores y alumnos asignados, y se cierra al completar todas las plazas en prácticas.
     */
    public function test_oferta_cerrada_visible_for_assigned_alumnos_and_closes_when_all_places_have_practicas(): void
    {
        $profesor = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);

        $alumnoAsignadoUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);
        $alumnoAsignado = Alumno::create(['user_id' => $alumnoAsignadoUser->id]);

        $empresa = Empresa::factory()->create();
        $grupo = Grupo::factory()->create([
            'nombre' => '2º DAW Final',
            'numero' => 2,
            'tutor_id' => $profesor->id,
            'is_active' => true,
        ]);
        $alumnoAsignado->grupos()->attach($grupo->id);

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Fullstack',
            'num_alumnos' => 1,
            'estado' => 'solicitada',
            'creador_id' => $profesor->id,
            'creador_type' => User::class,
        ]);
        $oferta->grupos()->attach($grupo->id);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoAsignado->id,
            'estado' => 'aceptado',
        ]);

        // Crear la práctica para cubrir la única plaza de la oferta
        $responsePractica = $this->actingAs($profesor)->post(route('practicas.store'), [
            'alumno_id' => $alumnoAsignado->id,
            'empresa_id' => $empresa->id,
            'oferta_id' => $oferta->id,
            'curso_academico_id' => $grupo->curso_academico_id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonths(3)->toDateString(),
            'horas_acumuladas' => 380,
            'convenio_firmado' => true,
        ]);

        $responsePractica->assertSessionHasNoErrors();

        // La oferta ahora debe estar automáticamente cerrada
        $oferta->refresh();
        $this->assertSame('cerrada', $oferta->estado);

        // El alumno asignado puede seguir viendo su oferta cerrada
        $responseShow = $this->actingAs($alumnoAsignadoUser)->get(route('ofertas.show', $oferta));
        $responseShow->assertOk();
        $responseShow->assertSee('Cerrada');
    }
}
