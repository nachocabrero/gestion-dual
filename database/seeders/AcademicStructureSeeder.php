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
                    ['codigo' => 'LMSGI', 'nombre' => 'Lenguajes de Marcas y Sistemas de Gestión de Información', 'horas_semanales' => 3],
                    ['codigo' => 'SI', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 5],
                    ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 6],
                    ['codigo' => 'PROG', 'nombre' => 'Programación', 'horas_semanales' => 8],
                    ['codigo' => 'ED', 'nombre' => 'Entornos de Desarrollo', 'horas_semanales' => 3],
                    ['codigo' => 'DASP', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 1],
                    ['codigo' => 'SASP', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 1],
                    ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3],
                    ['codigo' => 'INGLES', 'nombre' => 'Inglés Profesional', 'horas_semanales' => 2],
                    ['codigo' => 'DAWEC', 'nombre' => 'Desarrollo Web en Entorno Cliente', 'horas_semanales' => 6],
                    ['codigo' => 'DAWES', 'nombre' => 'Desarrollo Web en Entorno Servidor', 'horas_semanales' => 7],
                    ['codigo' => 'DAWDAW', 'nombre' => 'Despliegue de Aplicaciones Web', 'horas_semanales' => 2],
                    ['codigo' => 'DAWDIW', 'nombre' => 'Diseño de Interfaces Web', 'horas_semanales' => 5],
                    ['codigo' => 'DAWPI', 'nombre' => 'Proyecto Intermodular de Desarrollo de Aplicaciones Web', 'horas_semanales' => 2, 'es_practicas' => true],
                    ['codigo' => 'IPEII', 'nombre' => 'Itinerario Personal para la Empleabilidad II (IPE II)', 'horas_semanales' => 3],
                    ['codigo' => 'CTOP', 'nombre' => 'Desarrollo de Agente IA', 'horas_semanales' => 3],
                ],
                'DAM' => [
                    ['codigo' => 'LMSGI', 'nombre' => 'Lenguajes de Marcas y Sistemas de Gestión de Información', 'horas_semanales' => 3],
                    ['codigo' => 'SI', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 5],
                    ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 6],
                    ['codigo' => 'PROG', 'nombre' => 'Programación', 'horas_semanales' => 8],
                    ['codigo' => 'ED', 'nombre' => 'Entornos de Desarrollo', 'horas_semanales' => 3],
                    ['codigo' => 'DASP', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 1],
                    ['codigo' => 'SASP', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 1],
                    ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3],
                    ['codigo' => 'INGLES', 'nombre' => 'Inglés Profesional', 'horas_semanales' => 2],
                    ['codigo' => 'AD', 'nombre' => 'Acceso a Datos', 'horas_semanales' => 4],
                    ['codigo' => 'DI', 'nombre' => 'Desarrollo de Interfaces', 'horas_semanales' => 6],
                    ['codigo' => 'PMDM', 'nombre' => 'Programación Multimedia y Dispositivos Móviles', 'horas_semanales' => 3],
                    ['codigo' => 'PSP', 'nombre' => 'Programación de Servicios y Procesos', 'horas_semanales' => 3],
                    ['codigo' => 'SGE', 'nombre' => 'Sistemas de Gestión Empresarial', 'horas_semanales' => 4],
                    ['codigo' => 'PIM', 'nombre' => 'Proyecto Intermodular de Desarrollo de Aplicaciones Multiplataforma', 'horas_semanales' => 2, 'es_practicas' => true],
                    ['codigo' => 'IPEII', 'nombre' => 'Itinerario Personal para la Empleabilidad II (IPE II)', 'horas_semanales' => 3],
                    ['codigo' => 'CTOP', 'nombre' => 'Desarrollo Avanzado de Videojuegos', 'horas_semanales' => 3],
                ],
                'SMR' => [
                    ['codigo' => 'MME', 'nombre' => 'Montaje y Mantenimiento de Equipos', 'horas_semanales' => 6],
                    ['codigo' => 'SOM', 'nombre' => 'Sistemas Operativos Monopuesto', 'horas_semanales' => 5],
                    ['codigo' => 'AO', 'nombre' => 'Aplicaciones Ofimáticas', 'horas_semanales' => 7],
                    ['codigo' => 'RL', 'nombre' => 'Redes Locales', 'horas_semanales' => 7],
                    ['codigo' => 'DASP', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 1],
                    ['codigo' => 'SASP', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 1],
                    ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3],
                    ['codigo' => 'INGLES', 'nombre' => 'Inglés Profesional', 'horas_semanales' => 2],
                    ['codigo' => 'SOR', 'nombre' => 'Sistemas Operativos en Red', 'horas_semanales' => 6],
                    ['codigo' => 'SEG', 'nombre' => 'Seguridad Informática', 'horas_semanales' => 4],
                    ['codigo' => 'SER', 'nombre' => 'Servicios en Red', 'horas_semanales' => 6],
                    ['codigo' => 'AW', 'nombre' => 'Aplicaciones Web', 'horas_semanales' => 4],
                    ['codigo' => 'IPEII', 'nombre' => 'Itinerario Personal para la Empleabilidad II (IPE II)', 'horas_semanales' => 3],
                    ['codigo' => 'PIM', 'nombre' => 'Proyecto Intermodular', 'horas_semanales' => 2, 'es_practicas' => true],
                    ['codigo' => 'CTOP', 'nombre' => 'Ciberseguridad en PYMES', 'horas_semanales' => 3],
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