<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Empresa;
use App\Models\OfertaPractica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Tests\TestCase;

class OfertaPracticaTest extends TestCase
{
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

    private function createProfesor(): User
    {
        return User::factory()->create([
            'name' => 'Profesor User',
            'email' => 'profesor_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createAlumno(): User
    {
        return User::factory()->create([
            'name' => 'Alumno User',
            'email' => 'alumno_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    private function createEmpresaUser(): User
    {
        return User::factory()->create([
            'name' => 'Empresa User',
            'email' => 'empresa_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_EMPRESA],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    public function test_admin_can_view_ofertas_index(): void
    {
        $admin = $this->createAdmin();
        OfertaPractica::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('ofertas.index'));

        $response->assertOk();
        $response->assertViewHas('ofertas');
    }

    public function test_profesor_can_view_ofertas(): void
    {
        $profesor = $this->createProfesor();
        OfertaPractica::factory()->count(2)->create();

        $response = $this->actingAs($profesor)->get(route('ofertas.index'));

        $response->assertOk();
    }

    public function test_alumno_can_view_ofertas(): void
    {
        $alumno = $this->createAlumno();
        $alumnoModel = \App\Models\Alumno::factory()->create(['user_id' => $alumno->id]);
        OfertaPractica::factory()->create(['estado' => 'activa']);

        $response = $this->actingAs($alumno)->get(route('ofertas.index'));

        $response->assertOk();
    }

    public function test_profesor_can_create_oferta(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($profesor)->post(route('ofertas.store'), [
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Desarrollo Web',
            'num_alumnos' => 2,
            'descripcion' => 'Buscamos 2 alumnos para desarrollo web',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ofertas_practicas', [
            'especialidad_requerida' => 'Desarrollo Web',
            'num_alumnos' => 2,
            'estado' => 'pendiente',
        ]);
    }

    public function test_profesor_can_create_oferta_with_grupos(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $grupo1 = \App\Models\Grupo::factory()->create();
        $grupo2 = \App\Models\Grupo::factory()->create();

        $response = $this->actingAs($profesor)->post(route('ofertas.store'), [
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'DAM',
            'num_alumnos' => 3,
            'grupo_ids' => [$grupo1->id, $grupo2->id],
        ]);

        $response->assertRedirect();
        $oferta = OfertaPractica::where('especialidad_requerida', 'DAM')->firstOrFail();
        $this->assertSame([$grupo1->id, $grupo2->id], $oferta->grupos()->pluck('grupos.id')->sort()->values()->all());
        $this->assertDatabaseHas('grupo_oferta', ['oferta_practica_id' => $oferta->id, 'grupo_id' => $grupo1->id]);
        $this->assertDatabaseHas('grupo_oferta', ['oferta_practica_id' => $oferta->id, 'grupo_id' => $grupo2->id]);
    }

    public function test_create_view_solo_muestra_grupos_del_curso_actual(): void
    {
        $profesor = $this->createProfesor();
        \App\Models\CursoAcademico::query()->update(['is_active' => false]);
        $cursoActual = \App\Models\CursoAcademico::factory()->create(['is_active' => true, 'fecha_inicio' => now()->subMonths(2)]);
        $cursoAnterior = \App\Models\CursoAcademico::factory()->create(['is_active' => false, 'fecha_inicio' => now()->subYear()]);

        $grupoActual = \App\Models\Grupo::factory()->create(['curso_academico_id' => $cursoActual->id, 'is_active' => true]);
        $grupoAnterior = \App\Models\Grupo::factory()->create(['curso_academico_id' => $cursoAnterior->id, 'is_active' => true]);

        $response = $this->actingAs($profesor)->get(route('ofertas.create'));

        $response->assertOk();
        $response->assertSee($grupoActual->nombre, false);
        $response->assertDontSee($grupoAnterior->nombre, false);
    }

    public function test_profesor_can_edit_oferta_y_actualizar_grupos(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'pendiente',
        ]);

        $grupo1 = \App\Models\Grupo::factory()->create();
        $grupo2 = \App\Models\Grupo::factory()->create();
        $grupo3 = \App\Models\Grupo::factory()->create();
        $oferta->grupos()->attach([$grupo1->id, $grupo2->id]);

        // La vista de edición prestablece los grupos actuales
        $response = $this->actingAs($profesor)->get(route('ofertas.edit', $oferta));
        $response->assertOk();

        // Actualización: se sustituyen los grupos (grupo1 se quita, grupo3 entra)
        $response = $this->actingAs($profesor)->put(route('ofertas.update', $oferta), [
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'DAM',
            'num_alumnos' => 2,
            'estado' => 'activa',
            'grupo_ids' => [$grupo2->id, $grupo3->id],
        ]);

        $response->assertRedirect();
        $this->assertSame([$grupo2->id, $grupo3->id], $oferta->fresh()->grupos()->pluck('grupos.id')->sort()->values()->all());
        $this->assertDatabaseHas('grupo_oferta', ['oferta_practica_id' => $oferta->id, 'grupo_id' => $grupo3->id]);
        $this->assertDatabaseMissing('grupo_oferta', ['oferta_practica_id' => $oferta->id, 'grupo_id' => $grupo1->id]);
    }

    public function test_profesor_puede_enviar_oferta_a_alumnos_de_los_grupos(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $cursoActual = \App\Models\CursoAcademico::factory()->create(['is_active' => true, 'fecha_inicio' => now()->subMonths(2)]);

        $grupo = \App\Models\Grupo::factory()->create(['curso_academico_id' => $cursoActual->id, 'is_active' => true]);

        $userA = $this->createAlumno();
        $userB = $this->createAlumno();
        $alumnoA = Alumno::factory()->create(['user_id' => $userA->id]);
        $alumnoB = Alumno::factory()->create(['user_id' => $userB->id]);
        $grupo->alumnos()->sync([$alumnoA->id => ['curso_academico_id' => $cursoActual->id], $alumnoB->id => ['curso_academico_id' => $cursoActual->id]]);

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'pendiente',
        ]);
        $oferta->grupos()->attach($grupo->id);

        $response = $this->actingAs($profesor)->post(route('ofertas.enviar', $oferta), [
            'alumno_ids' => [$alumnoA->id, $alumnoB->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame('activa', $oferta->fresh()->estado);
        // Ambos alumnos reciben notificación in-app
        $this->assertEquals(1, \App\Models\Notificacion::where('usuario_id', $userA->id)->where('tipo', 'oferta_nueva')->count());
        $this->assertEquals(1, \App\Models\Notificacion::where('usuario_id', $userB->id)->where('tipo', 'oferta_nueva')->count());
    }

    public function test_profesor_puede_enviar_oferta_solo_a_algunos_alumnos(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $cursoActual = \App\Models\CursoAcademico::factory()->create(['is_active' => true, 'fecha_inicio' => now()->subMonths(2)]);

        $grupo = \App\Models\Grupo::factory()->create(['curso_academico_id' => $cursoActual->id, 'is_active' => true]);

        $userA = $this->createAlumno();
        $userB = $this->createAlumno();
        $alumnoA = Alumno::factory()->create(['user_id' => $userA->id]);
        $alumnoB = Alumno::factory()->create(['user_id' => $userB->id]);
        $grupo->alumnos()->sync([$alumnoA->id => ['curso_academico_id' => $cursoActual->id], $alumnoB->id => ['curso_academico_id' => $cursoActual->id]]);

        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'pendiente',
        ]);
        $oferta->grupos()->attach($grupo->id);

        // Solo se envía al alumno A
        $response = $this->actingAs($profesor)->post(route('ofertas.enviar', $oferta), [
            'alumno_ids' => [$alumnoA->id],
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, \App\Models\Notificacion::where('usuario_id', $userA->id)->where('tipo', 'oferta_nueva')->count());
        $this->assertEquals(0, \App\Models\Notificacion::where('usuario_id', $userB->id)->where('tipo', 'oferta_nueva')->count());
    }

    public function test_profesor_no_puede_enviar_oferta_sin_seleccionar_alumnos(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $oferta = OfertaPractica::factory()->create([
            'empresa_id' => $empresa->id,
            'creador_id' => $profesor->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($profesor)->post(route('ofertas.enviar', $oferta), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors('alumno_ids');
    }

    public function test_profesor_ve_formulario_de_envio_con_alumnos_del_curso_actual(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $cursoActual = \App\Models\CursoAcademico::factory()->create(['is_active' => true, 'fecha_inicio' => now()->subMonths(2)]);
        $cursoAnterior = \App\Models\CursoAcademico::factory()->create(['is_active' => false, 'fecha_inicio' => now()->subYear()]);

        $grupoActual = \App\Models\Grupo::factory()->create(['curso_academico_id' => $cursoActual->id, 'is_active' => true]);
        $grupoAnterior = \App\Models\Grupo::factory()->create(['curso_academico_id' => $cursoAnterior->id, 'is_active' => true]);

        $userAlumno = $this->createAlumno();
        $alumno = Alumno::factory()->create(['user_id' => $userAlumno->id]);
        $grupoActual->alumnos()->sync([$alumno->id => ['curso_academico_id' => $cursoActual->id]]);

        $oferta = OfertaPractica::factory()->create(['empresa_id' => $empresa->id, 'creador_id' => $profesor->id]);
        $oferta->grupos()->attach($grupoActual->id);

        $response = $this->actingAs($profesor)->get(route('ofertas.enviar-form', $oferta));

        $response->assertOk();
        $response->assertViewHas('grupos');
        // El grupo del curso anterior no aparece
        $response->assertDontSee($grupoAnterior->nombre ?: 'Grupo ' . $grupoAnterior->numero, false);
    }

    public function test_formulario_envio_solo_muestra_grupos_destinatarios(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $cursoActual = \App\Models\CursoAcademico::factory()->create(['is_active' => true, 'fecha_inicio' => now()->subMonths(2)]);

        $grupoObjetivo = \App\Models\Grupo::factory()->create(['curso_academico_id' => $cursoActual->id, 'is_active' => true]);
        $grupoOtro = \App\Models\Grupo::factory()->create(['curso_academico_id' => $cursoActual->id, 'is_active' => true]);

        $userObjetivo = $this->createAlumno();
        $userObjetivo->update(['name' => 'Ana Destinataria']);
        $alumnoObjetivo = Alumno::factory()->create(['user_id' => $userObjetivo->id]);
        $userOtro = $this->createAlumno();
        $userOtro->update(['name' => 'Berto NoDestinatario']);
        $alumnoOtro = Alumno::factory()->create(['user_id' => $userOtro->id]);
        $grupoObjetivo->alumnos()->sync([$alumnoObjetivo->id => ['curso_academico_id' => $cursoActual->id]]);
        $grupoOtro->alumnos()->sync([$alumnoOtro->id => ['curso_academico_id' => $cursoActual->id]]);

        $oferta = OfertaPractica::factory()->create(['empresa_id' => $empresa->id, 'creador_id' => $profesor->id]);
        $oferta->grupos()->attach($grupoObjetivo->id);

        $response = $this->actingAs($profesor)->get(route('ofertas.enviar-form', $oferta));

        $response->assertOk();
        // El grupo destinatario y su alumno aparecen; el grupo no destinatario no
        $response->assertSee($alumnoObjetivo->user->name, false);
        $response->assertDontSee($grupoOtro->nombre ?: 'Grupo ' . $grupoOtro->numero, false);
        $response->assertDontSee($alumnoOtro->user->name, false);
    }

    public function test_formulario_envio_avisa_si_oferta_no_tiene_grupos(): void
    {
        $profesor = $this->createProfesor();
        $empresa = Empresa::factory()->create();
        $oferta = OfertaPractica::factory()->create(['empresa_id' => $empresa->id, 'creador_id' => $profesor->id]);

        $response = $this->actingAs($profesor)->get(route('ofertas.enviar-form', $oferta));

        $response->assertOk();
        $response->assertViewHas('grupos');
        $this->assertCount(0, $response->viewData('grupos'));
        $response->assertSee('no está dirigida a ningún grupo', false);
    }

    public function test_empresa_can_create_oferta(): void
    {        $empresaUser = $this->createEmpresaUser();
        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($empresaUser)->post(route('ofertas.store'), [
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'Sistemas',
            'num_alumnos' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ofertas_practicas', [
            'especialidad_requerida' => 'Sistemas',
        ]);
    }

    public function test_alumno_cannot_create_oferta(): void
    {
        $alumno = $this->createAlumno();
        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($alumno)->post(route('ofertas.store'), [
            'empresa_id' => $empresa->id,
            'especialidad_requerida' => 'BD',
            'num_alumnos' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_oferta_store_requires_validation(): void
    {
        $profesor = $this->createProfesor();
        $response = $this->actingAs($profesor)->post(route('ofertas.store'), []);
        $response->assertSessionHasErrors(['empresa_id', 'especialidad_requerida', 'num_alumnos']);
    }

    public function test_alumno_can_postularse_to_oferta(): void
    {
        $alumno = $this->createAlumno();
        $alumnoModel = Alumno::factory()->create(['user_id' => $alumno->id]);
        $oferta = OfertaPractica::factory()->create(['estado' => 'activa']);

        $response = $this->actingAs($alumno)->post(route('ofertas.postularse', $oferta));

        $response->assertRedirect();
        $this->assertDatabaseHas('solicitudes_practicas', [
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_alumno_cannot_postularse_twice(): void
    {
        $alumno = $this->createAlumno();
        $alumnoModel = Alumno::factory()->create(['user_id' => $alumno->id]);
        $oferta = OfertaPractica::factory()->create(['estado' => 'activa']);

        // Primera solicitud
        $this->actingAs($alumno)->post(route('ofertas.postularse', $oferta));

        // Segunda solicitud (debe fallar)
        $response = $this->actingAs($alumno)->post(route('ofertas.postularse', $oferta));
        $response->assertSessionHasErrors('error');
    }

    public function test_alumno_cannot_postularse_to_inactive_oferta(): void
    {
        $alumno = $this->createAlumno();
        Alumno::factory()->create(['user_id' => $alumno->id]);
        $oferta = OfertaPractica::factory()->create(['estado' => 'cerrada']);

        $response = $this->actingAs($alumno)->post(route('ofertas.postularse', $oferta));
        $response->assertSessionHasErrors('error');
    }

    public function test_alumno_can_retirar_solicitud(): void
    {
        $alumno = $this->createAlumno();
        $alumnoModel = Alumno::factory()->create(['user_id' => $alumno->id]);
        $oferta = OfertaPractica::factory()->create(['estado' => 'activa']);
        $solicitud = SolicitudPractica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($alumno)->post(route('ofertas.solicitudes.retirar', $solicitud));

        $response->assertRedirect();
        $this->assertEquals('retirado', $solicitud->fresh()->estado);
    }

    public function test_profesor_can_aceptar_solicitud(): void
    {
        $profesor = $this->createProfesor();
        $oferta = OfertaPractica::factory()->create();
        $alumnoModel = \App\Models\Alumno::factory()->create();
        $solicitud = SolicitudPractica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($profesor)->post(route('ofertas.solicitudes.aceptar', $solicitud));

        $response->assertRedirect();
        $this->assertEquals('aceptado', $solicitud->fresh()->estado);
    }

    public function test_profesor_can_rechazar_solicitud(): void
    {
        $profesor = $this->createProfesor();
        $oferta = OfertaPractica::factory()->create();
        $alumnoModel = \App\Models\Alumno::factory()->create();
        $solicitud = SolicitudPractica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($profesor)->post(route('ofertas.solicitudes.rechazar', $solicitud), [
            'motivo_rechazo' => 'No cumple requisitos',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rechazado', $solicitud->fresh()->estado);
        $this->assertEquals('No cumple requisitos', $solicitud->fresh()->motivo_rechazo);
    }

    public function test_oferta_show_includes_solicitudes(): void
    {
        $profesor = $this->createProfesor();
        $oferta = OfertaPractica::factory()->create();
        $alumnoModel = \App\Models\Alumno::factory()->create();
        SolicitudPractica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
        ]);

        $response = $this->actingAs($profesor)->get(route('ofertas.show', $oferta));

        $response->assertOk();
        $response->assertViewHas('oferta', fn($o) => $o->id === $oferta->id);
    }

    public function test_oferta_model_has_correct_table(): void
    {
        $oferta = OfertaPractica::factory()->create();
        $this->assertEquals('ofertas_practicas', $oferta->getTable());
    }

    public function test_solicitud_model_has_correct_table(): void
    {
        $solicitud = SolicitudPractica::factory()->create();
        $this->assertEquals('solicitudes_practicas', $solicitud->getTable());
    }

    public function test_solicitud_belongs_to_oferta(): void
    {
        $oferta = OfertaPractica::factory()->create();
        $alumnoModel = \App\Models\Alumno::factory()->create();
        $solicitud = SolicitudPractica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
        ]);

        $this->assertInstanceOf(OfertaPractica::class, $solicitud->oferta);
    }

    public function test_solicitud_belongs_to_alumno(): void
    {
        $alumnoModel = \App\Models\Alumno::factory()->create();
        $oferta = OfertaPractica::factory()->create();
        $solicitud = SolicitudPractica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
        ]);

        $this->assertInstanceOf(Alumno::class, $solicitud->alumno);
    }

    public function test_solicitud_esta_pendiente(): void
    {
        $oferta = OfertaPractica::factory()->create();
        $alumnoModel = \App\Models\Alumno::factory()->create();
        $solicitud = SolicitudPractica::create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
            'estado' => 'pendiente',
        ]);
        $this->assertTrue($solicitud->estaPendiente());
        $this->assertFalse($solicitud->estaAceptada());

        $solicitud->update(['estado' => 'aceptado']);
        $this->assertFalse($solicitud->fresh()->estaPendiente());
        $this->assertTrue($solicitud->fresh()->estaAceptada());
    }

    public function test_oferta_scope_activas(): void
    {
        OfertaPractica::create(['especialidad_requerida' => 'BD', 'estado' => 'activa']);
        OfertaPractica::create(['especialidad_requerida' => 'BD', 'estado' => 'cerrada']);

        $activas = OfertaPractica::activas()->get();
        $this->assertCount(1, $activas);
    }

    public function test_oferta_scope_por_especialidad(): void
    {
        OfertaPractica::create(['especialidad_requerida' => 'BD', 'estado' => 'activa']);
        OfertaPractica::create(['especialidad_requerida' => 'Redes', 'estado' => 'activa']);

        $bd = OfertaPractica::porEspecialidad('BD')->get();
        $this->assertCount(1, $bd);
        $this->assertEquals('BD', $bd->first()->especialidad_requerida);
    }

    public function test_alumno_can_see_mis_ofertas(): void
    {
        $alumno = $this->createAlumno();
        $alumnoModel = Alumno::factory()->create(['user_id' => $alumno->id]);
        $oferta = OfertaPractica::factory()->create(['estado' => 'activa']);
        SolicitudPractica::factory()->create([
            'oferta_id' => $oferta->id,
            'alumno_id' => $alumnoModel->id,
        ]);

        $response = $this->actingAs($alumno)->get(route('ofertas.mis-ofertas'));

        $response->assertOk();
        $response->assertViewHas('solicitudes');
    }

    public function test_oferta_show_displays_cv_column_and_buttons(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $profesor = $this->createProfesor();
        $oferta = OfertaPractica::factory()->create();

        $user1 = $this->createAlumno();
        $user1->update(['name' => 'Carlos Con CV', 'cv_url' => 'cvs/carlos_cv.pdf']);
        \Illuminate\Support\Facades\Storage::disk('public')->put('cvs/carlos_cv.pdf', 'PDF Carlos');
        $alumno1 = Alumno::factory()->create(['user_id' => $user1->id]);

        $user2 = $this->createAlumno();
        $user2->update(['name' => 'David Sin CV', 'cv_url' => null]);
        $alumno2 = Alumno::factory()->create(['user_id' => $user2->id]);

        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno1->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno2->id]);

        $response = $this->actingAs($profesor)->get(route('ofertas.show', $oferta));

        $response->assertOk();
        $response->assertSee('Descargar todos los CVs (ZIP)');
        $response->assertSee('Descargar CV');
        $response->assertSee('Sin CV subido');
        $response->assertSee('1 CV disponible');
    }

    public function test_profesor_can_download_individual_student_cv_from_oferta(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $profesor = $this->createProfesor();
        $oferta = OfertaPractica::factory()->create();

        $user = $this->createAlumno();
        $user->update(['name' => 'Elena Gomez', 'cv_url' => 'cvs/elena_cv.pdf']);
        \Illuminate\Support\Facades\Storage::disk('public')->put('cvs/elena_cv.pdf', 'Contenido CV Elena');
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);

        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno->id]);

        $response = $this->actingAs($profesor)->get(route('ofertas.descargar-cv-alumno', [$oferta, $alumno]));

        $response->assertOk();
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('CV_elena_gomez.pdf', $response->headers->get('content-disposition'));
    }

    public function test_profesor_can_download_all_cvs_as_zip_from_oferta(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $profesor = $this->createProfesor();
        $oferta = OfertaPractica::factory()->create(['especialidad_requerida' => 'Desarrollo Web']);

        $user1 = $this->createAlumno();
        $user1->update(['name' => 'Alumno Uno', 'cv_url' => 'cvs/alumno1.pdf']);
        \Illuminate\Support\Facades\Storage::disk('public')->put('cvs/alumno1.pdf', 'PDF alumno uno');
        $alumno1 = Alumno::factory()->create(['user_id' => $user1->id]);

        $user2 = $this->createAlumno();
        $user2->update(['name' => 'Alumno Dos', 'cv_url' => 'cvs/alumno2.pdf']);
        \Illuminate\Support\Facades\Storage::disk('public')->put('cvs/alumno2.pdf', 'PDF alumno dos');
        $alumno2 = Alumno::factory()->create(['user_id' => $user2->id]);

        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno1->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno2->id]);

        $response = $this->actingAs($profesor)->get(route('ofertas.descargar-cvs', $oferta));

        $response->assertOk();
        $this->assertEquals('application/zip', $response->headers->get('content-type'));
        $this->assertStringContainsString('.zip', $response->headers->get('content-disposition'));
    }

    public function test_zip_download_redirects_with_warning_when_no_cvs_available(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $profesor = $this->createProfesor();
        $oferta = OfertaPractica::factory()->create();

        $user = $this->createAlumno();
        $user->update(['name' => 'Alumno Sin CV', 'cv_url' => null]);
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);

        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno->id]);

