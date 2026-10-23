<?php

namespace Tests\Feature;

use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\Empresa;
use App\Models\Mensaje;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresasActivasAsignaturasDamDawTest extends TestCase
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

    public function test_empresas_index_shows_only_active_by_default(): void
    {
        $admin = $this->createAdmin();

        $activa = Empresa::factory()->create([
            'nombre' => 'Tech Activa SL',
            'cif' => 'B11111111',
            'is_active' => true,
        ]);

        $inactiva = Empresa::factory()->create([
            'nombre' => 'Cerrada Inactiva SA',
            'cif' => 'A22222222',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('empresas.index'));

        $response->assertStatus(200);
        $response->assertSee('Tech Activa SL');
        $response->assertDontSee('Cerrada Inactiva SA');
    }

    public function test_empresas_index_can_filter_inactive_and_all(): void
    {
        $admin = $this->createAdmin();

        $activa = Empresa::factory()->create([
            'nombre' => 'Tech Activa SL',
            'cif' => 'B11111111',
            'is_active' => true,
        ]);

        $inactiva = Empresa::factory()->create([
            'nombre' => 'Cerrada Inactiva SA',
            'cif' => 'A22222222',
            'is_active' => false,
        ]);

        // Filtrar inactivas
        $responseInactivas = $this->actingAs($admin)->get(route('empresas.index', ['active' => '0']));
        $responseInactivas->assertStatus(200);
        $responseInactivas->assertSee('Cerrada Inactiva SA');
        $responseInactivas->assertDontSee('Tech Activa SL');

        // Filtrar todas
        $responseTodas = $this->actingAs($admin)->get(route('empresas.index', ['active' => 'todas']));
        $responseTodas->assertStatus(200);
        $responseTodas->assertSee('Tech Activa SL');
        $responseTodas->assertSee('Cerrada Inactiva SA');
    }

    public function test_empresas_index_renders_status_icon_instead_of_letter_avatar(): void
    {
        $admin = $this->createAdmin();

        $activa = Empresa::factory()->create([
            'nombre' => 'Zeta Tech',
            'cif' => 'B99999999',
            'is_active' => true,
        ]);

        $inactiva = Empresa::factory()->create([
            'nombre' => 'Alfa Inactiva',
            'cif' => 'A88888888',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('empresas.index', ['active' => 'todas']));

        $response->assertStatus(200);
        // Debe mostrar el icono con título y svg de estado
        $response->assertSee('title="Empresa activa"', false);
        $response->assertSee('title="Empresa inactiva"', false);

        // No debe mostrar la caja con fondo degradado y la letra inicial
        $response->assertDontSee('bg-gradient-to-br from-emerald-500 to-teal-600');

        // No debe mostrar la columna Estado en la tabla
        $response->assertDontSee('<th>Estado</th>', false);
    }

    public function test_inactive_companies_not_included_in_messages_create(): void
    {
        $admin = $this->createAdmin();

        $profUser = User::factory()->create(['name' => 'Profesor Seguimiento', 'roles' => [User::ROLE_PROFESOR], 'is_active' => true]);
        $prof = Profesor::factory()->create(['user_id' => $profUser->id]);

        $activa = Empresa::factory()->create([
            'nombre' => 'Empresa Activa Valida',
            'email' => 'activa@empresa.com',
            'profesor_id' => $prof->id,
            'is_active' => true,
        ]);

        $inactiva = Empresa::factory()->create([
            'nombre' => 'Empresa Inactiva No Valida',
            'email' => 'inactiva@empresa.com',
            'profesor_id' => $prof->id,
            'is_active' => false,
        ]);

        $inactivaSinProf = Empresa::factory()->create([
            'nombre' => 'Empresa Inactiva Sin Prof',
            'email' => 'inactiva_sinprof@empresa.com',
            'profesor_id' => null,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('mensajes.create'));

        $response->assertStatus(200);
        $response->assertSee('Empresa Activa Valida');
        $response->assertDontSee('Empresa Inactiva No Valida');
        $response->assertDontSee('Empresa Inactiva Sin Prof');
    }

    public function test_inactive_companies_cannot_receive_messages_in_store(): void
    {
        $admin = $this->createAdmin();

        $inactiva = Empresa::factory()->create([
            'nombre' => 'Empresa Inactiva Mandar',
            'email' => 'inactiva_mandar@empresa.com',
            'is_active' => false,
        ]);

        $activa = Empresa::factory()->create([
            'nombre' => 'Empresa Activa Mandar',
            'email' => 'activa_mandar@empresa.com',
            'is_active' => true,
        ]);

        // Intentar mandar a la empresa inactiva
        $response = $this->actingAs($admin)->post(route('mensajes.store'), [
            'asunto' => 'Comunicado Importante',
            'cuerpo' => 'Cuerpo del mensaje para empresas',
            'tipo_destinatario' => 'empresas',
            'empresa_ids' => [$inactiva->id],
        ]);

        // Al ser inactiva, queda excluida y no hay destinatarios válidos
        $response->assertSessionHas('error');

        // Ahora mandar a la activa
        $responseOk = $this->actingAs($admin)->post(route('mensajes.store'), [
            'asunto' => 'Comunicado Importante',
            'cuerpo' => 'Cuerpo del mensaje para empresas',
            'tipo_destinatario' => 'empresas',
            'empresa_ids' => [$activa->id],
        ]);

        $responseOk->assertSessionHas('success');
        $mensaje = Mensaje::where('asunto', 'Comunicado Importante')->first();
        $this->assertNotNull($mensaje);
        $responseOk->assertRedirect(route('mensajes.show', $mensaje));
        $this->assertDatabaseHas('mensajes', [
            'asunto' => 'Comunicado Importante',
        ]);

        // Comprobar que no se creó usuario receptor para la empresa inactiva
        $this->assertDatabaseMissing('users', [
            'email' => 'inactiva_mandar@empresa.com',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'activa_mandar@empresa.com',
        ]);
    }

    public function test_academic_structure_dam_and_daw_subjects(): void
    {
        (new \Database\Seeders\AcademicStructureSeeder())->run();

        $cicloDam = Ciclo::where('codigo', 'DAM')->first();
        $this->assertNotNull($cicloDam);

        $asignaturasDam = Asignatura::where('ciclo_id', $cicloDam->id)->pluck('nombre')->toArray();

        $esperadas1DAM = [
            'Programación',
            'Bases de Datos',
            'Sistemas Informáticos',
            'Lenguajes de Marcas y Sistemas de Gestión de Información',
            'Entornos de Desarrollo',
            'Itinerario Personal para la Empleabilidad I (IPE I)',
            'Digitalización Aplicada a los Sectores Productivos',
            'Sostenibilidad Aplicada al Sistema Productivo',
        ];

        $esperadas2DAM = [
            'Acceso a Datos',
            'Desarrollo de Interfaces',
            'Programación Multimedia y Dispositivos Móviles',
            'Programación de Servicios y Procesos',
            'Sistemas de Gestión Empresarial',
            'Inglés Profesional (o módulo técnico equivalente de idiomas aplicado al entorno laboral)',
            'Itinerario Personal para la Empleabilidad II (IPE II)',
            'Proyecto Intermodular',
        ];

        foreach ($esperadas1DAM as $asig) {
            $this->assertContains($asig, $asignaturasDam, "Falta la asignatura de 1º DAM: {$asig}");
        }

        foreach ($esperadas2DAM as $asig) {
            $this->assertContains($asig, $asignaturasDam, "Falta la asignatura de 2º DAM: {$asig}");
        }

        // DAM debe tener exactamente 16 asignaturas
        $this->assertCount(16, $asignaturasDam);

        // DAW debe incluir las 8 de 1º
        $cicloDaw = Ciclo::where('codigo', 'DAW')->first();
        $this->assertNotNull($cicloDaw);
        $asignaturasDaw = Asignatura::where('ciclo_id', $cicloDaw->id)->pluck('nombre')->toArray();

        foreach ($esperadas1DAM as $asig) {
            $this->assertContains($asig, $asignaturasDaw, "Falta la asignatura de 1º DAW: {$asig}");
        }
    }
}
