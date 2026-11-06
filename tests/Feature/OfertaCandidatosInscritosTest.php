<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\OfertaPractica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Tests\TestCase;

class OfertaCandidatosInscritosTest extends TestCase
{
    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Ofertas',
            'email' => 'admin_ofertas_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createEmpresa(): Empresa
    {
        return Empresa::factory()->create([
            'nombre' => 'Empresa Test ' . uniqid(),
            'is_active' => true,
        ]);
    }

    private function createAlumno(): Alumno
    {
        $user = User::factory()->create([
            'name' => 'Alumno Candidato ' . uniqid(),
            'email' => 'alumno_cand_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        return Alumno::factory()->create([
            'user_id' => $user->id,
        ]);
    }

    public function test_ofertas_index_muestra_candidatos_inscritos_junto_a_plazas(): void
    {
        $admin = $this->createAdmin();
        $empresa = $this->createEmpresa();
        $especialidad = 'Desarrollo Web FullStack ' . uniqid();

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => $admin->id,
            'creador_type' => User::class,
            'especialidad_requerida' => $especialidad,
            'descripcion' => 'Oferta de prueba para candidatos inscritos',
            'num_alumnos' => 4,
            'estado' => 'abierta',
        ]);

        $alumno1 = $this->createAlumno();
        $alumno2 = $this->createAlumno();

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno1->id,
            'estado' => 'pendiente',
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno2->id,
            'estado' => 'aceptado',
        ]);

        $response = $this->actingAs($admin)->get(route('ofertas.index', ['search' => $especialidad]));
        $response->assertOk();

        // Verifica plazas y candidatos inscritos
        $response->assertSee('4 plazas');
        $response->assertSee('2 candidatos inscritos');
    }

    public function test_ofertas_index_maneja_singular_y_plural_de_candidatos_y_plazas(): void
    {
        $admin = $this->createAdmin();
        $empresa = $this->createEmpresa();
        $especialidad = 'Administracion Sistemas ' . uniqid();

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => $admin->id,
            'creador_type' => User::class,
            'especialidad_requerida' => $especialidad,
            'descripcion' => 'Oferta de 1 plaza y 1 candidato',
            'num_alumnos' => 1,
            'estado' => 'abierta',
        ]);

        $alumno = $this->createAlumno();
        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        $response = $this->actingAs($admin)->get(route('ofertas.index', ['search' => $especialidad]));
        $response->assertOk();

        $response->assertSee('1 plaza');
        $response->assertSee('1 candidato inscrito');
    }

    public function test_ofertas_index_muestra_cero_candidatos_si_no_hay_inscritos(): void
    {
        $admin = $this->createAdmin();
        $empresa = $this->createEmpresa();
        $especialidad = 'Ciberseguridad Lanz ' . uniqid();

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => $admin->id,
            'creador_type' => User::class,
            'especialidad_requerida' => $especialidad,
            'descripcion' => 'Oferta sin candidatos',
            'num_alumnos' => 2,
            'estado' => 'abierta',
        ]);

        $response = $this->actingAs($admin)->get(route('ofertas.index', ['search' => $especialidad]));
        $response->assertOk();

        $response->assertSee('2 plazas');
        $response->assertSee('0 candidatos inscritos');
    }

    public function test_ofertas_index_no_cuenta_solicitudes_retiradas(): void
    {
        $admin = $this->createAdmin();
        $empresa = $this->createEmpresa();
        $especialidad = 'Redes Telematicas ' . uniqid();

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => $admin->id,
            'creador_type' => User::class,
            'especialidad_requerida' => $especialidad,
            'descripcion' => 'Oferta con candidato activo y retirado',
            'num_alumnos' => 2,
            'estado' => 'abierta',
        ]);

        $alumnoActivo = $this->createAlumno();
        $alumnoRetirado = $this->createAlumno();

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoActivo->id,
            'estado' => 'pendiente',
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoRetirado->id,
            'estado' => 'retirado',
        ]);

        $response = $this->actingAs($admin)->get(route('ofertas.index', ['search' => $especialidad]));
        $response->assertOk();

        // Debe contar únicamente 1 candidato inscrito (el activo, no el retirado)
        $response->assertSee('1 candidato inscrito');
        $response->assertDontSee('2 candidatos inscritos');
    }

    public function test_modelo_oferta_practica_relacion_y_accessor_candidatos(): void
    {
        $admin = $this->createAdmin();
        $empresa = $this->createEmpresa();

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => $admin->id,
            'creador_type' => User::class,
            'especialidad_requerida' => 'Especialidad Modelo Test',
            'num_alumnos' => 3,
            'estado' => 'abierta',
        ]);

        $alumno1 = $this->createAlumno();
        $alumno2 = $this->createAlumno();

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno1->id,
            'estado' => 'aceptado',
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumno2->id,
            'estado' => 'retirado',
        ]);

        // La relación candidatos solo debe incluir la solicitud activa
        $this->assertEquals(1, $oferta->candidatos()->count());
        $this->assertEquals(1, $oferta->candidatos_inscritos_count);

        // Si se carga con withCount('candidatos')
        $ofertaConCount = OfertaPractica::withCount('candidatos')->find($oferta->id);
        $this->assertEquals(1, $ofertaConCount->candidatos_count);
        $this->assertEquals(1, $ofertaConCount->candidatos_inscritos_count);
    }
}

