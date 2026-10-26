<?php

use App\Models\Asignatura;
use App\Models\Ciclo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Helper to desassign teachers and remove obsolete subjects.
     */
    private function cleanObsoleteSubjects(int $cicloId, array $validCodes): void
    {
        $obsoletas = Asignatura::where('ciclo_id', $cicloId)
            ->whereNotIn('codigo', $validCodes)
            ->get();

        foreach ($obsoletas as $obsoleta) {
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
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. SMR (Sistemas Microinformáticos y Redes)
        $asignaturasSmr = [
            // 1º Curso
            ['codigo' => 'MME', 'nombre' => 'Montaje y Mantenimiento de Equipos', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'SOM', 'nombre' => 'Sistemas Operativos Monopuesto', 'horas_semanales' => 5, 'es_practicas' => false],
            ['codigo' => 'AO', 'nombre' => 'Aplicaciones Ofimáticas', 'horas_semanales' => 7, 'es_practicas' => false],
            ['codigo' => 'RL', 'nombre' => 'Redes Locales', 'horas_semanales' => 7, 'es_practicas' => false],
            ['codigo' => 'DASP', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 1, 'es_practicas' => false],
            ['codigo' => 'SASP', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 1, 'es_practicas' => false],
            ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'INGLES', 'nombre' => 'Inglés Profesional', 'horas_semanales' => 2, 'es_practicas' => false],
            // 2º Curso
            ['codigo' => 'SOR', 'nombre' => 'Sistemas Operativos en Red', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'SEG', 'nombre' => 'Seguridad Informática', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'SER', 'nombre' => 'Servicios en Red', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'AW', 'nombre' => 'Aplicaciones Web', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'IPEII', 'nombre' => 'Itinerario Personal para la Empleabilidad II (IPE II)', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'PIM', 'nombre' => 'Proyecto Intermodular', 'horas_semanales' => 2, 'es_practicas' => true],
            ['codigo' => 'CTOP', 'nombre' => 'Ciberseguridad en PYMES', 'horas_semanales' => 3, 'es_practicas' => false],
        ];

        // 2. DAW (Desarrollo de Aplicaciones Web)
        $asignaturasDaw = [
            // 1º Curso
            ['codigo' => 'LMSGI', 'nombre' => 'Lenguajes de Marcas y Sistemas de Gestión de Información', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'SI', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 5, 'es_practicas' => false],
            ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'PROG', 'nombre' => 'Programación', 'horas_semanales' => 8, 'es_practicas' => false],
            ['codigo' => 'ED', 'nombre' => 'Entornos de Desarrollo', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'DASP', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 1, 'es_practicas' => false],
            ['codigo' => 'SASP', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 1, 'es_practicas' => false],
            ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'INGLES', 'nombre' => 'Inglés Profesional', 'horas_semanales' => 2, 'es_practicas' => false],
            // 2º Curso
            ['codigo' => 'DAWEC', 'nombre' => 'Desarrollo Web en Entorno Cliente', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'DAWES', 'nombre' => 'Desarrollo Web en Entorno Servidor', 'horas_semanales' => 7, 'es_practicas' => false],
            ['codigo' => 'DAWDAW', 'nombre' => 'Despliegue de Aplicaciones Web', 'horas_semanales' => 2, 'es_practicas' => false],
            ['codigo' => 'DAWDIW', 'nombre' => 'Diseño de Interfaces Web', 'horas_semanales' => 5, 'es_practicas' => false],
            ['codigo' => 'DAWPI', 'nombre' => 'Proyecto Intermodular de Desarrollo de Aplicaciones Web', 'horas_semanales' => 2, 'es_practicas' => true],
            ['codigo' => 'IPEII', 'nombre' => 'Itinerario Personal para la Empleabilidad II (IPE II)', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'CTOP', 'nombre' => 'Desarrollo de Agente IA', 'horas_semanales' => 3, 'es_practicas' => false],
        ];

        // 3. DAM (Desarrollo de Aplicaciones Multiplataforma)
        $asignaturasDam = [
            // 1º Curso
            ['codigo' => 'LMSGI', 'nombre' => 'Lenguajes de Marcas y Sistemas de Gestión de Información', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'SI', 'nombre' => 'Sistemas Informáticos', 'horas_semanales' => 5, 'es_practicas' => false],
            ['codigo' => 'BD', 'nombre' => 'Bases de Datos', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'PROG', 'nombre' => 'Programación', 'horas_semanales' => 8, 'es_practicas' => false],
            ['codigo' => 'ED', 'nombre' => 'Entornos de Desarrollo', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'DASP', 'nombre' => 'Digitalización Aplicada a los Sectores Productivos', 'horas_semanales' => 1, 'es_practicas' => false],
            ['codigo' => 'SASP', 'nombre' => 'Sostenibilidad Aplicada al Sistema Productivo', 'horas_semanales' => 1, 'es_practicas' => false],
            ['codigo' => 'IPEI', 'nombre' => 'Itinerario Personal para la Empleabilidad I (IPE I)', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'INGLES', 'nombre' => 'Inglés Profesional', 'horas_semanales' => 2, 'es_practicas' => false],
            // 2º Curso
            ['codigo' => 'AD', 'nombre' => 'Acceso a Datos', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'DI', 'nombre' => 'Desarrollo de Interfaces', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'PMDM', 'nombre' => 'Programación Multimedia y Dispositivos Móviles', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'PSP', 'nombre' => 'Programación de Servicios y Procesos', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'SGE', 'nombre' => 'Sistemas de Gestión Empresarial', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'PIM', 'nombre' => 'Proyecto Intermodular de Desarrollo de Aplicaciones Multiplataforma', 'horas_semanales' => 2, 'es_practicas' => true],
            ['codigo' => 'IPEII', 'nombre' => 'Itinerario Personal para la Empleabilidad II (IPE II)', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'CTOP', 'nombre' => 'Desarrollo Avanzado de Videojuegos', 'horas_semanales' => 3, 'es_practicas' => false],
        ];

        // Procesar SMR
        $ciclosSmr = Ciclo::where('codigo', 'SMR')->orWhere('nombre', 'like', '%Microinformáticos%')->get();
        foreach ($ciclosSmr as $ciclo) {
            // Mapear código antiguo SR a SEG si existe
            Asignatura::where('ciclo_id', $ciclo->id)->where('codigo', 'SR')->update(['codigo' => 'SEG']);

            $validCodes = array_column($asignaturasSmr, 'codigo');
            $this->cleanObsoleteSubjects($ciclo->id, $validCodes);

            foreach ($asignaturasSmr as $asigData) {
                Asignatura::updateOrCreate(
                    ['ciclo_id' => $ciclo->id, 'codigo' => $asigData['codigo']],
                    array_merge($asigData, ['is_active' => true])
                );
            }
        }

        // Procesar DAW
        $ciclosDaw = Ciclo::where('codigo', 'DAW')->orWhere('nombre', 'like', '%Web%')->get();
        foreach ($ciclosDaw as $ciclo) {
            $validCodes = array_column($asignaturasDaw, 'codigo');
            $this->cleanObsoleteSubjects($ciclo->id, $validCodes);

            foreach ($asignaturasDaw as $asigData) {
                Asignatura::updateOrCreate(
                    ['ciclo_id' => $ciclo->id, 'codigo' => $asigData['codigo']],
                    array_merge($asigData, ['is_active' => true])
                );
            }
        }

        // Procesar DAM
        $ciclosDam = Ciclo::where('codigo', 'DAM')->orWhere('nombre', 'like', '%Multiplataforma%')->get();
        foreach ($ciclosDam as $ciclo) {
            $validCodes = array_column($asignaturasDam, 'codigo');
            $this->cleanObsoleteSubjects($ciclo->id, $validCodes);

            foreach ($asignaturasDam as $asigData) {
                Asignatura::updateOrCreate(
                    ['ciclo_id' => $ciclo->id, 'codigo' => $asigData['codigo']],
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
