<?php

namespace Tests\Feature;

use App\Models\CursoAcademico;
use App\Models\Ciclo;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\OfertaPractica;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfertaBusquedaEmpresaTest extends TestCase
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

    private function createCoordinadorDual(): User
    {
        return User::factory()->create([
            'name' => 'Coordinador Dual User',
            'email' => 'coord_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_COORDINADOR_DUAL],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    public function test_filtro_busca_ofertas_por_nombre_completo_de_empresa(): void
    {
        $admin = $this->createAdmin();

        $empresaA = Empresa::factory()->create(['nombre' => 'Innovaciones Granadinas S.L.', 'cif' => 'B18111222']);
        $empresaB = Empresa::factory()->create(['nombre' => 'Soluciones Andaluzas Tech', 'cif' => 'B18333444']);

        $ofertaA = OfertaPractica::factory()->create([
            'empresa_id' => $empresaA->id,
            'descripcion' => 'Desarrollo web con Laravel',
            'especialidad_requerida' => 'Desarrollo Web',
        ]);

        $ofertaB = OfertaPractica::factory()->create([
            'empresa_id' => $empresaB->id,
            'descripcion' => 'Administración de servidores cloud',
            'especialidad_requerida' => 'Sistemas y Redes',
        ]);

        $response = $this->actingAs($admin)->get(route('ofertas.index', ['search' => 'Innovaciones Granadinas S.L.']));

        $response->assertOk();
        $response->assertSee('Innovaciones Granadinas S.L.');
        $response->assertDontSee('Soluciones Andaluzas Tech');
    }

    public function test_filtro_busca_ofertas_por_nombre_parcial_de_empresa(): void
    {
        $admin = $this->createAdmin();

        $empresaA = Empresa::factory()->create(['nombre' => 'Vertigo Software Systems']);
        $empresaB = Empresa::factory()->create(['nombre' => 'Cívica Digital SL']);

        $ofertaA = OfertaPractica::factory()->create([
            'empresa_id' => $empresaA->id,
            'descripcion' => 'Frontend con Vue',
            'especialidad_requerida' => 'Informática',
        ]);

        $ofertaB = OfertaPractica::factory()->create([
            'empresa_id' => $empresaB->id,
            'descripcion' => 'Big data y analítica',
            'especialidad_requerida' => 'Informática',
        ]);

        // Búsqueda parcial "Vertigo"
        $response = $this->actingAs($admin)->get(route('ofertas.index', ['search' => 'Vertigo']));

        $response->assertOk();
        $response->assertSee('Vertigo Software Systems');
        $response->assertDontSee('Cívica Digital SL');
    }

    public function test_filtro_busca_ofertas_por_cif_de_empresa(): void
    {
        $admin = $this->createAdmin();

        $empresaA = Empresa::factory()->create(['nombre' => 'Empresa Alfa', 'cif' => 'B99887766']);
        $empresaB = Empresa::factory()->create(['nombre' => 'Empresa Beta', 'cif' => 'A11223344']);

        $ofertaA = OfertaPractica::factory()->create([
            'empresa_id' => $empresaA->id,
            'descripcion' => 'Puesto A',
        ]);

        $ofertaB = OfertaPractica::factory()->create([
            'empresa_id' => $empresaB->id,
            'descripcion' => 'Puesto B',
        ]);

        $response = $this->actingAs($admin)->get(route('ofertas.index', ['search' => 'B99887766']));

        $response->assertOk();
        $response->assertSee('Empresa Alfa');
        $response->assertDontSee('Empresa Beta');
    }

    public function test_filtro_no_muestra_ofertas_si_empresa_no_coincide(): void
    {
        $admin = $this->createAdmin();

        $empresa = Empresa::factory()->create(['nombre' => 'Google Cloud España']);
        OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'descripcion' => 'Soporte Cloud',
        ]);

        $response = $this->actingAs($admin)->get(route('ofertas.index', ['search' => 'Microsoft']));

        $response->assertOk();
        $response->assertDontSee('Google Cloud España');
        $response->assertSee('No hay ofertas de prácticas disponibles.');
    }

    public function test_profesor_puede_filtrar_ofertas_por_empresa_en_sus_grupos(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['is_active' => true],
            ['nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']
        );
        $familia = Familia::firstOrCreate(['codigo' => 'INF'], ['nombre' => 'Informática', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW'], ['nombre' => 'DAW', 'grado' => 'superior', 'familia_id' => $familia->id, 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Mañana', 'turno' => 'manana', 'is_active' => true]);
        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => '2º DAW Mañana',
            'is_active' => true,
        ]);

        $profesorUser = User::factory()->create([
            'roles' => [User::ROLE_PROFESOR],
            'consent_rgpd' => true,
        ]);
        $profesor = Profesor::create([
            'user_id' => $profesorUser->id,
            'especialidad' => 'Informática',
        ]);
        $profesor->gruposImpartidos()->attach($grupo->id);

        $empresaA = Empresa::factory()->create(['nombre' => 'Telefónica Tech Granada']);
        $empresaB = Empresa::factory()->create(['nombre' => 'Vodafone Empresas Granada']);

        $ofertaA = OfertaPractica::factory()->create(['empresa_id' => $empresaA->id]);
        $ofertaA->grupos()->attach($grupo->id);

        $ofertaB = OfertaPractica::factory()->create(['empresa_id' => $empresaB->id]);
        $ofertaB->grupos()->attach($grupo->id);

        $response = $this->actingAs($profesorUser)->get(route('ofertas.index', ['search' => 'Telefónica']));

        $response->assertOk();
        $response->assertSee('Telefónica Tech Granada');
        $response->assertDontSee('Vodafone Empresas Granada');
    }

    public function test_busqueda_mantiene_soporte_para_descripcion_y_especialidad(): void
    {
        $admin = $this->createAdmin();

        $empresa = Empresa::factory()->create(['nombre' => 'Empresa Genérica']);
        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'descripcion' => 'Desarrollador con conocimientos profundos de Kotlin',
            'especialidad_requerida' => 'Desarrollo Móvil',
        ]);

        // Buscar por término en la descripción
        $resDesc = $this->actingAs($admin)->get(route('ofertas.index', ['search' => 'Kotlin']));
        $resDesc->assertOk();
        $resDesc->assertSee('Desarrollo Móvil');

        // Buscar por especialidad
        $resEsp = $this->actingAs($admin)->get(route('ofertas.index', ['search' => 'Móvil']));
        $resEsp->assertOk();
        $resEsp->assertSee('Desarrollo Móvil');
    }
}
