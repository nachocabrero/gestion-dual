<?php

namespace Database\Seeders;

use App\Models\Familia;
use App\Models\Ciclo;
use App\Models\Linea;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea la estructura académica estándar del IES Hermenegildo Lanz:
     * - Familia de Informática
     * - Ciclos: DAW, DAM, ASIR
     * - Líneas: mañana y tarde
     * - Grupos con tutores
     */
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
            [
                'codigo' => 'DAW',
                'nombre' => 'Desarrollo de Aplicaciones Web',
                'grado' => 'superior',
                'duracion_anos' => 2,
            ],
            [
                'codigo' => 'DAM',
                'nombre' => 'Desarrollo de Aplicaciones Multiplataforma',
                'grado' => 'superior',
                'duracion_anos' => 2,
            ],
            [
                'codigo' => 'ASIR',
                'nombre' => 'Administración de Sistemas Informáticos en Red',
                'grado' => 'superior',
                'duracion_anos' => 2,
            ],
        ];

        foreach ($ciclosData as $cicloData) {
            $ciclo = Ciclo::firstOrCreate(
                ['codigo' => $cicloData['codigo']],
                array_merge($cicloData, [
                    'familia_id' => $familia->id,
                    'is_active' => true,
                ])
            );

            // 3. Líneas (mañana y tarde)
            foreach (['manana' => 'Mañana', 'tarde' => 'Tarde'] as $turno => $nombre) {
                $linea = Linea::firstOrCreate(
                    ['ciclo_id' => $ciclo->id, 'turno' => $turno],
                    [
                        'nombre' => "{$cicloData['codigo']} - {$nombre}",
                        'is_active' => true,
                    ]
                );

                // 4. Grupos (1-3 por línea)
                for ($i = 1; $i <= 2; $i++) {
                    Grupo::firstOrCreate(
                        ['linea_id' => $linea->id, 'numero' => $i],
                        [
                            'nombre' => "{$i}º {$cicloData['codigo']} - {$nombre}",
                            'is_active' => true,
                        ]
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