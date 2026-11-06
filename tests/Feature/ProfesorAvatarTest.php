<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Empresa;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\User;
use Tests\TestCase;

class ProfesorAvatarTest extends TestCase
{
    private function createProfesorWithAvatar(string $name, ?string $avatarPath = null): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . '_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'avatar_url' => $avatarPath,
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
        ]);

        $profesor = Profesor::create([
            'user_id' => $user->id,
            'especialidad' => 'Informática',
            'es_tutor' => false,
        ]);

        return [$user, $profesor];
    }

    public function test_profesores_index_shows_avatar_when_present_and_initial_when_not(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN], 'is_active' => true]);

        [$userConFoto, $profConFoto] = $this->createProfesorWithAvatar('Docente Con Foto', 'avatars/foto_docente_test.jpg');
        [$userSinFoto, $profSinFoto] = $this->createProfesorWithAvatar('Docente Sin Foto', null);

        $response = $this->actingAs($admin)->get(route('profesores.index'));
        $response->assertOk();

        // Debe contener la imagen del avatar del docente con foto
        $response->assertSee('avatars/foto_docente_test.jpg');
        // Debe contener el nombre de ambos
        $response->assertSee($userConFoto->name);
        $response->assertSee($userSinFoto->name);
    }

    public function test_profesores_show_shows_avatar_when_present_and_initial_when_not(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN], 'is_active' => true]);

        [$userConFoto, $profConFoto] = $this->createProfesorWithAvatar('Clara Con Foto', 'avatars/clara_test.png');
        [$userSinFoto, $profSinFoto] = $this->createProfesorWithAvatar('Marcos Sin Foto', null);

        // Ficha del profesor con foto
        $resConFoto = $this->actingAs($admin)->get(route('profesores.show', $profConFoto));
        $resConFoto->assertOk();
        $resConFoto->assertSee('avatars/clara_test.png');

        // Ficha del profesor sin foto (muestra inicial)
        $resSinFoto = $this->actingAs($admin)->get(route('profesores.show', $profSinFoto));
        $resSinFoto->assertOk();
        $resSinFoto->assertDontSee('avatars/clara_test.png');
        $resSinFoto->assertSee('M'); // Inicial de Marcos
    }

    public function test_alumnos_show_displays_teacher_avatar_in_educational_team(): void
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-15', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );
        $familia = Familia::firstOrCreate(['codigo' => 'IFC_AVATAR'], ['nombre' => 'Informática Avatar', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW_AVATAR'], ['familia_id' => $familia->id, 'nombre' => 'DAW Avatar', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::firstOrCreate(['ciclo_id' => $ciclo->id, 'turno' => 'manana'], ['nombre' => 'DAW Mañana Avatar', 'is_active' => true]);
        $asig = Asignatura::firstOrCreate(['codigo' => 'DWE_AVATAR'], ['ciclo_id' => $ciclo->id, 'nombre' => 'Desarrollo Web Avatar', 'horas_semanales' => 6, 'is_active' => true]);

        [$userProfesor, $profesor] = $this->createProfesorWithAvatar('Elena Profesora Avatar', 'avatars/elena_avatar_test.webp');

        $grupo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'nombre' => 'Grupo Avatar Test',
            'numero' => 1,
            'tutor_id' => $userProfesor->id,
            'is_active' => true,
        ]);
        $grupo->profesores()->attach($profesor->id, ['asignatura_id' => $asig->id]);

        $userAlumno = User::factory()->create(['roles' => [User::ROLE_ALUMNO], 'is_active' => true]);
        $alumno = Alumno::create(['user_id' => $userAlumno->id]);
        $alumno->grupos()->attach($grupo->id, ['curso_academico_id' => $curso->id]);

        $response = $this->actingAs($userAlumno)->get(route('alumnos.show', $alumno));
        $response->assertOk();

        // En el bloque de equipo educativo debe aparecer la foto de la profesora
        $response->assertSee('avatars/elena_avatar_test.webp');
        $response->assertSee('Elena Profesora Avatar');
    }

    public function test_empresas_show_and_index_displays_tracking_teacher_avatar(): void
    {
        $admin = User::factory()->create(['roles' => [User::ROLE_ADMIN], 'is_active' => true]);
        [$userProfesor, $profesor] = $this->createProfesorWithAvatar('Tutor Empresa Avatar', 'avatars/tutor_empresa.jpg');

        $empresa = Empresa::create([
            'nombre' => 'Empresa Avatar Foto SL',
            'cif' => 'B' . rand(10000000, 99999999),
            'email' => 'avatar@empresa.com',
            'profesor_id' => $profesor->id,
            'admite_alumnos_curso_actual' => true,
            'is_active' => true,
        ]);

        // Ver en empresas.show
        $resShow = $this->actingAs($admin)->get(route('empresas.show', $empresa));
        $resShow->assertOk();
        $resShow->assertSee('avatars/tutor_empresa.jpg');
        $resShow->assertSee('Tutor Empresa Avatar');

        // Ver en empresas.index
        $resIndex = $this->actingAs($admin)->get(route('empresas.index', ['buscar' => 'Empresa Avatar Foto']));
        $resIndex->assertOk();
        $resIndex->assertSee('avatars/tutor_empresa.jpg');
        $resIndex->assertSee('Tutor Empresa Avatar');
    }
}
