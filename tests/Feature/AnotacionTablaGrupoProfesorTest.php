<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Anotacion;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnotacionTablaGrupoProfesorTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@ieshlanz.es',
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
            'email' => 'prof_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_PROFESOR],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $profesor = Profesor::create([
            'user_id' => $user->id,
            'especialidad' => 'Informática',
            'es_tutor' => false,
        ]);

        return ['user' => $user, 'profesor' => $profesor];
    }

    private function createGrupo(string $nombre = '1º DAM-A'): Grupo
    {
        $curso = CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            ['fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30', 'is_active' => true]
        );

        $familia = Familia::firstOrCreate(
            ['codigo' => 'INF'],
            ['nombre' => 'Informática', 'is_active' => true]
        );

        $ciclo = Ciclo::firstOrCreate(
            ['codigo' => 'DAM'],
            ['familia_id' => $familia->id, 'nombre' => 'DAM', 'grado' => 'superior', 'duracion_anos' => 2, 'is_active' => true]
        );

        $linea = Linea::firstOrCreate(
            ['ciclo_id' => $ciclo->id, 'turno' => 'manana'],
            ['nombre' => 'DAM Mañana', 'is_active' => true]
        );

        return Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $curso->id,
            'numero' => 1,
            'nombre' => $nombre,
            'is_active' => true,
        ]);
    }

    private function createAlumno(Grupo $grupo, string $name = 'Alumno Test'): Alumno
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => 'alum_' . uniqid() . '@ieshlanz.es',
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);

        $alumno = Alumno::create([
            'user_id' => $user->id,
        ]);

        $alumno->grupos()->attach($grupo->id, ['curso_academico_id' => $grupo->curso_academico_id]);

        return $alumno;
    }

    public function test_profesor_only_sees_groups_they_teach(): void
    {
        $profData = $this->createProfesor('Profesor Asignado');
        $grupoImpartido = $this->createGrupo('1º DAM-Impartido');
        $grupoAjeno = $this->createGrupo('1º DAW-Ajeno');

        // Asignar solo grupoImpartido al profesor
        $profData['profesor']->gruposImpartidos()->attach($grupoImpartido->id);

        $this->createAlumno($grupoImpartido, 'Alumno DAM');
        $this->createAlumno($grupoAjeno, 'Alumno DAW');

        $response = $this->actingAs($profData['user'])->get(route('anotaciones.create'));

        $response->assertStatus(200);
        $response->assertSee('1º DAM-Impartido');
        $response->assertDontSee('1º DAW-Ajeno');
        $response->assertSee('Alumno DAM');
        $response->assertDontSee('Alumno DAW');
    }

    public function test_admin_can_see_all_groups(): void
    {
        $admin = $this->createAdmin();
        $grupo1 = $this->createGrupo('1º DAM-General');
        $grupo2 = $this->createGrupo('2º DAW-General');

        $this->createAlumno($grupo1, 'Alumno DAM');
        $this->createAlumno($grupo2, 'Alumno DAW');

        $response = $this->actingAs($admin)->get(route('anotaciones.create'));

        $response->assertStatus(200);
        $response->assertSee('1º DAM-General');
        $response->assertSee('2º DAW-General');
    }

    public function test_create_view_renders_group_table_with_fields_and_alpine(): void
    {
        $profData = $this->createProfesor();
        $grupo = $this->createGrupo('1º DAM-Tabla');
        $profData['profesor']->gruposImpartidos()->attach($grupo->id);

        $alumno1 = $this->createAlumno($grupo, 'Carlos Benitez');
        $alumno1->user->update(['avatar_url' => 'avatars/carlos.jpg']);
        $alumno2 = $this->createAlumno($grupo, 'Ana Alvarez');

        $response = $this->actingAs($profData['user'])->get(route('anotaciones.create', ['grupo_id' => $grupo->id]));

        $response->assertStatus(200);
        $response->assertSee('Puesto');
        $response->assertSee('Alumno');
        $response->assertSee('Título');
        $response->assertSee('Contenido / Observación');
        $response->assertSee('Carlos Benitez');
        $response->assertSee('Ana Alvarez');
        $response->assertSee('carlos.jpg');
        $response->assertDontSee('item.name.charAt(0)');
        $response->assertSee('anotacionesGrupoApp');
        $response->assertSee('Guardar Anotaciones del Grupo');
    }

    public function test_profesor_can_batch_store_anotaciones_with_puestos_for_group(): void
    {
        $profData = $this->createProfesor();
        $grupo = $this->createGrupo('1º DAM-Batch');
        $profData['profesor']->gruposImpartidos()->attach($grupo->id);

        $alumno1 = $this->createAlumno($grupo, 'Carlos Primero');
        $alumno2 = $this->createAlumno($grupo, 'Beatriz Segunda');

        $response = $this->actingAs($profData['user'])->post(route('anotaciones.store'), [
            'grupo_id' => $grupo->id,
            'anotaciones' => [
                $alumno1->id => [
                    'alumno_id' => $alumno1->id,
                    'puesto' => 1,
                    'titulo' => 'Primero de la clase',
                    'contenido' => 'Muy participativo y resolutivo.',
                ],
                $alumno2->id => [
                    'alumno_id' => $alumno2->id,
                    'puesto' => 2,
                    'titulo' => 'Segunda de la clase',
                    'contenido' => 'Excelente trabajo en equipo.',
                ],
            ],
        ]);

        $response->assertRedirect(route('anotaciones.create', ['grupo_id' => $grupo->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('anotaciones', [
            'alumno_id' => $alumno1->id,
            'profesor_id' => $profData['profesor']->id,
            'puesto' => 1,
            'titulo' => 'Primero de la clase',
            'contenido' => 'Muy participativo y resolutivo.',
        ]);

        $this->assertDatabaseHas('anotaciones', [
            'alumno_id' => $alumno2->id,
            'profesor_id' => $profData['profesor']->id,
            'puesto' => 2,
            'titulo' => 'Segunda de la clase',
            'contenido' => 'Excelente trabajo en equipo.',
        ]);
    }

    public function test_profesor_cannot_batch_store_for_unassigned_group(): void
    {
        $profData = $this->createProfesor();
        $grupoAjeno = $this->createGrupo('Grupo Prohibido');
        $alumno = $this->createAlumno($grupoAjeno, 'Alumno Prohibido');

        // El profesor no tiene asignado este grupo
        $response = $this->actingAs($profData['user'])->post(route('anotaciones.store'), [
            'grupo_id' => $grupoAjeno->id,
            'anotaciones' => [
                $alumno->id => [
                    'alumno_id' => $alumno->id,
                    'puesto' => 1,
                    'titulo' => 'Intento No Autorizado',
                    'contenido' => 'Contenido no autorizado',
                ],
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('anotaciones', [
            'alumno_id' => $alumno->id,
            'titulo' => 'Intento No Autorizado',
        ]);
    }

    public function test_batch_store_updates_existing_anotacion_if_already_present(): void
    {
        $profData = $this->createProfesor();
        $grupo = $this->createGrupo('1º DAM-Update');
        $profData['profesor']->gruposImpartidos()->attach($grupo->id);

        $alumno = $this->createAlumno($grupo, 'Estudiante Existente');

        // Crear una anotación previa
        $anotacionPrevia = Anotacion::create([
            'alumno_id' => $alumno->id,
            'profesor_id' => $profData['profesor']->id,
            'puesto' => 5,
            'titulo' => 'Puesto Anterior',
            'contenido' => 'Observación anterior',
        ]);

        // Guardar actualización con nuevo puesto y contenido
        $response = $this->actingAs($profData['user'])->post(route('anotaciones.store'), [
            'grupo_id' => $grupo->id,
            'anotaciones' => [
                $alumno->id => [
                    'alumno_id' => $alumno->id,
                    'anotacion_id' => $anotacionPrevia->id,
                    'puesto' => 2,
                    'titulo' => 'Puesto Actualizado',
                    'contenido' => 'Observación actualizada y mejorada',
                ],
            ],
        ]);

        $response->assertRedirect(route('anotaciones.create', ['grupo_id' => $grupo->id]));

        // No se debe duplicar el registro
        $this->assertEquals(1, Anotacion::where('alumno_id', $alumno->id)->count());

        $this->assertDatabaseHas('anotaciones', [
            'id' => $anotacionPrevia->id,
            'puesto' => 2,
            'titulo' => 'Puesto Actualizado',
            'contenido' => 'Observación actualizada y mejorada',
        ]);
    }
}
