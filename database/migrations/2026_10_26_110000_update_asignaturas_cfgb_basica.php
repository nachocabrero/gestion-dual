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
        $asignaturasBasica = [
            // 1º Curso (30h/sem)
            ['codigo' => 'MMEI', 'nombre' => 'Montaje y Mantenimiento de Sistemas y Componentes Informáticos', 'horas_semanales' => 9, 'es_practicas' => false],
            ['codigo' => 'OIM', 'nombre' => 'Operaciones Auxiliares para la Configuración y la Explotación', 'horas_semanales' => 7, 'es_practicas' => false],
            ['codigo' => 'IPE', 'nombre' => 'Itinerario Personal', 'horas_semanales' => 3, 'es_practicas' => false],
            ['codigo' => 'CCSI', 'nombre' => 'Comunicación y Ciencias Sociales I', 'horas_semanales' => 6, 'es_practicas' => false],
            ['codigo' => 'CAI', 'nombre' => 'Ciencias Aplicadas I', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'TUT1', 'nombre' => 'Tutoría I', 'horas_semanales' => 1, 'es_practicas' => false],
            // 2º Curso (30h/sem)
            ['codigo' => 'EEE', 'nombre' => 'Equipos Eléctricos y Electrónicos', 'horas_semanales' => 10, 'es_practicas' => false],
            ['codigo' => 'IMRD', 'nombre' => 'Instalación y Mantenimiento de Redes para Transmisión de Datos', 'horas_semanales' => 8, 'es_practicas' => false],
            ['codigo' => 'PIAC', 'nombre' => 'Proyecto Intermodular de Aprendizaje Colaborativo', 'horas_semanales' => 2, 'es_practicas' => true],
            ['codigo' => 'CCSII', 'nombre' => 'Comunicación y Ciencias Sociales II', 'horas_semanales' => 5, 'es_practicas' => false],
            ['codigo' => 'CAII', 'nombre' => 'Ciencias Aplicadas II', 'horas_semanales' => 4, 'es_practicas' => false],
            ['codigo' => 'TUT2', 'nombre' => 'Tutoría II', 'horas_semanales' => 1, 'es_practicas' => false],
        ];

        $validCodes = array_column($asignaturasBasica, 'codigo');

        $ciclosBasica = Ciclo::where('codigo', 'GBI')
            ->orWhere('grado', 'basica')
            ->orWhere('nombre', 'like', '%Oficina%')
            ->orWhere('nombre', 'like', '%Básica%')
            ->get();

        foreach ($ciclosBasica as $ciclo) {
            // Desasignar y eliminar asignaturas obsoletas (ej: FCT)
            $obsoletas = Asignatura::where('ciclo_id', $ciclo->id)
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

            // Crear o actualizar las 12 asignaturas oficiales
            foreach ($asignaturasBasica as $asigData) {
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
