<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\OfertaPractica;
use App\Models\Practica;
use App\Models\SolicitudPractica;
use App\Models\TutorLaboral;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmpresaAccesoExclusivoOfertasYPracticasTest extends TestCase
{
    private function createAcademicContext(): CursoAcademico
    {
        return CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );
    }

    private function createEmpresaWithUser(string $prefix = 'emp_a'): array
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Empresa ' . strtoupper($prefix),
            'cif' => 'B' . rand(10000000, 99999999),
            'email' => "contacto_{$prefix}@test.com",
            'is_active' => true,
            'admite_alumnos_curso_actual' => true,
        ]);

        $user = User::factory()->create([
            'name' => "Usuario {$prefix}",
            'email' => "usuario_{$prefix}@test.com",
            'roles' => [User::ROLE_EMPRESA],
            'empresa_id' => $empresa->id,
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        return [$empresa, $user];
    }

    public function test_empresa_user_only_sees_own_company_ofertas_in_index(): void
    {
        $this->createAcademicContext();

        [$empresaA, $userA] = $this->createEmpresaWithUser('alpha');
        [$empresaB, $userB] = $this->createEmpresaWithUser('beta');

        $ofertaA = OfertaPractica::factory()->create([
            'empresa_id' => $empresaA->id,
            'especialidad_requerida' => 'Desarrollador Web Alpha',
            'estado' => 'abierta',
        ]);

        $ofertaB = OfertaPractica::factory()->create([
            'empresa_id' => $empresaB->id,
            'especialidad_requerida' => 'Desarrollador Web Beta',
            'estado' => 'abierta',
        ]);

        $response = $this->actingAs($userA)->get(route('ofertas.index'));
        $response->assertOk();
        $response->assertSee('Desarrollador Web Alpha');
        $response->assertDontSee('Desarrollador Web Beta');
    }

    public function test_empresa_user_cannot_access_other_company_oferta_detail(): void
    {
        $this->createAcademicContext();

        [$empresaA, $userA] = $this->createEmpresaWithUser('gamma');
        [$empresaB, $userB] = $this->createEmpresaWithUser('delta');

        $ofertaA = OfertaPractica::factory()->create([
            'empresa_id' => $empresaA->id,
            'especialidad_requerida' => 'Especialista Gamma',
        ]);

        $ofertaB = OfertaPractica::factory()->create([
            'empresa_id' => $empresaB->id,
            'especialidad_requerida' => 'Especialista Delta',
        ]);

        // Puede acceder a su propia oferta
        $resPropia = $this->actingAs($userA)->get(route('ofertas.show', $ofertaA));
        $resPropia->assertOk();
        $resPropia->assertSee('Especialista Gamma');

        // Intento de acceso a la oferta de otra empresa responde 403
        $resAjena = $this->actingAs($userA)->get(route('ofertas.show', $ofertaB));
        $resAjena->assertForbidden();
    }

    public function test_empresa_user_cannot_download_cv_or_view_solicitudes_of_other_company_oferta(): void
    {
        Storage::fake('public');
        $this->createAcademicContext();

        [$empresaA, $userA] = $this->createEmpresaWithUser('emp1');
        [$empresaB, $userB] = $this->createEmpresaWithUser('emp2');

        $alumnoUser = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'cv_url' => 'cvs/alumno_test.pdf',
        ]);
        Storage::disk('public')->put('cvs/alumno_test.pdf', 'Contenido CV PDF');

        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);

        $ofertaB = OfertaPractica::factory()->create([
            'empresa_id' => $empresaB->id,
        ]);

        SolicitudPractica::create([
            'oferta_id' => $ofertaB->id,
            'alumno_id' => $alumno->id,
            'estado' => 'aceptado',
        ]);

        // Intento de descargar CV individual de oferta ajena responde 403
        $resCv = $this->actingAs($userA)->get(route('ofertas.descargar-cv-alumno', [$ofertaB, $alumno]));
        $resCv->assertForbidden();

        // Intento de descargar ZIP de CVs de oferta ajena responde 403
        $resZip = $this->actingAs($userA)->get(route('ofertas.descargar-cvs', $ofertaB));
        $resZip->assertForbidden();

        // Intento de ver solicitudes de oferta ajena responde 403
        $resSol = $this->actingAs($userA)->get(route('ofertas.solicitudes', $ofertaB));
        $resSol->assertForbidden();
    }

    public function test_empresa_user_cannot_create_oferta_for_another_company(): void
    {
        $this->createAcademicContext();

        [$empresaA, $userA] = $this->createEmpresaWithUser('crea1');
        [$empresaB, $userB] = $this->createEmpresaWithUser('crea2');

        // En el formulario de creación, solo ve su empresa
        $resForm = $this->actingAs($userA)->get(route('ofertas.create'));
        $resForm->assertOk();
        $resForm->assertSee($empresaA->nombre);
        $resForm->assertDontSee($empresaB->nombre);

        // Intento de crear oferta para otra empresa es rechazado con 403
        $resStore = $this->actingAs($userA)->post(route('ofertas.store'), [
            'empresa_id' => $empresaB->id,
            'especialidad_requerida' => 'Intento Hack',
            'num_alumnos' => 2,
        ]);
        $resStore->assertForbidden();

        // Creación para su propia empresa funciona correctamente
        $resStoreOk = $this->actingAs($userA)->post(route('ofertas.store'), [
            'empresa_id' => $empresaA->id,
            'especialidad_requerida' => 'Desarrollador Empresa A',
            'num_alumnos' => 2,
        ]);
        $resStoreOk->assertRedirect();
        $this->assertDatabaseHas('ofertas_practicas', [
            'empresa_id' => $empresaA->id,
            'especialidad_requerida' => 'Desarrollador Empresa A',
        ]);
    }

    public function test_empresa_user_only_sees_own_company_practicas_in_index(): void
    {
        $curso = $this->createAcademicContext();

        [$empresaA, $userA] = $this->createEmpresaWithUser('prac1');
        [$empresaB, $userB] = $this->createEmpresaWithUser('prac2');

        $alumnoA = Alumno::factory()->create();
        $alumnoB = Alumno::factory()->create();

        $practicaA = Practica::create([
            'alumno_id' => $alumnoA->id,
            'empresa_id' => $empresaA->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => '2026-10-01',
            'fecha_fin' => '2027-02-15',
            'horas_acumuladas' => 100,
            'convenio_firmado' => true,
        ]);

        $practicaB = Practica::create([
            'alumno_id' => $alumnoB->id,
            'empresa_id' => $empresaB->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => '2026-10-01',
            'fecha_fin' => '2027-02-15',
            'horas_acumuladas' => 120,
            'convenio_firmado' => true,
        ]);

        $response = $this->actingAs($userA)->get(route('practicas.index'));
        $response->assertOk();
        $response->assertSee($alumnoA->user->name);
        $response->assertDontSee($alumnoB->user->name);
    }

    public function test_empresa_user_cannot_access_other_company_practica_detail(): void
    {
        $curso = $this->createAcademicContext();

        [$empresaA, $userA] = $this->createEmpresaWithUser('det1');
        [$empresaB, $userB] = $this->createEmpresaWithUser('det2');

        $alumnoA = Alumno::factory()->create();
        $alumnoB = Alumno::factory()->create();

        $practicaA = Practica::create([
            'alumno_id' => $alumnoA->id,
            'empresa_id' => $empresaA->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => '2026-10-01',
            'fecha_fin' => '2027-02-15',
            'horas_acumuladas' => 100,
            'convenio_firmado' => true,
        ]);

        $practicaB = Practica::create([
            'alumno_id' => $alumnoB->id,
            'empresa_id' => $empresaB->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => '2026-10-01',
            'fecha_fin' => '2027-02-15',
            'horas_acumuladas' => 120,
            'convenio_firmado' => true,
        ]);

        // Práctica de su propia empresa: acceso 200 OK
        $resA = $this->actingAs($userA)->get(route('practicas.show', $practicaA));
        $resA->assertOk();
        $resA->assertSee($empresaA->nombre);

        // Práctica de otra empresa: 403 Forbidden
        $resB = $this->actingAs($userA)->get(route('practicas.show', $practicaB));
        $resB->assertForbidden();
    }

    public function test_empresa_user_cannot_create_edit_update_or_delete_academic_practicas(): void
    {
        $curso = $this->createAcademicContext();

        [$empresaA, $userA] = $this->createEmpresaWithUser('crud1');
        $alumno = Alumno::factory()->create();

        $practica = Practica::create([
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresaA->id,
            'curso_academico_id' => $curso->id,
            'fecha_inicio' => '2026-10-01',
            'fecha_fin' => '2027-02-15',
            'horas_acumuladas' => 80,
            'convenio_firmado' => true,
        ]);

        $this->actingAs($userA);

        // create() formulario
        $this->get(route('practicas.create'))->assertForbidden();

        // store()
        $this->post(route('practicas.store'), [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresaA->id,
            'fecha_inicio' => '2026-10-01',
        ])->assertForbidden();

        // edit()
        $this->get(route('practicas.edit', $practica))->assertForbidden();

        // update()
        $this->put(route('practicas.update', $practica), [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresaA->id,
            'fecha_inicio' => '2026-10-01',
        ])->assertForbidden();

        // destroy()
        $this->delete(route('practicas.destroy', $practica))->assertForbidden();

        // actualizarHoras()
        $this->post(route('practicas.horas', $practica), [
            'horas_acumuladas' => 200,
        ])->assertForbidden();
    }

    public function test_empresa_user_resolves_empresa_via_fallback_mechanisms(): void
    {
        $this->createAcademicContext();

        // Caso 1: Resolución directa por empresa_id
        $empresaDirecta = Empresa::factory()->create(['cif' => 'B11111111']);
        $userDirecto = User::factory()->create([
            'roles' => [User::ROLE_EMPRESA],
            'empresa_id' => $empresaDirecta->id,
        ]);
        $this->assertEquals($empresaDirecta->id, $userDirecto->empresa?->id);

        // Caso 2: Fallback por email idéntico al de la Empresa
        $empresaEmail = Empresa::factory()->create([
            'cif' => 'B22222222',
            'email' => 'corporativo@innova.com',
        ]);
        $userEmail = User::factory()->create([
            'roles' => [User::ROLE_EMPRESA],
            'email' => 'corporativo@innova.com',
            'empresa_id' => null,
        ]);
        $this->assertEquals($empresaEmail->id, $userEmail->empresa?->id);

        // Caso 3: Fallback por email de TutorLaboral
        $empresaTutor = Empresa::factory()->create(['cif' => 'B33333333']);
        $tutor = TutorLaboral::create([
            'empresa_id' => $empresaTutor->id,
            'nombre' => 'Tutor Laboral Test',
            'email' => 'tutor.laboral@innova.com',
        ]);
        $userTutor = User::factory()->create([
            'roles' => [User::ROLE_EMPRESA],
            'email' => 'tutor.laboral@innova.com',
            'empresa_id' => null,
        ]);
        $this->assertEquals($empresaTutor->id, $userTutor->empresa?->id);
    }
}
