<?php

namespace Tests\Feature;

use App\Mail\NotificacionMailable;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Mensaje;
use App\Models\Profesor;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MensajesEmpresasContactoProfesorTest extends TestCase
{
    private function createProfesor(string $name = 'Profesor Remitente', ?string $telefono = '611223344'): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => 'profe_' . uniqid() . '@ieshlanz.es',
            'telefono' => $telefono,
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        Profesor::factory()->create([
            'user_id' => $user->id,
            'es_coordinador_dual' => true, // Para tener permiso de enviar mensajes
        ]);

        return $user;
    }

    public function test_envio_mensaje_a_empresas_incluye_reply_to_con_email_del_profesor(): void
    {
        Mail::fake();

        $profesor = $this->createProfesor('Laura Tutor Dual', '654987321');

        $empresa = Empresa::factory()->create([
            'nombre' => 'Innovaciones Granadinas SL',
            'email' => 'rrhh_' . uniqid() . '@innovaciones.es',
            'is_active' => true,
        ]);

        $payload = [
            'asunto' => 'Plan de formación DUAL segundo trimestre',
            'cuerpo' => 'Estimados tutores, adjuntamos la información del seguimiento.',
            'tipo_destinatario' => 'empresas',
            'empresa_ids' => [$empresa->id],
        ];

        $response = $this->actingAs($profesor)->post(route('mensajes.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        Mail::assertSent(NotificacionMailable::class, function (NotificacionMailable $mail) use ($profesor, $empresa) {
            $hasRecipient = $mail->hasTo($empresa->email);
            $replyToList = $mail->envelope()->replyTo;

            $hasReplyTo = false;
            foreach ($replyToList as $address) {
                if ($address->address === $profesor->email) {
                    $hasReplyTo = true;
                    break;
                }
            }

            return $hasRecipient && $hasReplyTo && $mail->esParaEmpresa === true;
        });
    }

    public function test_envio_mensaje_a_empresas_incluye_datos_contacto_profesor_y_boton_mailto(): void
    {
        Mail::fake();

        $profesor = $this->createProfesor('Marcos Profesor', '648753326');

        $empresa = Empresa::factory()->create([
            'nombre' => 'Sistemas y Redes Andalucia',
            'email' => 'contacto_' . uniqid() . '@redesand.es',
            'is_active' => true,
        ]);

        $payload = [
            'asunto' => 'Información sobre incorporación de alumnado',
            'cuerpo' => 'Buenas tardes. Les comunicamos las fechas oficiales de inicio de las prácticas.',
            'tipo_destinatario' => 'empresas',
            'empresa_ids' => [$empresa->id],
        ];

        $this->actingAs($profesor)->post(route('mensajes.store'), $payload);

        Mail::assertSent(NotificacionMailable::class, function (NotificacionMailable $mail) use ($profesor) {
            $html = $mail->render();

            // Debe contener datos de contacto del profesor
            $hasName = str_contains($html, $profesor->name);
            $hasEmail = str_contains($html, $profesor->email);
            $hasPhone = str_contains($html, '648753326');

            // Debe contener botón para responder por mailto
            $hasMailto = str_contains($html, 'mailto:' . $profesor->email);
            $hasMailtoBtn = str_contains($html, 'Responder por correo electrónico');

            return $hasName && $hasEmail && $hasPhone && $hasMailto && $hasMailtoBtn;
        });
    }

    public function test_envio_mensaje_a_empresas_no_incluye_boton_ni_enlace_a_la_plataforma(): void
    {
        Mail::fake();

        $profesor = $this->createProfesor('Ana Garcia', '600112233');

        $empresa = Empresa::factory()->create([
            'nombre' => 'Desarrollo Web Granada',
            'email' => 'info_' . uniqid() . '@dwgranada.es',
            'is_active' => true,
        ]);

        $payload = [
            'asunto' => 'Calendario lectivo para empresas',
            'cuerpo' => 'Detalle del calendario para el periodo de estancia.',
            'tipo_destinatario' => 'empresas',
            'empresa_ids' => [$empresa->id],
        ];

        $this->actingAs($profesor)->post(route('mensajes.store'), $payload);

        Mail::assertSent(NotificacionMailable::class, function (NotificacionMailable $mail) {
            $html = $mail->render();

            // No debe incluir el botón de ver notificación en plataforma
            $hasPlatformButton = str_contains($html, 'Ver notificación en la plataforma');
            // No debe incluir el texto para copiar/pegar enlace
            $hasCopyPaste = str_contains($html, 'copiar y pegar el siguiente enlace en tu navegador');

            return !$hasPlatformButton && !$hasCopyPaste;
        });
    }

    public function test_envio_mensaje_a_empresas_usa_telefono_centro_si_profesor_no_tiene_telefono(): void
    {
        Mail::fake();

        // Profesor sin teléfono registrado
        $profesor = $this->createProfesor('Profesor Sin Telefono', null);

        $empresa = Empresa::factory()->create([
            'nombre' => 'Consultoría Informática Sur',
            'email' => 'tutor_' . uniqid() . '@consulsur.es',
            'is_active' => true,
        ]);

        $payload = [
            'asunto' => 'Consulta de plazas DUAL',
            'cuerpo' => 'Agradecemos su disponibilidad.',
            'tipo_destinatario' => 'empresas',
            'empresa_ids' => [$empresa->id],
        ];

        $this->actingAs($profesor)->post(route('mensajes.store'), $payload);

        Mail::assertSent(NotificacionMailable::class, function (NotificacionMailable $mail) {
            $html = $mail->render();
            // Debe mostrar el teléfono por defecto del centro escolar
            return str_contains($html, '958 89 38 50');
        });
    }

    public function test_envio_mensaje_a_alumnos_mantiene_acceso_a_plataforma_sin_contacto_empresa(): void
    {
        Mail::fake();

        $profesor = $this->createProfesor('Profesor de Grupo', '611223344');

        $grupo = Grupo::first() ?? Grupo::factory()->create(['nombre' => '1º DAM Mañana']);

        $alumnoUser = User::factory()->create([
            'name' => 'Alumno Test',
            'email' => 'alumno_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);

        $alumno = \App\Models\Alumno::factory()->create([
            'user_id' => $alumnoUser->id,
        ]);
        $grupo->alumnos()->attach($alumno->id);

        $payload = [
            'asunto' => 'Convocatoria de reunión de grupo',
            'cuerpo' => 'Estimados alumnos, el lunes tendremos sesión de seguimiento.',
            'tipo_destinatario' => 'grupos',
            'grupo_ids' => [$grupo->id],
        ];

        $response = $this->actingAs($profesor)->post(route('mensajes.store'), $payload);
        $response->assertSessionHasNoErrors();

        Mail::assertSent(NotificacionMailable::class, function (NotificacionMailable $mail) {
            $html = $mail->render();

            $hasPlatformButton = str_contains($html, 'Ver notificación en la plataforma');
            $hasCopyPaste = str_contains($html, 'copiar y pegar el siguiente enlace en tu navegador');
            $hasMailtoBtn = str_contains($html, 'Responder por correo electrónico');

            return $hasPlatformButton && $hasCopyPaste && !$hasMailtoBtn && $mail->esParaEmpresa === false;
        });
    }

    public function test_notificacion_service_detecta_rol_empresa_automaticamente(): void
    {
        Mail::fake();

        $empresaUser = User::factory()->create([
            'name' => 'Entidad Colaboradora SL',
            'email' => 'empresa_' . uniqid() . '@entidad.es',
            'roles' => [User::ROLE_EMPRESA],
            'is_active' => true,
        ]);

        $service = app(NotificacionService::class);
        $service->enviarEmail(
            usuarioId: $empresaUser->id,
            titulo: 'Aviso directo',
            mensaje: 'Mensaje directo a entidad',
            replyToEmail: 'docente@ieshlanz.es',
            replyToNombre: 'Docente Lanz'
        );

        Mail::assertSent(NotificacionMailable::class, function (NotificacionMailable $mail) {
            return $mail->esParaEmpresa === true &&
                   !empty($mail->envelope()->replyTo) &&
                   $mail->envelope()->replyTo[0]->address === 'docente@ieshlanz.es';
        });
    }
}
