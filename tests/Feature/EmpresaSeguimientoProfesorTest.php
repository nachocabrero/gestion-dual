<?php

namespace Tests\Feature;

use App\Mail\NotificacionMailable;
use App\Models\Empresa;
use App\Models\Mensaje;
use App\Models\Notificacion;
use App\Models\Profesor;
use App\Models\TutorLaboral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmpresaSeguimientoProfesorTest extends TestCase
{
    use RefreshDatabase;

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

    private function createProfesor(string $name = 'Profesor Test', bool $isCoord = false): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => 'prof_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $profesor = Profesor::factory()->create([
            'user_id' => $user->id,
            'es_coordinador_dual' => $isCoord,
        ]);

        return [$user, $profesor];
    }

    public function test_can_create_empresa_with_profesor_seguimiento(): void
    {
        $admin = $this->createAdmin();
        [$profUser, $profesor] = $this->createProfesor('Laura Seguimiento');

        $payload = [
            'nombre' => 'Tech Solutions SL',
            'cif' => 'B99900011',
            'email' => 'contacto@techsolutions.es',
            'telefono' => '958112233',
            'direccion' => 'Parque Tecnológico, Granada',
            'profesor_id' => $profesor->id,
            'is_active' => 1,
        ];

        $response = $this->actingAs($admin)->post(route('empresas.store'), $payload);

        $empresa = Empresa::where('cif', 'B99900011')->first();
        $this->assertNotNull($empresa);
        $response->assertRedirect(route('empresas.show', $empresa));
        $this->assertDatabaseHas('empresas', [
            'nombre' => 'Tech Solutions SL',
            'cif' => 'B99900011',
            'profesor_id' => $profesor->id,
        ]);

        $this->assertNotNull($empresa->profesor);
        $this->assertEquals($profesor->id, $empresa->profesor->id);
        $this->assertTrue($profesor->empresas->contains($empresa));
    }

    public function test_can_create_empresa_without_profesor_seguimiento(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'nombre' => 'Innova Granada SL',
            'cif' => 'B99900022',
            'email' => 'info@innova.es',
            'profesor_id' => '',
            'is_active' => 1,
        ];

        $response = $this->actingAs($admin)->post(route('empresas.store'), $payload);

        $empresa = Empresa::where('cif', 'B99900022')->first();
        $this->assertNotNull($empresa);
        $response->assertRedirect(route('empresas.show', $empresa));
        $this->assertDatabaseHas('empresas', [
            'cif' => 'B99900022',
            'profesor_id' => null,
        ]);

        $empresa = Empresa::where('cif', 'B99900022')->first();
        $this->assertNull($empresa->profesor);
    }

    public function test_can_update_empresa_to_assign_or_change_profesor(): void
    {
        $admin = $this->createAdmin();
        [$pUser1, $profesor1] = $this->createProfesor('Profesor Uno');
        [$pUser2, $profesor2] = $this->createProfesor('Profesor Dos');

        $empresa = Empresa::factory()->create([
            'nombre' => 'Empresa Test',
            'cif' => 'B12345678',
            'profesor_id' => $profesor1->id,
        ]);

        // Cambiar a profesor2
        $response = $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => 'Empresa Test Modificada',
            'cif' => 'B12345678',
            'profesor_id' => $profesor2->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('empresas.show', $empresa));
        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'profesor_id' => $profesor2->id,
        ]);

        // Desasignar profesor
        $response = $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => 'Empresa Test Modificada',
            'cif' => 'B12345678',
            'profesor_id' => '',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('empresas.show', $empresa));
        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'profesor_id' => null,
        ]);
    }

    public function test_empresas_index_displays_assigned_profesor_and_filters_correctly(): void
    {
        $admin = $this->createAdmin();
        [$pUser1, $profesor1] = $this->createProfesor('Rosa Tutor');
        [$pUser2, $profesor2] = $this->createProfesor('Marcos Tutor');

        $empresaAsignada = Empresa::factory()->create([
            'nombre' => 'Empresa Asignada Rosa',
            'profesor_id' => $profesor1->id,
        ]);

        $empresaSinAsignar = Empresa::factory()->create([
            'nombre' => 'Empresa Huérfana',
            'profesor_id' => null,
        ]);

        // Index normal muestra ambos y el nombre del profesor
        $response = $this->actingAs($admin)->get(route('empresas.index'));
        $response->assertOk();
        $response->assertSee('Rosa Tutor');
        $response->assertSee('Sin profesor asignado');
        $response->assertSee('Empresa Asignada Rosa');
        $response->assertSee('Empresa Huérfana');

        // Filtrar por profesor1
        $responseFiltroProf = $this->actingAs($admin)->get(route('empresas.index', ['profesor_id' => $profesor1->id]));
        $responseFiltroProf->assertOk();
        $responseFiltroProf->assertSee('Empresa Asignada Rosa');
        $responseFiltroProf->assertDontSee('Empresa Huérfana');

        // Filtrar por sin_asignar
        $responseSinProf = $this->actingAs($admin)->get(route('empresas.index', ['profesor_id' => 'sin_asignar']));
        $responseSinProf->assertOk();
        $responseSinProf->assertSee('Empresa Huérfana');
        $responseSinProf->assertDontSee('Empresa Asignada Rosa');
    }

    public function test_mensajes_create_form_displays_empresa_audience_and_profesores(): void
    {
        $admin = $this->createAdmin();
        [$pUser, $profesor] = $this->createProfesor('Carlos Dual', true);

        Empresa::factory()->create([
            'nombre' => 'Empresa de Carlos',
            'profesor_id' => $profesor->id,
            'is_active' => true,
        ]);

        Empresa::factory()->create([
            'nombre' => 'Empresa Sin Tutor',
            'profesor_id' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('mensajes.create'));
        $response->assertOk();
        $response->assertSee('Empresas colaboradoras');
        $response->assertSee('Carlos Dual');
        $response->assertSee('Empresas sin profesor asignado');
        $response->assertSee('Empresa de Carlos');
        $response->assertSee('Empresa Sin Tutor');
    }

    public function test_send_message_to_empresas_by_profesor(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        [$pUser1, $profesor1] = $this->createProfesor('Profesor Uno');
        [$pUser2, $profesor2] = $this->createProfesor('Profesor Dos');

        $empresa1 = Empresa::factory()->create([
            'nombre' => 'Empresa Uno',
            'email' => 'empresa1@test.com',
            'profesor_id' => $profesor1->id,
            'is_active' => true,
        ]);

        $empresa2 = Empresa::factory()->create([
            'nombre' => 'Empresa Dos',
            'email' => 'empresa2@test.com',
            'profesor_id' => $profesor2->id,
            'is_active' => true,
        ]);

        $payload = [
            'asunto' => 'Aviso a empresas del Profesor Uno',
            'cuerpo' => 'Contenido importante del comunicado oficial para empresas.',
            'tipo_destinatario' => 'empresas',
            'profesor_ids' => [$profesor1->id],
        ];

        $response = $this->actingAs($admin)->post(route('mensajes.store'), $payload);

        $mensaje = Mensaje::where('asunto', 'Aviso a empresas del Profesor Uno')->first();
        $this->assertNotNull($mensaje);
        $response->assertRedirect(route('mensajes.show', $mensaje));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mensajes', [
            'asunto' => 'Aviso a empresas del Profesor Uno',
            'tipo_destinatario' => 'empresas',
        ]);

        // Verificar que se creó notificación para empresa1 y no para empresa2
        $userEmpresa1 = User::where('email', 'empresa1@test.com')->first();
        $this->assertNotNull($userEmpresa1);
        $this->assertContains(User::ROLE_EMPRESA, $userEmpresa1->roles);

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $userEmpresa1->id,
            'titulo' => 'Aviso a empresas del Profesor Uno',
        ]);

        $userEmpresa2 = User::where('email', 'empresa2@test.com')->first();
        $this->assertNull($userEmpresa2);

        Mail::assertSent(NotificacionMailable::class, function ($mail) use ($userEmpresa1) {
            return $mail->hasTo('empresa1@test.com');
        });
    }

    public function test_send_message_to_empresas_sin_profesor_and_combination(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        [$pUser1, $profesor1] = $this->createProfesor('Profesor Uno');

        $empresaConProf = Empresa::factory()->create([
            'nombre' => 'Empresa Con Profesor',
            'email' => 'conprof@test.com',
            'profesor_id' => $profesor1->id,
            'is_active' => true,
        ]);

        $empresaSinProf = Empresa::factory()->create([
            'nombre' => 'Empresa Sin Asignar',
            'email' => 'sinprof@test.com',
            'profesor_id' => null,
            'is_active' => true,
        ]);

        // Enviar a la combinación: empresas del profesor1 Y empresas sin profesor asignado
        $payload = [
            'asunto' => 'Comunicado mixto',
            'cuerpo' => 'Mensaje para profesor 1 y sin profesor asignado.',
            'tipo_destinatario' => 'empresas',
            'profesor_ids' => [$profesor1->id],
            'sin_profesor' => '1',
        ];

        $response = $this->actingAs($admin)->post(route('mensajes.store'), $payload);

        $mensajeMixto = Mensaje::where('asunto', 'Comunicado mixto')->first();
        $this->assertNotNull($mensajeMixto);
        $response->assertRedirect(route('mensajes.show', $mensajeMixto));

        $user1 = User::where('email', 'conprof@test.com')->first();
        $user2 = User::where('email', 'sinprof@test.com')->first();

        $this->assertNotNull($user1);
        $this->assertNotNull($user2);

        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $user1->id]);
        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $user2->id]);

        Mail::assertSent(NotificacionMailable::class, 2);
    }
}
