<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\Ciclo;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Familia de Informática
        $familia = Familia::firstOrCreate(
            ['codigo' => 'INFORMATICA'],
            [
                'nombre' => 'Familia de Informática',
                'descripcion' => 'Familia profesional de Informática y Comunicaciones',
                'is_active' => true,
            ]
        );

        // 2. Ciclos
        $ciclosData = [
            ['codigo' => 'DAW', 'nombre' => 'Desarrollo de Aplicaciones Web', 'grado' => 'superior', 'duracion_anos' => 2],
            ['codigo' => 'DAM', 'nombre' => 'Desarrollo de Aplicaciones Multiplataforma', 'grado' => 'superior', 'duracion_anos' => 2],
            ['codigo' => 'ASIR', 'nombre' => 'Administración de Sistemas Informáticos en Red', 'grado' => 'superior', 'duracion_anos' => 2],
        ];

        foreach ($ciclosData as $cicloData) {
            $ciclo = Ciclo::firstOrCreate(
                ['codigo' => $cicloData['codigo']],
                array_merge($cicloData, ['familia_id' => $familia->id, 'is_active' => true])
            );

            // Asignaturas del ciclo
            $asignaturasData = match($ciclo->codigo) {
                'DAW' => [
                    ['codigo' => 'DAWES', 'nombre' => 'Desarrollo en Entorno Servidor', 'horas_semanales' => 6],
                    ['codigo' => 'DAWEC', 'nombre' => 'Desarrollo en Entorno Cliente', 'horas_semanales' => 6],
                    ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 4],
                    ['codigo' => 'FG', 'nombre' => 'Formación y Orientación Laboral', 'horas_semanales' => 2],
                    ['codigo' => 'DACC', 'nombre' => 'Desarrollo de Interficies Web', 'horas_semanales' => 4],
                    ['codigo' => 'PRC', 'nombre' => 'Proyecto de Desarrollo de Aplicaciones Web', 'horas_semanales' => 4, 'es_practicas' => true],
                ],
                'DAM' => [
                    ['codigo' => 'DAMES', 'nombre' => 'Desarrollo en Entorno Servidor', 'horas_semanales' => 6],
                    ['codigo' => 'DAMC', 'nombre' => 'Desarrollo en Entorno Cliente', 'horas_semanales' => 6],
                    ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 4],
                    ['codigo' => 'FG', 'nombre' => 'Formación y Orientación Laboral', 'horas_semanales' => 2],
                    ['codigo' => 'DAIM', 'nombre' => 'Desarrollo de Aplicaciones Multiplataforma', 'horas_semanales' => 4],
                    ['codigo' => 'PRD', 'nombre' => 'Proyecto de Desarrollo de Aplicaciones Multiplataforma', 'horas_semanales' => 4, 'es_practicas' => true],
                ],
                'ASIR' => [
                    ['codigo' => 'SIO', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 6],
                    ['codigo' => 'SI', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 6],
                    ['codigo' => 'ACC', 'nombre' => 'Acceso a Datos', 'horas_semanales' => 4],
                    ['codigo' => 'FG', 'nombre' => 'Formación y Orientación Laboral', 'horas_semanales' => 2],
                    ['codigo' => 'SS', 'nombre' => 'Seguridad Informática', 'horas_semanales' => 4],
                    ['codigo' => 'PPR', 'nombre' => 'Proyecto de Administración de Sistemas Informáticos en Red', 'horas_semanales' => 4, 'es_practicas' => true],
                ],
            };

            foreach ($asignaturasData as $asigData) {
                Asignatura::firstOrCreate(
                    ['ciclo_id' => $ciclo->id, 'codigo' => $asigData['codigo']],
                    array_merge($asigData, ['is_active' => true])
                );
            }

            // 3. Líneas y 4. Grupos
            foreach (['manana' => 'Mañana', 'tarde' => 'Tarde'] as $turno => $nombre) {
                $linea = Linea::firstOrCreate(
                    ['ciclo_id' => $ciclo->id, 'turno' => $turno],
                    ['nombre' => "{$cicloData['codigo']} - {$nombre}", 'is_active' => true]
                );

                for ($i = 1; $i <= 2; $i++) {
                    Grupo::firstOrCreate(
                        ['linea_id' => $linea->id, 'numero' => $i],
                        ['nombre' => "{$i}º {$cicloData['codigo']} - {$nombre}", 'is_active' => true]
                    );
                }
            }
        }

        // Asignar tutores a los primeros grupos
        $tutores = User::whereJsonContains('roles', 'profesor')
                       ->orWhereJsonContains('roles', 'coordinador_dual')
                       ->take(5)
                       ->get();

        $grupos = Grupo::where('is_active', true)->take(5)->get();
        foreach ($grupos as $index => $grupo) {
            if (isset($tutores[$index])) {
                $grupo->update(['tutor_id' => $tutores[$index]->id]);
            }
        }
    }
}