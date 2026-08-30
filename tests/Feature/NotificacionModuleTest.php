<?php

namespace Tests\Feature;

use App\Models\Notificacion;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificacionModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_ver_notificaciones(): void
    {
        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        Notificacion::create([
            'usuario_id' => $user->id,
            'tipo' => 'empresa_asignada',
            'titulo' => 'Empresa asignada',
            'mensaje' => 'Se te asignó una empresa',
            'es_leida' => false,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('notificaciones.index'));
        $response->assertOk();
        $response->assertSee('Empresa asignada');
    }

    public function test_notificaciones_se_marcan_leidas_al_ver(): void
    {
        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        Notificacion::create([
            'usuario_id' => $user->id,
            'tipo' => 'empresa_asignada',
            'titulo' => 'Empresa',
            'mensaje' => 'Test',
            'es_leida' => false,
        ]);

        $this->actingAs($user);
        $this->get(route('notificaciones.index'));

        $this->assertFalse(Notificacion::where('usuario_id', $user->id)->where('es_leida', false)->exists());
    }

    public function test_contador_de_no_leidas(): void
    {
        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        Notificacion::create(['usuario_id' => $user->id, 'tipo' => 'a', 'titulo' => 'T1', 'mensaje' => 'M1', 'es_leida' => false]);
        Notificacion::create(['usuario_id' => $user->id, 'tipo' => 'b', 'titulo' => 'T2', 'mensaje' => 'M2', 'es_leida' => false]);
        Notificacion::create(['usuario_id' => $user->id, 'tipo' => 'c', 'titulo' => 'T3', 'mensaje' => 'M3', 'es_leida' => true]);

        $this->actingAs($user);
        $response = $this->get(route('notificaciones.contador'));
        $response->assertJson(['count' => 2]);
    }

    public function test_service_crea_notificacion(): void
    {
        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        $service = app(NotificacionService::class);
        $notif = $service->crear($user->id, 'test', 'Test', 'Mensaje', '/test', ['key' => 'val'], 7);

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $user->id,
            'tipo' => 'test',
            'titulo' => 'Test',
            'enlace' => '/test',
        ]);
        $this->assertNotNull($notif->expira_en);
    }

    public function test_service_empresa_asignada(): void
    {
        $alumno = \App\Models\Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'nss' => '1234567890B',
            'domicilio' => 'Calle 2',
        ]);

        $service = app(NotificacionService::class);
        $service->empresaAsignada($alumno->id, 'Acme Corp');

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $alumno->user_id,
            'tipo' => 'empresa_asignada',
            'titulo' => 'Empresa asignada: Acme Corp',
        ]);
    }

    public function test_service_acuerdo_cambiado(): void
    {
        $alumno = \App\Models\Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'nss' => '1234567890B',
            'domicilio' => 'Calle 2',
        ]);

        $service = app(NotificacionService::class);
        $service->acuerdoCambiado($alumno->id, 'firmado');

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $alumno->user_id,
            'tipo' => 'estado_acuerdo',
        ]);
    }

    public function test_service_proyecto_calificado(): void
    {
        $alumno = \App\Models\Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'nss' => '1234567890C',
            'domicilio' => 'Calle 3',
        ]);

        $service = app(NotificacionService::class);
        $service->proyectoCalificado($alumno->id, 8.5);

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $alumno->user_id,
            'tipo' => 'proyecto_calificado',
        ]);
    }

    public function test_service_limpiar_expiradas(): void
    {
        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        // Notificación expirada
        Notificacion::create([
            'usuario_id' => $user->id,
            'tipo' => 'test',
            'titulo' => 'Expirada',
            'mensaje' => 'Expiró ayer',
            'expira_en' => now()->subDay(),
        ]);

        // Notificación vigente
        Notificacion::create([
            'usuario_id' => $user->id,
            'tipo' => 'test2',
            'titulo' => 'Vigente',
            'mensaje' => 'Válida',
            'expira_en' => now()->addDay(),
        ]);

        $service = app(NotificacionService::class);
        $eliminated = $service->limpiarExpiradas();

        $this->assertEquals(1, $eliminated);
        $this->assertDatabaseMissing('notificaciones', ['titulo' => 'Expirada']);
        $this->assertDatabaseHas('notificaciones', ['titulo' => 'Vigente']);
    }

    public function test_notificacion_sin_expiracion_es_permanente(): void
    {
        $user = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        $service = app(NotificacionService::class);
        $service->crear($user->id, 'test', 'Permanente', 'Sin expirar', null, null, null);

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $user->id,
            'expira_en' => null,
        ]);
    }

    public function test_usuario_ajeno_no_ve_notificaciones(): void
    {
        $user1 = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);
        $user2 = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()]);

        Notificacion::create([
            'usuario_id' => $user1->id,
            'tipo' => 'test',
            'titulo' => 'Privada',
            'mensaje' => 'No visible',
        ]);

        $this->actingAs($user2);
        $response = $this->get(route('notificaciones.index'));
        $response->assertOk();
        $response->assertDontSee('Privada');
    }

    public function test_aceptar_solicitud_envia_notificacion(): void
    {
        $empresa = \App\Models\Empresa::create([
            'nombre' => 'Acme Corp',
            'cif' => 'A12345678',
            'direccion' => 'Calle 1',
            'telefono' => '600000000',
            'email' => 'contacto@acme.com',
            'responsable_nombre' => 'Juan',
            'responsable_dni' => '12345678A',
        ]);

        $alumno = \App\Models\Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'nss' => '1234567890D',
            'domicilio' => 'Calle 4',
        ]);

        $oferta = \App\Models\OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR]])->id,
            'creador_type' => User::class,
            'especialidad_requerida' => 'DAW',
            'num_alumnos' => 1,
            'estado' => 'activa',
        ]);

        $solicitud = \App\Models\SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'pendiente',
        ]);

        $this->actingAs(User::where('roles', 'like', '%profesor%')->first() ?? User::factory()->create(['roles' => [User::ROLE_PROFESOR]]));
        $response = $this->post(route('ofertas.solicitudes.aceptar', $solicitud));

        $this->assertDatabaseHas('solicitudes_practicas', [
            'id' => $solicitud->id,
            'estado' => 'aceptado',
        ]);

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $alumno->user_id,
            'tipo' => 'empresa_asignada',
        ]);
    }

    public function test_calificar_proyecto_envia_notificacion(): void
    {
        $profesor = \App\Models\Profesor::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_PROFESOR], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'dni' => '11111111A',
        ]);

        $alumno = \App\Models\Alumno::create([
            'user_id' => User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'consent_rgpd' => true, 'consent_rgpd_at' => now()])->id,
            'nss' => '1234567890E',
            'domicilio' => 'Calle 5',
        ]);
        $grupo = \App\Models\Grupo::factory()->create(['tutor_id' => $profesor->id]);
        $alumno->grupos()->attach($grupo->id);

        $proyecto = \App\Models\Proyecto::create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => \App\Models\Ciclo::factory()->create()->id,
            'curso_academico_id' => \App\Models\CursoAcademico::factory()->create()->id,
            'titulo' => 'Proyecto Test',
            'descripcion' => 'Descripcion',
        ]);

        $this->actingAs($profesor->user);
        $response = $this->post(route('proyectos.calificar', $proyecto), [
            'calificacion' => 8.5,
            'es_destacado' => false,
        ]);

        $this->assertDatabaseHas('proyectos', [
            'id' => $proyecto->id,
            'calificacion' => 8.5,
        ]);

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $alumno->user_id,
            'tipo' => 'proyecto_calificado',
        ]);
    }
}