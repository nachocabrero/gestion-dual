<?php

namespace Tests\Feature;

use App\Models\Mensaje;
use App\Models\User;
use Tests\TestCase;

class MensajeDestinatariosTruncarTest extends TestCase
{
    /**
     * Un mensaje con destinatarios de 100 caracteres o menos se muestra sin truncar ni añadir '...'.
     */
    public function test_destinatarios_under_100_chars_are_displayed_normally(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
        ]);

        $destinatariosCorto = 'Grupos completos: 1º DAW Mañana, 2º DAW Mañana';
        $this->assertLessThanOrEqual(100, mb_strlen($destinatariosCorto));

        $mensaje = Mensaje::create([
            'remitente_id' => $admin->id,
            'asunto' => 'Comunicado de prueba corto',
            'cuerpo' => 'Cuerpo del mensaje corto',
            'tipo_destinatario' => 'grupos',
            'resumen_destinatarios' => $destinatariosCorto,
            'total_destinatarios' => 10,
        ]);

        // Ver en vista de detalle (show)
        $responseShow = $this->actingAs($admin)->get(route('mensajes.show', $mensaje));
        $responseShow->assertOk();
        $responseShow->assertSee($destinatariosCorto);

        // Ver en vista de listado (index)
        $responseIndex = $this->actingAs($admin)->get(route('mensajes.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee($destinatariosCorto);
    }

    /**
     * Un mensaje con destinatarios de más de 100 caracteres se corta a 100 con '...' y dispone de hover y click para ver todos.
     */
    public function test_destinatarios_over_100_chars_are_truncated_and_expandable_on_hover_or_click(): void
    {
        $admin = User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
        ]);

        $destinatariosLargo = 'Grupos completos: 1º DAW Mañana, 2º DAW Mañana, 1º DAM Mañana, 2º DAM Mañana, 1º ASIR Mañana, 2º ASIR Mañana, 1º SMR Tarde';
        $this->assertGreaterThan(100, mb_strlen($destinatariosLargo));

        $esperadoCorto = mb_substr($destinatariosLargo, 0, 100) . '...';

        $mensaje = Mensaje::create([
            'remitente_id' => $admin->id,
            'asunto' => 'Comunicado de prueba con muchos destinatarios',
            'cuerpo' => 'Cuerpo del mensaje con audiencia amplia',
            'tipo_destinatario' => 'grupos',
            'resumen_destinatarios' => $destinatariosLargo,
            'total_destinatarios' => 80,
        ]);

        // Ver en vista show
        $responseShow = $this->actingAs($admin)->get(route('mensajes.show', $mensaje));
        $responseShow->assertOk();
        // El texto truncado inicial con puntos suspensivos debe estar presente
        $responseShow->assertSee($esperadoCorto);
        // Debe contener los manejadores de Alpine para ratón encima y clic
        $responseShow->assertSee('@mouseenter="hovered = true"', false);
        $responseShow->assertSee('@mouseleave="hovered = false"', false);
        $responseShow->assertSee('@click.stop="expanded = !expanded"', false);
        // Debe tener el título con el texto completo para accesibilidad/tooltip nativo
        $responseShow->assertSee('title="' . e($destinatariosLargo) . '"', false);

        // Ver en listado index
        $responseIndex = $this->actingAs($admin)->get(route('mensajes.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee($esperadoCorto);
        $responseIndex->assertSee('@mouseenter="hovered = true"', false);
        $responseIndex->assertSee('@click.stop="expanded = !expanded"', false);
    }
}
