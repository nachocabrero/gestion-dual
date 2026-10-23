<?php

use App\Models\Asignatura;
use App\Models\Ciclo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1º Curso Asignaturas (Comunes para DAM y DAW)
        $asignaturasPrimero = [
            ['codigo' => 'PROG', 'nombre' => 'Programación', 'horas_semanales' => 8, 'es_practicas' => false],
            ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'SI', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 5, 'es_practicas' => false],
            ['codigo' => 'LMSGI', 'nombre' => 'Lenguajes de Marcas y Sistemas de Gestión de Información', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'ED', 'nombre' => 'Entornos de Desarrollo', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'DASP', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 1, 'es_practicas' => false],
            ['codigo' => 'SASP', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 1, 'es_practicas' => false],
        ];

        // 2º Curso Asignaturas DAM
        $asignaturasSegundoDam = [
            ['codigo' => 'AD', 'nombre' => 'Acceso a Datos', 'horas_semanales' => 5, 'es_practicas' => false],
            ['codigo' => 'DI', 'nombre' => 'Desarrollo de Interfaces', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'PMDM', 'nombre' => 'Programación Multimedia y Dispositivos Móviles', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'PSP', 'nombre' => 'Programación de Servicios y Procesos', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'SGE', 'nombre' => 'Sistemas de Gestión Empresarial', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'INGLES', 'nombre' => 'Inglés Profesional (o módulo técnico equivalente de idiomas aplicado al entorno laboral)', 'horas_semanales' => 2, 'es_practicas' => false],
            ['codigo' => 'IPEII', 'nombre' => 'Itinerario Personal para la Empleabilidad II (IPE II)', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'PIM', 'nombre' => 'Proyecto Intermodular', 'horas_semanales' => 3, 'es_practicas' => true],
        ];

        $ciclosDam = Ciclo::where('codigo', 'DAM')
            ->orWhere('nombre', 'like', '%Multiplataforma%')
            ->get();

        $ciclosDaw = Ciclo::where('codigo', 'DAW')
            ->orWhere('nombre', 'like', '%Web%')
            ->get();

        // 1. Actualizar DAM
        foreach ($ciclosDam as $cicloDam) {
            $codigosValidosDam = array_merge(
                array_column($asignaturasPrimero, 'codigo'),
                array_column($asignaturasSegundoDam, 'codigo')
            );

            // Obtener asignaturas obsoletas a eliminar en este ciclo
            $asignaturasObsoletas = Asignatura::where('ciclo_id', $cicloDam->id)
                ->whereNotIn('codigo', $codigosValidosDam)
                ->get();

            foreach ($asignaturasObsoletas as $obsoleta) {
                // Desasignar profesores
                if (Schema::hasTable('profesor_asignatura')) {
                    DB::table('profesor_asignatura')->where('asignatura_id', $obsoleta->id)->delete();
                }
                if (Schema::hasTable('grupo_profesor')) {
                    DB::table('grupo_profesor')->where('asignatura_id', $obsoleta->id)->delete();
                }
                if (Schema::hasTable('sustituciones')) {
                    DB::table('sustituciones')->where('asignatura_id', $obsoleta->id)->update(['asignatura_id' => null]);
                }
                if (Schema::hasTable('calificaciones')) {
                    DB::table('calificaciones')->where('asignatura_id', $obsoleta->id)->delete();
                }
                $obsoleta->delete();
            }

            // Crear o actualizar las 8 de primero
            foreach ($asignaturasPrimero as $asigData) {
                Asignatura::updateOrCreate(
                    ['ciclo_id' => $cicloDam->id, 'codigo' => $asigData['codigo']],
                    array_merge($asigData, ['is_active' => true])
                );
            }

            // Crear o actualizar las 8 de segundo
            foreach ($asignaturasSegundoDam as $asigData) {
                Asignatura::updateOrCreate(
                    ['ciclo_id' => $cicloDam->id, 'codigo' => $asigData['codigo']],
                    array_merge($asigData, ['is_active' => true])
                );
            }
        }

        // 2. Actualizar 1º en DAW
        foreach ($ciclosDaw as $cicloDaw) {
            foreach ($asignaturasPrimero as $asigData) {
                Asignatura::updateOrCreate(
                    ['ciclo_id' => $cicloDaw->id, 'codigo' => $asigData['codigo']],
                    array_merge($asigData, ['is_active' => true])
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo
    }
};
