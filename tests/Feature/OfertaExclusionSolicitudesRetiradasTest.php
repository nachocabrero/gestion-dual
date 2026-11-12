<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\OfertaPractica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfertaExclusionSolicitudesRetiradasTest extends TestCase
{
    use RefreshDatabase;

    private function createProfesor(): User
    {
        return User::factory()->create([
            'name' => 'Profesor Gestor',
            'email' => 'profesor_gestor_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
        ]);
    }

    private function createAlumno(string $name = 'Alumno Test'): Alumno
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => 'alumno_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
        ]);

        return Alumno::create([
            'user_id' => $user->id,
            'no_hace_practicas_este_ano' => false,
        ]);
    }

    public function test_oferta_show_and_compartida_exclude_retired_solicitudes(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create(['nombre' => 'Innovacion Tech']);

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'creador_type' => User::class,
            'titulo' => 'Desarrollo Web Dual',
            'descripcion' => 'Puesto en desarrollo',
            'especialidad_requerida' => 'Desarrollo Web',
            'num_alumnos' => 2,
            'curso_academico_id' => $curso->id,
            'estado' => 'activa',
            'token' => 'test-token-seguro-12345',
        ]);

        $alumnoActivo1 = $this->createAlumno('Carlos Activo Uno');
        $alumnoActivo2 = $this->createAlumno('Beatriz Activa Dos');
        $alumnoRetirado = $this->createAlumno('Ruben Desasignado Retirado');

        // Solicitudes: 2 aceptadas, 1 retirada
        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoActivo1->id,
            'estado' => 'aceptado',
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoActivo2->id,
            'estado' => 'aceptado',
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoRetirado->id,
            'estado' => 'retirado',
        ]);

        // 1. Verificar ofertas.show para profesor
        $showRes = $this->actingAs($profesor)->get(route('ofertas.show', $oferta));
        $showRes->assertOk();

        // El conteo activo en el badge debe ser 2 (no 3)
        $showRes->assertSee('Carlos Activo Uno');
        $showRes->assertSee('Beatriz Activa Dos');
        // El alumno retirado no está en la tabla de asignados activos
        $showRes->assertSee('Ver alumnos desasignados previamente (1)');
        $showRes->assertSee('Ruben Desasignado Retirado');

        // 2. Verificar vista pública de la empresa (ofertas.compartida)
        $compRes = $this->get(route('ofertas.compartida', $oferta->token));
        $compRes->assertOk();

        // El alumno retirado NUNCA debe verse en la vista pública
        $compRes->assertSee('Carlos Activo Uno');
        $compRes->assertSee('Beatriz Activa Dos');
        $compRes->assertDontSee('Ruben Desasignado Retirado');

        // 3. Descarga individual de CV de alumno retirado en la vista pública debe dar 404
        $cvRes = $this->get(route('ofertas.compartida.descargar-cv', [
            'token' => $oferta->token,
            'alumno' => $alumnoRetirado,
        ]));
        $cvRes->assertNotFound();

        // 4. En el formulario de asignación (enviarForm), solo los 2 activos están marcados
        $enviarRes = $this->actingAs($profesor)->get(route('ofertas.enviar-form', $oferta));
        $enviarRes->assertOk();
    }

    public function test_descargar_zip_cvs_only_includes_active_candidates(): void
    {
        Storage::fake('public');

        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create(['nombre' => 'Software Corp']);

        $oferta = OfertaPractica::create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'creador_type' => User::class,
            'titulo' => 'Desarrollador Web',
            'descripcion' => 'Prácticas',
            'especialidad_requerida' => 'Desarrollo Web',
            'num_alumnos' => 2,
            'curso_academico_id' => $curso->id,
            'estado' => 'activa',
            'token' => 'token-zip-test-99999',
        ]);

        $alumnoActivo = $this->createAlumno('Alumno Con CV Activo');
        $alumnoRetirado = $this->createAlumno('Alumno Con CV Retirado');

        // Subir CVs ficticios
        $pathActivo = 'cvs/cv_activo.pdf';
        $pathRetirado = 'cvs/cv_retirado.pdf';
        Storage::disk('public')->put($pathActivo, 'Contenido CV Activo');
        Storage::disk('public')->put($pathRetirado, 'Contenido CV Retirado');

        $alumnoActivo->user->update(['cv_url' => $pathActivo]);
        $alumnoRetirado->user->update(['cv_url' => $pathRetirado]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoActivo->id,
            'estado' => 'aceptado',
        ]);

        SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoRetirado->id,
            'estado' => 'retirado',
        ]);

        // Descarga ZIP como profesor
        $zipRes = $this->actingAs($profesor)->get(route('ofertas.descargar-cvs', $oferta));
        $zipRes->assertOk();
        $this->assertEquals('application/zip', $zipRes->headers->get('content-type'));

        // Descarga ZIP desde la vista pública compartida
        $zipCompRes = $this->get(route('ofertas.compartida.descargar-cvs', $oferta->token));
        $zipCompRes->assertOk();
        $this->assertEquals('application/zip', $zipCompRes->headers->get('content-type'));
    }
}

