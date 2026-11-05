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
use App\Models\Practica;
use App\Models\SolicitudPractica;
use App\Models\User;
use Tests\TestCase;

class AlumnoIndexColumnasYColoresTest extends TestCase
{
    private function createAcademicContext(): array
    {
        $curso = CursoAcademico::where('is_active', true)->first() ?? CursoAcademico::create([
            'nombre' => '2026-2027',
            'is_active' => true,
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2027-06-30',
        ]);

        $familia = Familia::firstOrCreate(['codigo' => 'INF_TEST_COL'], ['nombre' => 'Informática Test Col', 'is_active' => true]);
        $ciclo = Ciclo::firstOrCreate(['codigo' => 'DAW_COL'], ['familia_id' => $familia->id, 'nombre' => 'DAW Col', 'grado' => 'superior', 'is_active' => true]);
        $linea = Linea::firstOrCreate(['ciclo_id' => $ciclo->id, 'turno' => 'manana'], ['nombre' => 'DAW Mañana Col', 'is_active' => true]);
        $grupo = Grupo::firstOrCreate(['linea_id' => $linea->id, 'curso_academico_id' => $curso->id, 'numero' => 2], ['nombre' => '2º DAW Mañana Col', 'is_active' => true]);

        return compact('curso', 'familia', 'ciclo', 'linea', 'grupo');
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'roles' => [User::ROLE_ADMIN],
            'is_active' => true,
            'registration_status' => User::STATUS_APPROVED,
            'consent_rgpd' => true,
            'consent_rgpd_at' => now(),
        ]);
    }

    /**
     * Verificar que la columna Estado ya no está en la cabecera y Ciclos y Grupos están fusionados.
     */
    public function test_alumnos_index_sin_columna_estado_y_con_ciclos_y_grupos_fusionados(): void
    {
        $admin = $this->createAdmin();
        $ctx = $this->createAcademicContext();

        $user = User::factory()->create(['name' => 'Alumno Test Fusión', 'roles' => [User::ROLE_ALUMNO]]);
        $alumno = Alumno::create(['user_id' => $user->id]);
        $alumno->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $response = $this->actingAs($admin)->get(route('alumnos.index', ['search' => 'Alumno Test Fusión']));

        $response->assertOk();
        // Verificar cabecera fusionada
        $response->assertSee('<th>Ciclos y Grupos</th>', false);
        // Verificar que no hay columna Estado en thead
        $response->assertDontSee('<th class="text-center">Estado</th>', false);
        // Verificar que en la fila se muestran tanto el código del ciclo como el nombre del grupo
        $response->assertSee($ctx['ciclo']->codigo);
        $response->assertSee($ctx['grupo']->nombre);
    }

    /**
     * Verificar el código de colores en Prácticas 2º:
     * - Naranja: No hace prácticas (Repite 1º)
     * - Verde: Confirmada (con práctica formal creada)
     * - Amarillo: Propuesta (asignada en oferta sin confirmar)
     * - Gris: Sin asignar
     */
    public function test_columna_practicas_segundo_semaforo_colores(): void
    {
        $admin = $this->createAdmin();
        $ctx = $this->createAcademicContext();

        // 1. Alumno que NO hace prácticas (repitiendo módulos de 1º) -> Naranja
        $uNoHace = User::factory()->create(['name' => 'Alumno Naranja No Hace', 'roles' => [User::ROLE_ALUMNO]]);
        $aNoHace = Alumno::create([
            'user_id' => $uNoHace->id,
            'repitiendo_modulos_primero' => true,
        ]);
        $aNoHace->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        // 2. Alumno con práctica CONFIRMADA (creada formalmente en Practica) -> Verde
        $empresaVerde = Empresa::create([
            'nombre' => 'Empresa Verde Confirmada SL',
            'cif' => 'B' . rand(10000000, 99999999),
            'email' => 'verde_' . uniqid() . '@test.com',
            'is_active' => true,
        ]);
        $uConfirmado = User::factory()->create(['name' => 'Alumno Verde Confirmado', 'roles' => [User::ROLE_ALUMNO]]);
        $aConfirmado = Alumno::create(['user_id' => $uConfirmado->id]);
        $aConfirmado->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);
        Practica::create([
            'alumno_id' => $aConfirmado->id,
            'empresa_id' => $empresaVerde->id,
            'curso_academico_id' => $ctx['curso']->id,
            'fecha_inicio' => now()->subMonth(),
            'estado' => 'activa',
            'tipo' => 'dual',
        ]);

        // 3. Alumno con PROPUESTA (asignado/solicitud en oferta pero sin práctica formal) -> Amarillo
        $empresaAmarilla = Empresa::create([
            'nombre' => 'Empresa Amarilla Propuesta SL',
            'cif' => 'B' . rand(10000000, 99999999),
            'email' => 'amarilla_' . uniqid() . '@test.com',
            'is_active' => true,
        ]);
        $oferta = OfertaPractica::create([
            'empresa_id' => $empresaAmarilla->id,
            'curso_academico_id' => $ctx['curso']->id,
            'titulo' => 'Oferta Dual DAW Desarrollo',
            'descripcion' => 'Desarrollo web',
            'especialidad_requerida' => 'Desarrollo Web',
            'plazas' => 2,
            'estado' => 'solicitada',
        ]);
        $uPropuesta = User::factory()->create(['name' => 'Alumno Amarillo Propuesta', 'roles' => [User::ROLE_ALUMNO]]);
        $aPropuesta = Alumno::create(['user_id' => $uPropuesta->id]);
        $aPropuesta->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);
        SolicitudPractica::create([
            'alumno_id' => $aPropuesta->id,
            'oferta_id' => $oferta->id,
            'estado' => 'aceptado',
        ]);

        // 4. Alumno sin asignar -> Gris
        $uSinAsignar = User::factory()->create(['name' => 'Alumno Gris Sin Asignar', 'roles' => [User::ROLE_ALUMNO]]);
        $aSinAsignar = Alumno::create(['user_id' => $uSinAsignar->id]);
        $aSinAsignar->grupos()->attach($ctx['grupo']->id, ['curso_academico_id' => $ctx['curso']->id]);

        $this->actingAs($admin);

        // Comprobación Alumno 1 (Naranja)
        $r1 = $this->get(route('alumnos.index', ['search' => 'Alumno Naranja No Hace']));
        $r1->assertOk();
        $r1->assertSee('bg-orange-100', false);
        $r1->assertSee('No hace prácticas');

        // Comprobación Alumno 2 (Verde)
        $r2 = $this->get(route('alumnos.index', ['search' => 'Alumno Verde Confirmado']));
        $r2->assertOk();
        $r2->assertSee('bg-emerald-100', false);
        $r2->assertSee('Empresa Verde Confirmada SL');
        $r2->assertSee('Confirmada');

        // Comprobación Alumno 3 (Amarillo)
        $r3 = $this->get(route('alumnos.index', ['search' => 'Alumno Amarillo Propuesta']));
        $r3->assertOk();
        $r3->assertSee('bg-amber-100', false);
        $r3->assertSee('Empresa Amarilla Propuesta SL');
        $r3->assertSee('Propuesta (sin confirmar)');

        // Comprobación Alumno 4 (Sin asignar)
        $r4 = $this->get(route('alumnos.index', ['search' => 'Alumno Gris Sin Asignar']));
        $r4->assertOk();
        $r4->assertSee('Sin asignar');
    }
}
