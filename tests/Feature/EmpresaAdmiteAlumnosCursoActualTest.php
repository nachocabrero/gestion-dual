<?php

namespace Tests\Feature;

use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\OfertaPractica;
use App\Models\Profesor;
use App\Models\User;
use Tests\TestCase;

class EmpresaAdmiteAlumnosCursoActualTest extends TestCase
{
    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Empresa',
            'email' => 'admin_emp_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createProfesor(): User
    {
        $user = User::factory()->create([
            'name' => 'Profesor Tutor',
            'email' => 'prof_tutor_' . uniqid() . '@ieshlanz.es',
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

    public function test_empresa_creada_por_defecto_admite_alumnos_curso_actual(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'nombre' => 'Innovaciones Alhambra ' . uniqid(),
            'cif' => 'B' . rand(10000000, 99999999),
            'email' => 'contacto_' . uniqid() . '@alhambra.es',
            'telefono' => '958112233',
            'direccion' => 'Camino de Ronda, Granada',
            'admite_alumnos_curso_actual' => '1',
            'is_active' => 1,
        ];

        $response = $this->actingAs($admin)->post(route('empresas.store'), $payload);
        $response->assertSessionHasNoErrors();

        $empresa = Empresa::where('email', $payload['email'])->first();
        $this->assertNotNull($empresa);
        $this->assertTrue((bool) $empresa->admite_alumnos_curso_actual);
    }

    public function test_se_puede_crear_empresa_marcando_que_no_coge_alumnado(): void
    {
        $admin = $this->createAdmin();

        // Sin enviar el checkbox admite_alumnos_curso_actual o enviando '0'
        $payload = [
            'nombre' => 'Compañía Sin Plazas ' . uniqid(),
            'cif' => 'B' . rand(10000000, 99999999),
            'email' => 'sinplazas_' . uniqid() . '@empresa.es',
            'telefono' => '958445566',
            'direccion' => 'Polígono Juncaril',
            'admite_alumnos_curso_actual' => '0',
            'is_active' => 1,
        ];

        $response = $this->actingAs($admin)->post(route('empresas.store'), $payload);
        $response->assertSessionHasNoErrors();

        $empresa = Empresa::where('email', $payload['email'])->first();
        $this->assertNotNull($empresa);
        $this->assertFalse((bool) $empresa->admite_alumnos_curso_actual);
    }

    public function test_se_puede_actualizar_empresa_para_marcar_o_desmarcar_que_coge_alumnado(): void
    {
        $admin = $this->createAdmin();

        $empresa = Empresa::factory()->create([
            'nombre' => 'Tecnología Dinámica ' . uniqid(),
            'cif' => 'B' . rand(10000000, 99999999),
            'admite_alumnos_curso_actual' => true,
            'is_active' => true,
        ]);

        // Desmarcar checkbox (no enviar admite_alumnos_curso_actual)
        $response = $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => $empresa->nombre,
            'cif' => $empresa->cif,
            'email' => 'nuevo_' . uniqid() . '@dinamica.es',
        ]);
        $response->assertSessionHasNoErrors();

        $empresa->refresh();
        $this->assertFalse((bool) $empresa->admite_alumnos_curso_actual);

        // Volver a marcar checkbox (enviar '1')
        $response = $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => $empresa->nombre,
            'cif' => $empresa->cif,
            'email' => $empresa->email,
            'admite_alumnos_curso_actual' => '1',
        ]);
        $response->assertSessionHasNoErrors();

        $empresa->refresh();
        $this->assertTrue((bool) $empresa->admite_alumnos_curso_actual);
    }

    public function test_listado_empresas_ensombrece_las_que_no_cogen_alumnado_y_filtro_funciona(): void
    {
        $admin = $this->createAdmin();

        $empresaNoAdmite = Empresa::factory()->create([
            'nombre' => 'Empresa No Disponible ' . uniqid(),
            'admite_alumnos_curso_actual' => false,
            'is_active' => true,
        ]);

        $empresaSiAdmite = Empresa::factory()->create([
            'nombre' => 'Empresa Disponible ' . uniqid(),
            'admite_alumnos_curso_actual' => true,
            'is_active' => true,
        ]);

        // Por defecto (sin parámetro de filtro), NO deben salir las que no cogen alumnado
        $responseDefecto = $this->actingAs($admin)->get(route('empresas.index', ['search' => $empresaNoAdmite->nombre]));
        $responseDefecto->assertOk();
        $this->assertFalse($responseDefecto->viewData('empresas')->pluck('id')->contains($empresaNoAdmite->id));
        $responseDefecto->assertDontSee('No coge este curso');

        // La empresa que sí coge alumnado sí sale por defecto
        $responseSiDefecto = $this->actingAs($admin)->get(route('empresas.index', ['search' => $empresaSiAdmite->nombre]));
        $responseSiDefecto->assertOk();
        $this->assertTrue($responseSiDefecto->viewData('empresas')->pluck('id')->contains($empresaSiAdmite->id));
        $responseSiDefecto->assertSee($empresaSiAdmite->nombre);

        // Si se selecciona "todas", la no disponible sí aparece y sale ensombrecida
        $responseTodas = $this->actingAs($admin)->get(route('empresas.index', [
            'search' => $empresaNoAdmite->nombre,
            'admite_alumnos' => 'todas',
        ]));
        $responseTodas->assertOk();
        $this->assertTrue($responseTodas->viewData('empresas')->pluck('id')->contains($empresaNoAdmite->id));
        $responseTodas->assertSee('No coge este curso');
        $responseTodas->assertSee('opacity-60');

        // Filtrar solo las que no cogen alumnado
        $responseFiltroNo = $this->actingAs($admin)->get(route('empresas.index', [
            'search' => $empresaNoAdmite->nombre,
            'admite_alumnos' => '0',
        ]));
        $responseFiltroNo->assertOk();
        $this->assertTrue($responseFiltroNo->viewData('empresas')->pluck('id')->contains($empresaNoAdmite->id));
        $responseFiltroNo->assertSee('No coge este curso');

        // Filtrar explícitamente solo las que cogen alumnado
        $responseFiltroSi = $this->actingAs($admin)->get(route('empresas.index', [
            'search' => $empresaNoAdmite->nombre,
            'admite_alumnos' => '1',
        ]));
        $responseFiltroSi->assertOk();
        $this->assertFalse($responseFiltroSi->viewData('empresas')->pluck('id')->contains($empresaNoAdmite->id));
        $responseFiltroSi->assertDontSee('No coge este curso');
    }

    public function test_crear_oferta_de_practicas_excluye_empresas_que_no_cogen_alumnado(): void
    {
        $profesor = $this->createProfesor();

        $empresaNoAdmite = Empresa::factory()->create([
            'nombre' => 'Empresa Excluida Ofertas ' . uniqid(),
            'admite_alumnos_curso_actual' => false,
            'is_active' => true,
        ]);

        $empresaSiAdmite = Empresa::factory()->create([
            'nombre' => 'Empresa Incluida Ofertas ' . uniqid(),
            'admite_alumnos_curso_actual' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($profesor)->get(route('ofertas.create'));
        $response->assertOk();

        // Debe aparecer la empresa que sí coge alumnado
        $response->assertSee($empresaSiAdmite->nombre);
        // NO debe aparecer la empresa que no coge alumnado
        $response->assertDontSee($empresaNoAdmite->nombre);
    }

    public function test_crear_oferta_para_empresa_marca_automaticamente_que_si_coge_alumnado(): void
    {
        $profesor = $this->createProfesor();

        $empresa = Empresa::factory()->create([
            'nombre' => 'Empresa Reactivada ' . uniqid(),
            'admite_alumnos_curso_actual' => false,
            'is_active' => true,
        ]);

        $this->assertFalse((bool) $empresa->admite_alumnos_curso_actual);

        // Crear una oferta de prácticas para esta empresa mediante el controlador
        $payload = [
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Desarrollo de Software ' . uniqid(),
            'num_alumnos' => 2,
            'descripcion' => 'Pruebas de auto-activación de disponibilidad',
            'estado' => 'abierta',
        ];

        $response = $this->actingAs($profesor)->post(route('ofertas.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // La empresa debe haber sido marcada automáticamente con admite_alumnos_curso_actual = true
        $empresa->refresh();
        $this->assertTrue((bool) $empresa->admite_alumnos_curso_actual);
    }
}
