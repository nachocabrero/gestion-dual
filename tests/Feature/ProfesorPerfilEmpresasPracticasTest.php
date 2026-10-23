<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Convenio;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Practica;
use App\Models\Profesor;
use App\Models\TutorLaboral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfesorPerfilEmpresasPracticasTest extends TestCase
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

    private function createProfesor(string $name = 'Profesor Test'): array
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
            'especialidad' => 'Informática',
        ]);

        return [$user, $profesor];
    }

    private function createAlumno(string $name = 'Alumno Test', ?int $tutorPracticasUserId = null): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => 'alum_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $alumno = Alumno::factory()->create([
            'user_id' => $user->id,
            'tutor_practicas_id' => $tutorPracticasUserId,
        ]);

        return [$user, $alumno];
    }

    public function test_teacher_profile_displays_assigned_tracking_companies(): void
    {
        $admin = $this->createAdmin();
        [$profUser, $profesor] = $this->createProfesor('Marcos Seguimiento');

        $empresa1 = Empresa::factory()->create([
            'nombre' => 'Pixel Dreams SL',
            'cif' => 'B11223344',
            'profesor_id' => $profesor->id,
            'is_active' => true,
        ]);

        $empresa2 = Empresa::factory()->create([
            'nombre' => 'Granada Cloud Tech',
            'cif' => 'B55667788',
            'profesor_id' => $profesor->id,
            'is_active' => true,
        ]);

        $empresaAjena = Empresa::factory()->create([
            'nombre' => 'Empresa de Otro Docente',
            'cif' => 'B99999999',
            'profesor_id' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('profesores.show', $profesor));

        $response->assertOk();
        $response->assertSee('Empresas en Seguimiento');
        $response->assertSee('Pixel Dreams SL');
        $response->assertSee('B11223344');
        $response->assertSee('Granada Cloud Tech');
        $response->assertSee('B55667788');
        $response->assertDontSee('Empresa de Otro Docente');
    }

    public function test_teacher_profile_displays_empty_state_when_no_companies_assigned(): void
    {
        $admin = $this->createAdmin();
        [$profUser, $profesor] = $this->createProfesor('Docente Sin Empresas');

        $response = $this->actingAs($admin)->get(route('profesores.show', $profesor));

        $response->assertOk();
        $response->assertSee('Empresas en Seguimiento');
        $response->assertSee('Este profesor no tiene actualmente empresas asignadas para seguimiento.');
    }

    public function test_teacher_profile_displays_practices_of_current_academic_year(): void
    {
        $admin = $this->createAdmin();
        [$profUser, $profesor] = $this->createProfesor('Elena Tutora');

        CursoAcademico::query()->update(['is_active' => false]);
        $cursoActual = CursoAcademico::factory()->create([
            'nombre' => '2026-2027',
            'fecha_inicio' => '2026-09-01',
            'fecha_fin' => '2027-06-30',
            'is_active' => true,
        ]);

        [$alumUser, $alumno] = $this->createAlumno('Carlos Alumno Dual', $profUser->id);
        $empresa = Empresa::factory()->create(['nombre' => 'Innova Tech SL']);
        $tutorLaboral = TutorLaboral::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Marta Laboral']);

        $practica = Practica::factory()->create([
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'tutor_laboral_id' => $tutorLaboral->id,
            'curso_academico_id' => $cursoActual->id,
            'horas_acumuladas' => 450,
            'convenio_firmado' => true,
        ]);

        $response = $this->actingAs($profUser)->get(route('profesores.show', $profesor));

        $response->assertOk();
        $response->assertSee('Prácticas Tutorizadas — Curso Actual');
        $response->assertSee('2026-2027');
        $response->assertSee('Carlos Alumno Dual');
        $response->assertSee('Innova Tech SL');
        $response->assertSee('Marta Laboral');
        $response->assertSee('450 h');
        $response->assertSee('Firmado');
    }

    public function test_teacher_profile_displays_previous_courses_dropdown_when_has_past_practices(): void
    {
        $admin = $this->createAdmin();
        [$profUser, $profesor] = $this->createProfesor('Antonio Veterano');

        CursoAcademico::query()->update(['is_active' => false]);
        $cursoActual = CursoAcademico::factory()->create([
            'nombre' => '2026-2027',
            'fecha_inicio' => '2026-09-01',
            'fecha_fin' => '2027-06-30',
            'is_active' => true,
        ]);

        $cursoAnterior = CursoAcademico::factory()->create([
            'nombre' => '2025-2026',
            'fecha_inicio' => '2025-09-01',
            'fecha_fin' => '2026-06-30',
            'is_active' => false,
        ]);

        // Práctica de curso anterior donde es tutor docente
        [$alumUserPasado, $alumnoPasado] = $this->createAlumno('Lucia Alumna 2025', $profUser->id);
        $empresaPasada = Empresa::factory()->create(['nombre' => 'Antigua Empresa SL']);

        Practica::factory()->create([
            'alumno_id' => $alumnoPasado->id,
            'empresa_id' => $empresaPasada->id,
            'curso_academico_id' => $cursoAnterior->id,
            'horas_acumuladas' => 520,
            'convenio_firmado' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('profesores.show', $profesor));

        $response->assertOk();
        $response->assertSee('Historial de Prácticas — Cursos Anteriores');
        $response->assertSee('2025-2026');
        $response->assertSee('Lucia Alumna 2025');
        $response->assertSee('Antigua Empresa SL');
        $response->assertSee('select-curso-anterior');
    }

    public function test_teacher_profile_does_not_display_previous_courses_dropdown_if_none_exist(): void
    {
        $admin = $this->createAdmin();
        [$profUser, $profesor] = $this->createProfesor('Profesor Nuevo');

        CursoAcademico::query()->update(['is_active' => false]);
        $cursoActual = CursoAcademico::factory()->create([
            'nombre' => '2026-2027',
            'fecha_inicio' => '2026-09-01',
            'fecha_fin' => '2027-06-30',
            'is_active' => true,
        ]);

        // Solo tiene práctica en el curso actual, ninguna en cursos anteriores
        [$alumUser, $alumno] = $this->createAlumno('Pedro Actual', $profUser->id);
        $empresa = Empresa::factory()->create(['nombre' => 'Empresa Actual SL']);

        Practica::factory()->create([
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'curso_academico_id' => $cursoActual->id,
            'horas_acumuladas' => 200,
        ]);

        $response = $this->actingAs($admin)->get(route('profesores.show', $profesor));

        $response->assertOk();
        $response->assertSee('Prácticas Tutorizadas — Curso Actual');
        $response->assertSee('Pedro Actual');
        // El bloque del desplegable de cursos anteriores NO debe aparecer
        $response->assertDontSee('Historial de Prácticas — Cursos Anteriores');
        $response->assertDontSee('select-curso-anterior');
    }

    public function test_convenio_tutor_docente_links_practice_to_professor(): void
    {
        $admin = $this->createAdmin();
        [$profUser, $profesor] = $this->createProfesor('Tutor Por Convenio');

        CursoAcademico::query()->update(['is_active' => false]);
        $cursoActual = CursoAcademico::factory()->create([
            'nombre' => '2026-2027',
            'fecha_inicio' => '2026-09-01',
            'fecha_fin' => '2027-06-30',
            'is_active' => true,
        ]);

        // Alumno sin tutor_practicas_id directo pero con convenio firmado por este profesor como tutor_docente
        [$alumUser, $alumno] = $this->createAlumno('Alumno Con Convenio', null);
        $empresa = Empresa::factory()->create(['nombre' => 'Software Al-Andalus SL']);

        Convenio::factory()->create([
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'tutor_docente_id' => $profesor->id,
            'estado' => 'firmado',
        ]);

        Practica::factory()->create([
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'curso_academico_id' => $cursoActual->id,
            'horas_acumuladas' => 380,
        ]);

        $response = $this->actingAs($profUser)->get(route('profesores.show', $profesor));

        $response->assertOk();
        $response->assertSee('Alumno Con Convenio');
        $response->assertSee('Software Al-Andalus SL');
        $response->assertSee('380 h');
    }
}
