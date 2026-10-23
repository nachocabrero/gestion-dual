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
        // 1. Curso académico activo actual
        $cursoActual = \App\Models\CursoAcademico::firstOrCreate(
            ['nombre' => '2026-2027'],
            [
                'fecha_inicio' => '2026-09-01',
                'fecha_fin' => '2027-06-30',
                'is_active' => true,
            ]
        );
        if (!$cursoActual->is_active && !\App\Models\CursoAcademico::where('is_active', true)->exists()) {
            $cursoActual->update(['is_active' => true]);
        }

        // 2. Familia de Informática
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
            ['codigo' => 'SMR', 'nombre' => 'Sistemas Microinformáticos y Redes', 'grado' => 'media', 'duracion_anos' => 2],
            ['codigo' => 'GBI', 'nombre' => 'Informática de Oficina', 'grado' => 'basica', 'duracion_anos' => 2],
        ];

        foreach ($ciclosData as $cicloData) {
            $ciclo = Ciclo::firstOrCreate(
                ['codigo' => $cicloData['codigo']],
                array_merge($cicloData, ['familia_id' => $familia->id, 'is_active' => true])
            );

            // Asignaturas del ciclo
            $asignaturasData = match($ciclo->codigo) {
                'DAW' => [
                    ['codigo' => 'PROG', 'nombre' => 'Programación', 'horas_semanales' => 8],
                    ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 6],
                    ['codigo' => 'SI', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 6],
                    ['codigo' => 'LMSGI', 'nombre' => 'Lenguajes de Marcas y Sistemas de Gestión de Información', 'horas_semanales' => 4],
                    ['codigo' => 'ED', 'nombre' => 'Entornos de Desarrollo', 'horas_semanales' => 3],
                    ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3],
                    ['codigo' => 'DIG', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 2],
                    ['codigo' => 'SOST', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 2],
                    ['codigo' => 'DAWES', 'nombre' => 'Desarrollo en Entorno Servidor', 'horas_semanales' => 8],
                    ['codigo' => 'DAWEC', 'nombre' => 'Desarrollo en Entorno Cliente', 'horas_semanales' => 7],
                    ['codigo' => 'DAWDIW', 'nombre' => 'Desarrollo de Interfaces Web', 'horas_semanales' => 6],
                    ['codigo' => 'DAWDAW', 'nombre' => 'Despliegue de Aplicaciones Web', 'horas_semanales' => 4],
                    ['codigo' => 'DAWPI', 'nombre' => 'Proyecto Intermodular', 'horas_semanales' => 5, 'es_practicas' => true],
                ],
                'DAM' => [
                    ['codigo' => 'PROG', 'nombre' => 'Programación', 'horas_semanales' => 8],
                    ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 6],
                    ['codigo' => 'SI', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 6],
                    ['codigo' => 'LMSGI', 'nombre' => 'Lenguajes de Marcas y Sistemas de Gestión de Información', 'horas_semanales' => 4],
                    ['codigo' => 'ED', 'nombre' => 'Entornos de Desarrollo', 'horas_semanales' => 3],
                    ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3],
                    ['codigo' => 'DIG', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 2],
                    ['codigo' => 'SOST', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 2],
                    ['codigo' => 'AD', 'nombre' => 'Acceso a Datos', 'horas_semanales' => 5],
                    ['codigo' => 'DI', 'nombre' => 'Desarrollo de Interfaces', 'horas_semanales' => 6],
                    ['codigo' => 'PMDM', 'nombre' => 'Programación Multimedia y Dispositivos Móviles', 'horas_semanales' => 4],
                    ['codigo' => 'PSP', 'nombre' => 'Programación de Servicios y Procesos', 'horas_semanales' => 4],
                    ['codigo' => 'SGE', 'nombre' => 'Sistemas de Gestión Empresarial', 'horas_semanales' => 4],
                    ['codigo' => 'ING', 'nombre' => 'Inglés Profesional (o módulo técnico equivalente de idiomas aplicado al entorno laboral)', 'horas_semanales' => 2],
                    ['codigo' => 'IPEII', 'nombre' => 'Itinerario Personal para la Empleabilidad II (IPE II)', 'horas_semanales' => 3],
                    ['codigo' => 'PI', 'nombre' => 'Proyecto Intermodular', 'horas_semanales' => 5, 'es_practicas' => true],
                ],
                'SMR' => [
                    ['codigo' => 'MME', 'nombre' => 'Montaje y Mantenimiento de Equipos', 'horas_semanales' => 6],
                    ['codigo' => 'SOM', 'nombre' => 'Sistemas Operativos Monopuesto', 'horas_semanales' => 6],
                    ['codigo' => 'RL', 'nombre' => 'Redes Locales', 'horas_semanales' => 4],
                    ['codigo' => 'FG', 'nombre' => 'Formación y Orientación Laboral', 'horas_semanales' => 2],
                    ['codigo' => 'SR', 'nombre' => 'Seguridad Informática', 'horas_semanales' => 4],
                    ['codigo' => 'FCT', 'nombre' => 'Formación en Centros de Trabajo', 'horas_semanales' => 4, 'es_practicas' => true],
                ],
                'GBI' => [
                    ['codigo' => 'MMEI', 'nombre' => 'Montaje y Mantenimiento de Sistemas y Componentes Informáticos', 'horas_semanales' => 6],
                    ['codigo' => 'OIM', 'nombre' => 'Operaciones Auxiliares para la Configuración y Explotación', 'horas_semanales' => 6],
                    ['codigo' => 'FCT', 'nombre' => 'Formación en Centros de Trabajo', 'horas_semanales' => 4, 'es_practicas' => true],
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
                    $grupo = Grupo::firstOrCreate(
                        ['linea_id' => $linea->id, 'numero' => $i],
                        [
                            'nombre' => "{$i}º {$cicloData['codigo']} - {$nombre}",
                            'curso_academico_id' => $cursoActual->id,
                            'is_active' => true,
                        ]
                    );
                    if (!$grupo->curso_academico_id) {
                        $grupo->update(['curso_academico_id' => $cursoActual->id]);
                    }
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