        $response = $this->actingAs($profesor)->get(route('ofertas.descargar-cvs', $oferta));

        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }

    public function test_alumno_cannot_download_cvs_or_zip_from_oferta(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $alumnoUser = $this->createAlumno();
        $oferta = OfertaPractica::factory()->create();

        $otroUser = $this->createAlumno();
        $otroUser->update(['name' => 'Otro Alumno', 'cv_url' => 'cvs/otro.pdf']);
        \Illuminate\Support\Facades\Storage::disk('public')->put('cvs/otro.pdf', 'PDF otro');
        $otroAlumno = Alumno::factory()->create(['user_id' => $otroUser->id]);

        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $otroAlumno->id]);

        // Alumno no puede descargar el zip de la oferta
        $responseZip = $this->actingAs($alumnoUser)->get(route('ofertas.descargar-cvs', $oferta));
        $responseZip->assertForbidden();

        // Alumno no puede descargar el CV de otro alumno a través de la oferta
        $responseInd = $this->actingAs($alumnoUser)->get(route('ofertas.descargar-cv-alumno', [$oferta, $otroAlumno]));
        $responseInd->assertForbidden();
    }

    public function test_empresa_sin_usuario_can_view_oferta_via_token(): void
    {
        $oferta = OfertaPractica::factory()->create(['especialidad_requerida' => 'Desarrollo Web Dual']);
        $user = $this->createAlumno();
        $user->update(['name' => 'Sara Martinez', 'cv_url' => 'cvs/sara.pdf']);
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno->id]);

        $this->assertNotNull($oferta->token);

        // Sin autenticación (usuario invitado / empresa externa)
        $response = $this->get(route('ofertas.compartida', $oferta->token));

        $response->assertOk();
        $response->assertSee('Desarrollo Web Dual');
        $response->assertSee('Sara Martinez');
        $response->assertSee('Portal de Empresas');
    }

    public function test_empresa_sin_usuario_can_download_student_cv_via_token(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $oferta = OfertaPractica::factory()->create();
        $user = $this->createAlumno();
        $user->update(['name' => 'Manuel Gomez', 'cv_url' => 'cvs/manuel.pdf']);
        \Illuminate\Support\Facades\Storage::disk('public')->put('cvs/manuel.pdf', 'Contenido CV Manuel');
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno->id]);

        $response = $this->get(route('ofertas.compartida.descargar-cv', ['token' => $oferta->token, 'alumno' => $alumno->id]));

        $response->assertOk();
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('CV_manuel_gomez.pdf', $response->headers->get('content-disposition'));
    }

    public function test_empresa_sin_usuario_can_download_all_cvs_zip_via_token(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $oferta = OfertaPractica::factory()->create(['especialidad_requerida' => 'Ciberseguridad']);
        $user = $this->createAlumno();
        $user->update(['name' => 'Laura Ruiz', 'cv_url' => 'cvs/laura.pdf']);
        \Illuminate\Support\Facades\Storage::disk('public')->put('cvs/laura.pdf', 'Contenido CV Laura');
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);
        SolicitudPractica::factory()->create(['oferta_id' => $oferta->id, 'alumno_id' => $alumno->id]);

        $response = $this->get(route('ofertas.compartida.descargar-cvs', ['token' => $oferta->token]));

        $response->assertOk();
        $this->assertEquals('application/zip', $response->headers->get('content-type'));
        $this->assertStringContainsString('.zip', $response->headers->get('content-disposition'));
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->get(route('ofertas.compartida', 'token_invalido_inexistente_12345'));
        $response->assertNotFound();
    }

    public function test_profesor_can_regenerate_token(): void
    {
        $profesor = $this->createProfesor();
        $oferta = OfertaPractica::factory()->create(['creador_id' => $profesor->id]);
        $oldToken = $oferta->token;

        $response = $this->actingAs($profesor)->post(route('ofertas.regenerar-token', $oferta));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNotEquals($oldToken, $oferta->fresh()->token);

        // El token antiguo ya no funciona
        $this->get(route('ofertas.compartida', $oldToken))->assertNotFound();

        // El nuevo token funciona
        $this->get(route('ofertas.compartida', $oferta->fresh()->token))->assertOk();
    }
}