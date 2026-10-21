<?php

namespace Tests\Feature;

use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/teacher');

        $response->assertStatus(200);
        $response->assertSee('Portal del Profesorado');
        $response->assertSee('Registro de Profesorado');
        $response->assertSee('Especialidad docente');
        $response->assertSee('Familia Profesional');
    }

    public function test_teacher_registration_lists_active_familias(): void
    {
        $familiaActiva = Familia::create([
            'nombre' => 'Informática y Comunicaciones',
            'codigo' => 'IFC',
            'is_active' => true,
        ]);

        $familiaInactiva = Familia::create([
            'nombre' => 'Familia Desactivada',
            'codigo' => 'DES',
            'is_active' => false,
        ]);

        $response = $this->get('/teacher');

        $response->assertStatus(200);
        $response->assertSee('Informática y Comunicaciones');
        $response->assertDontSee('Familia Desactivada');
    }

    public function test_teachers_can_register_and_remain_inactive_pending_approval(): void
    {
        $familia = Familia::create([
            'nombre' => 'Informática y Comunicaciones',
            'codigo' => 'IFC',
            'is_active' => true,
        ]);

        $response = $this->post('/teacher', [
            'name' => 'Profesor Prueba',
            'email' => 'profesor.prueba@ieshlanz.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'especialidad' => 'Informática',
            'familia_id' => $familia->id,
            'consent_rgpd' => '1',
            'privacy_policy' => '1',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $this->assertGuest();

        $user = User::where('email', 'profesor.prueba@ieshlanz.es')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(User::ROLE_PROFESOR));
        $this->assertFalse($user->is_active);
        $this->assertFalse($user->isActive());
        $this->assertEquals(User::STATUS_PENDING, $user->registration_status);
        $this->assertTrue($user->isPendingApproval());

        $profesor = Profesor::where('user_id', $user->id)->first();
        $this->assertNotNull($profesor);
        $this->assertEquals('Informática', $profesor->especialidad);
        $this->assertEquals($familia->id, $profesor->familia_id);
    }

    public function test_teacher_registration_requires_rgpd_and_privacy_consent(): void
    {
        $response = $this->post('/teacher', [
            'name' => 'Profesor Sin Consentimiento',
            'email' => 'profesor.sinconsentimiento@ieshlanz.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'especialidad' => 'Informática',
        ]);

        $response->assertSessionHasErrors(['consent_rgpd', 'privacy_policy']);
        $this->assertDatabaseMissing('users', [
            'email' => 'profesor.sinconsentimiento@ieshlanz.es',
        ]);
    }

    public function test_pending_teacher_cannot_log_in(): void
    {
        $user = User::create([
            'name' => 'Profesor Pendiente',
            'email' => 'profesor.pendiente@ieshlanz.es',
            'password' => bcrypt('password123'),
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => false,
            'registration_status' => User::STATUS_PENDING,
            'consent_rgpd' => true,
        ]);

        Profesor::create([
            'user_id' => $user->id,
            'especialidad' => 'Sistemas',
        ]);

        $response = $this->post('/login', [
            'email' => 'profesor.pendiente@ieshlanz.es',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_view_pending_teacher_and_approve_access(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@ieshlanz.es'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('password123'),
                'roles' => [User::ROLE_ADMIN],
                'is_active' => true,
                'registration_status' => User::STATUS_APPROVED,
                'consent_rgpd' => true,
            ]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'IFC'],
            [
                'nombre' => 'Informática y Comunicaciones',
                'is_active' => true,
            ]
        );

        $teacherUser = User::create([
            'name' => 'Profesor Solicitante',
            'email' => 'profesor.solicitante@ieshlanz.es',
            'password' => bcrypt('password123'),
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => false,
            'registration_status' => User::STATUS_PENDING,
            'consent_rgpd' => true,
        ]);

        Profesor::create([
            'user_id' => $teacherUser->id,
            'familia_id' => $familia->id,
            'especialidad' => 'Desarrollo Web',
        ]);

        // Admin ve la lista con la especialidad
        $viewResponse = $this->actingAs($admin)->get('/admin/users');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Profesor Solicitante');
        $viewResponse->assertSee('Desarrollo Web');
        $viewResponse->assertSee('En espera');

        // Admin aprueba la cuenta
        $approveResponse = $this->actingAs($admin)->post(route('admin.users.approve', $teacherUser));
        $approveResponse->assertRedirect();

        $teacherUser->refresh();
        $this->assertTrue($teacherUser->is_active);
        $this->assertTrue($teacherUser->isActive());
        $this->assertEquals(User::STATUS_APPROVED, $teacherUser->registration_status);
        $this->assertNotNull($teacherUser->registration_approved_at);

        // Ahora el profesor puede iniciar sesión
        $this->post('/logout');

        $loginResponse = $this->post('/login', [
            'email' => 'profesor.solicitante@ieshlanz.es',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($teacherUser);
    }

    public function test_admin_can_reject_pending_teacher(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.test@ieshlanz.es',
            'password' => bcrypt('password123'),
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
            'consent_rgpd' => true,
        ]);

        $teacherUser = User::create([
            'name' => 'Profesor Rechazado',
            'email' => 'profesor.rechazado@ieshlanz.es',
            'password' => bcrypt('password123'),
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => false,
            'registration_status' => User::STATUS_PENDING,
            'consent_rgpd' => true,
        ]);

        Profesor::create([
            'user_id' => $teacherUser->id,
            'especialidad' => 'Mecánica',
        ]);

        $rejectResponse = $this->actingAs($admin)->post(route('admin.users.reject', $teacherUser));
        $rejectResponse->assertRedirect();

        $teacherUser->refresh();
        $this->assertFalse($teacherUser->is_active);
        $this->assertFalse($teacherUser->isActive());
        $this->assertEquals(User::STATUS_REJECTED, $teacherUser->registration_status);

        // Rechazado no puede iniciar sesión
        $this->post('/logout');

        $loginResponse = $this->post('/login', [
            'email' => 'profesor.rechazado@ieshlanz.es',
            'password' => 'password123',
        ]);

        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_teacher_registration_screen_displays_groups_for_tutor_and_teaching(): void
    {
        CursoAcademico::query()->update(['is_active' => false]);
        $curso = CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => now()->startOfYear(),
            'fecha_fin' => now()->endOfYear(),
            'is_active' => true,
        ]);

        $grupo = Grupo::factory()->create([
            'nombre' => '1º DAW Mañana',
            'curso_academico_id' => $curso->id,
            'is_active' => true,
        ]);

        $response = $this->get('/teacher');
        $response->assertStatus(200);
        $response->assertSee('Grupo del que eres tutor (Optativo)');
        $response->assertSee('Grupos a los que impartes clase (Optativo)');
        $response->assertSee('1º DAW Mañana');
    }

    public function test_teacher_can_register_with_tutor_group_and_teaching_groups(): void
    {
        CursoAcademico::query()->update(['is_active' => false]);
        $curso = CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => now()->startOfYear(),
            'fecha_fin' => now()->endOfYear(),
            'is_active' => true,
        ]);

        $grupoTutor = Grupo::factory()->create([
            'nombre' => '1º DAW Mañana',
            'curso_academico_id' => $curso->id,
            'tutor_id' => null,
            'is_active' => true,
        ]);

        $grupoDocencia = Grupo::factory()->create([
            'nombre' => '2º DAW Mañana',
            'curso_academico_id' => $curso->id,
            'tutor_id' => null,
            'is_active' => true,
        ]);

        $response = $this->post('/teacher', [
            'name' => 'Profesor Con Grupos',
            'email' => 'profe.grupos@ieshlanz.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'especialidad' => 'Desarrollo Web',
            'tutor_grupo_id' => $grupoTutor->id,
            'grupos' => [$grupoTutor->id, $grupoDocencia->id],
            'consent_rgpd' => '1',
            'privacy_policy' => '1',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();

        $user = User::where('email', 'profe.grupos@ieshlanz.es')->first();
        $this->assertNotNull($user);

        // Verificar que el grupo tiene al usuario como tutor_id
        $grupoTutor->refresh();
        $this->assertEquals($user->id, $grupoTutor->tutor_id);

        // Verificar profesor
        $profesor = Profesor::where('user_id', $user->id)->first();
        $this->assertNotNull($profesor);
        $this->assertTrue($profesor->es_tutor);

        // Verificar que gruposImpartidos tiene ambos grupos
        $impartidosIds = $profesor->gruposImpartidos()->pluck('grupos.id')->toArray();
        $this->assertContains($grupoTutor->id, $impartidosIds);
        $this->assertContains($grupoDocencia->id, $impartidosIds);
    }

    public function test_teacher_registration_with_subject_per_group_persists_asignatura_in_pivot(): void
    {
        $ciclo = \App\Models\Ciclo::factory()->create();
        $asignatura = \App\Models\Asignatura::factory()->create([
            'ciclo_id' => $ciclo->id,
            'nombre' => 'Desarrollo Web en Entorno Servidor',
            'codigo' => 'DWES',
        ]);

        $grupo = Grupo::factory()->create([
            'nombre' => '2º DAW Mañana Docencia',
            'is_active' => true,
        ]);

        $response = $this->post('/teacher', [
            'name' => 'Profesor Con Asignatura',
            'email' => 'profe.asig@ieshlanz.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'especialidad' => 'Informática',
            'grupos' => [
                $grupo->id => [
                    'activo' => '1',
                    'asignatura_id' => $asignatura->id,
                ],
            ],
            'consent_rgpd' => '1',
            'privacy_policy' => '1',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'profe.asig@ieshlanz.es')->first();
        $this->assertNotNull($user);

        $profesor = Profesor::where('user_id', $user->id)->first();
        $this->assertNotNull($profesor);

        // Comprobar que en grupo_profesor está el grupo con su asignatura_id
        $this->assertDatabaseHas('grupo_profesor', [
            'profesor_id' => $profesor->id,
            'grupo_id' => $grupo->id,
            'asignatura_id' => $asignatura->id,
        ]);

        // Comprobar que la asignatura está sincronizada en profesor_asignatura
        $this->assertDatabaseHas('profesor_asignatura', [
            'profesor_id' => $profesor->id,
            'asignatura_id' => $asignatura->id,
        ]);
    }

    public function test_teacher_registration_fails_validation_when_active_group_has_no_subject(): void
    {
        $grupo = Grupo::factory()->create([
            'nombre' => '1º DAW Mañana Sin Asig',
            'is_active' => true,
        ]);

        $response = $this->post('/teacher', [
            'name' => 'Profesor Incompleto',
            'email' => 'profe.incompleto@ieshlanz.es',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'especialidad' => 'Informática',
            'grupos' => [
                $grupo->id => [
                    'activo' => '1',
                    'asignatura_id' => '', // Sin seleccionar asignatura
                ],
            ],
            'consent_rgpd' => '1',
            'privacy_policy' => '1',
        ]);

        $response->assertSessionHasErrors('grupos');
        $this->assertDatabaseMissing('users', [
            'email' => 'profe.incompleto@ieshlanz.es',
        ]);
    }
}

