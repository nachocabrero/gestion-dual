<?php

namespace Tests\Feature;

use App\Models\Ciclo;
use App\Models\CursoAcademico;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoRegistroScrollGruposTest extends TestCase
{
    use RefreshDatabase;

    private function createAcademicEnvironment(int $gruposCount = 6): array
    {
        $curso = CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-25',
            'is_active' => true,
        ]);

        $familia = Familia::create([
            'nombre' => 'Informática y Comunicaciones ' . uniqid(),
            'codigo' => 'IFC' . rand(100, 999),
            'is_active' => true,
        ]);

        $ciclo = Ciclo::create([
            'familia_id' => $familia->id,
            'nombre' => 'Desarrollo de Aplicaciones Web',
            'codigo' => 'DAW' . rand(100, 999),
            'grado' => 'superior',
            'duracion_anos' => 2,
            'is_active' => true,
        ]);

        $linea = Linea::create([
            'ciclo_id' => $ciclo->id,
            'nombre' => 'Línea Mañana',
            'turno' => 'manana',
            'is_active' => true,
        ]);

        $grupos = [];
        for ($i = 1; $i <= $gruposCount; $i++) {
            $grupos[] = Grupo::create([
                'linea_id' => $linea->id,
                'curso_academico_id' => $curso->id,
                'numero' => ($i % 2 === 0) ? 2 : 1,
                'nombre' => "Grupo {$i} DAW Mañana",
                'is_active' => true,
            ]);
        }

        return [$curso, $grupos];
    }

    public function test_registration_view_renders_group_scroll_container_and_indicators(): void
    {
        [$curso, $grupos] = $this->createAcademicEnvironment(6);

        $response = $this->get('/register');

        $response->assertOk();
        // Contenedor con scroll forzado y clase de estilo
        $response->assertSee('grupos-scroll-box');
        $response->assertSee('overflow-y-scroll');

        // Contador de grupos disponibles
        $response->assertSee('6 grupos disponibles');

        // Texto que indica desplazamiento
        $response->assertSee('Desplaza la lista verticalmente para ver todos los grupos');

        // Indicador visual de más grupos abajo y banner guiado
        $response->assertSee('Hay más grupos hacia abajo');
        $response->assertSee('Desplaza o pulsa aquí para ver más');
        $response->assertSee('6 grupos en total ↓');

        // Confirmación de final de lista
        $response->assertSee('Has revisado todos los grupos disponibles');

        // Los grupos creados deben estar presentes
        foreach ($grupos as $grupo) {
            $response->assertSee($grupo->nombre);
        }
    }

    public function test_registration_view_renders_filter_input_when_more_than_four_groups(): void
    {
        [$curso, $grupos] = $this->createAcademicEnvironment(5);

        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('Buscar grupo o ciclo (ej. DAW, DAM, SMR...)');
    }

    public function test_student_can_register_selecting_groups_from_scroll_list(): void
    {
        [$curso, $grupos] = $this->createAcademicEnvironment(5);

        $selected = [$grupos[0]->id, $grupos[1]->id];

        $response = $this->post('/register', [
            'name' => 'Lucía Alumna',
            'email' => 'lucia@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'alumno',
            'grupos' => $selected,
            'consent_rgpd' => true,
            'privacy_policy' => true,
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));

        $user = User::where('email', 'lucia@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->alumno);
        $this->assertCount(2, $user->alumno->grupos);
        $this->assertTrue($user->alumno->grupos->contains($grupos[0]));
        $this->assertTrue($user->alumno->grupos->contains($grupos[1]));
    }

    public function test_registration_without_groups_shows_informative_warning(): void
    {
        CursoAcademico::create([
            'nombre' => '2026/2027',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-25',
            'is_active' => true,
        ]);

        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('No hay grupos disponibles en este momento');
        $response->assertDontSee('grupos-scroll-box');
    }
}
