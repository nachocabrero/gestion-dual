<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MensajeGruposFiltroTest extends TestCase
{
    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Depuracion Grupos',
            'email' => 'admin_depuracion_' . uniqid() . '@test.com',
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    /**
     * El formulario de redacción de mensajes solo muestra grupos activos del curso actual.
     */
    public function test_mensaje_create_solo_muestra_grupos_activos_del_curso_actual(): void
    {
        $admin = $this->createAdmin();

        $cursoActual = CursoAcademico::where('is_active', true)->first();
        if (!$cursoActual) {
            $cursoActual = CursoAcademico::create([
                'nombre' => '2026-2027',
                'is_active' => true,
                'fecha_inicio' => '2026-09-15',
                'fecha_fin' => '2027-06-30',
            ]);
        }

        $cursoAntiguo = CursoAcademico::create([
            'nombre' => '2020-2021',
            'is_active' => false,
            'fecha_inicio' => '2020-09-15',
            'fecha_fin' => '2021-06-30',
        ]);

        $familia = Familia::firstOrCreate(['codigo' => 'INF_TEST'], ['nombre' => 'Informática Test', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW_TEST'], ['familia_id' => $familia->id, 'nombre' => 'DAW Test', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::create(['ciclo_id' => $ciclo->id, 'nombre' => 'Línea Test', 'turno' => 'manana', 'is_active' => true]);

        $grupoActivo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $cursoActual->id,
            'numero' => 1,
            'nombre' => '1º DAW Test Actual',
            'is_active' => true,
        ]);

        $grupoAntiguo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $cursoAntiguo->id,
            'numero' => 1,
            'nombre' => '1º DAW Test Antiguo',
            'is_active' => false,
        ]);

        $grupoInactivo = Grupo::create([
            'linea_id' => $linea->id,
            'curso_academico_id' => $cursoActual->id,
            'numero' => 2,
            'nombre' => '2º ASIR Inexistente',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('mensajes.create'));

        $response->assertOk();
        $response->assertSee('1º DAW Test Actual');
        $response->assertDontSee('1º DAW Test Antiguo');
        $response->assertDontSee('2º ASIR Inexistente');
    }

    /**
     * En la base de datos no deben existir grupos con 'ASIR' ni grupos 'SMR Tarde' o 'GBI Tarde'.
     */
    public function test_no_existen_grupos_asir_ni_turnos_inexistentes(): void
    {
        $asirCount = Grupo::where('nombre', 'like', '%ASIR%')->count();
        $this->assertSame(0, $asirCount, 'No deben existir grupos de ASIR en la base de datos.');

        $smrTardeCount = Grupo::where('nombre', 'like', '%SMR%Tarde%')->count();
        $this->assertSame(0, $smrTardeCount, 'No deben existir grupos de SMR Tarde.');

        $gbiTardeCount = Grupo::where('nombre', 'like', '%GBI%Tarde%')->count();
        $this->assertSame(0, $gbiTardeCount, 'No deben existir grupos de GBI Tarde.');
    }

    /**
     * Enviar mensaje a un grupo oficial activo notifica a los alumnos de ese grupo.
     */
    public function test_envio_mensaje_a_grupo_oficial_llega_a_alumnos_activos(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $cursoActual = CursoAcademico::where('is_active', true)->first();

        $grupo = Grupo::where('is_active', true)
            ->where('curso_academico_id', $cursoActual->id)
            ->where('nombre', 'like', '%DAW%')
            ->first();

        $this->assertNotNull($grupo, 'Debe existir un grupo activo de DAW en el curso actual.');

        $userAlumno = User::factory()->create([
            'roles' => [User::ROLE_ALUMNO],
            'is_active' => true,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
        $alumno = Alumno::factory()->create(['user_id' => $userAlumno->id]);
        $alumno->grupos()->attach($grupo->id, ['curso_academico_id' => $cursoActual->id]);

        $response = $this->actingAs($admin)->post(route('mensajes.store'), [
            'tipo_destinatario' => 'grupos',
            'grupo_ids' => [$grupo->id],
            'asunto' => 'Aviso Oficial de Curso',
            'cuerpo' => 'Estimado alumnado, comunicado oficial para el grupo.',
            'canales' => ['in_app', 'email'],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $userAlumno->id,
            'titulo' => 'Aviso Oficial de Curso',
        ]);
    }
}
