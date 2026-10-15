<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Unifica "2º DAW mañana" y "2º DAW - Mañana", migra los alumnos registrados,
     * normaliza el formato de nombres de grupos ("Xº CODIGO - Turno"),
     * desactiva los grupos del curso anterior para evitar duplicados en la interfaz,
     * y crea los grupos faltantes del curso activo.
     */
    public function up(): void
    {
        // 1. Obtener el curso académico activo actual (ej: 2026-2027)
        $cursoActivo = DB::table('cursos_academicos')
            ->where('is_active', true)
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        if (!$cursoActivo) {
            // En tests con base de datos recién creada y limpia, no hay datos históricos que normalizar
            return;
        }

        $cursoActivoId = $cursoActivo->id;

        // 2. Normalizar el nombre del grupo 21 (2º DAW - Mañana del curso activo)
        DB::table('grupos')
            ->where('id', 21)
            ->update([
                'nombre' => '2º DAW - Mañana',
                'is_active' => true,
                'curso_academico_id' => $cursoActivoId,
                'updated_at' => now(),
            ]);

        // 3. Migrar alumnos registrados en grupo 2 hacia grupo 21
        $alumnosGrupo2 = DB::table('alumno_grupo')->where('grupo_id', 2)->get();
        foreach ($alumnosGrupo2 as $ag) {
            $existsIn21 = DB::table('alumno_grupo')
                ->where('alumno_id', $ag->alumno_id)
                ->where('grupo_id', 21)
                ->where('curso_academico_id', $cursoActivoId)
                ->exists();

            if ($existsIn21) {
                // Eliminar duplicado si ya existía en 21
                DB::table('alumno_grupo')->where('id', $ag->id)->delete();
            } else {
                // Mover a grupo 21 asignando el curso activo
                DB::table('alumno_grupo')
                    ->where('id', $ag->id)
                    ->update([
                        'grupo_id' => 21,
                        'curso_academico_id' => $cursoActivoId,
                        'updated_at' => now(),
                    ]);
            }
        }

        // Migrar profesores asignados al grupo 2 hacia el grupo 21
        if (DB::getSchemaBuilder()->hasTable('profesor_grupo')) {
            $profesoresGrupo2 = DB::table('profesor_grupo')->where('grupo_id', 2)->get();
            foreach ($profesoresGrupo2 as $pg) {
                $exists = DB::table('profesor_grupo')
                    ->where('profesor_id', $pg->profesor_id)
                    ->where('grupo_id', 21)
                    ->exists();

                if ($exists) {
                    DB::table('profesor_grupo')->where('id', $pg->id)->delete();
                } else {
                    DB::table('profesor_grupo')
                        ->where('id', $pg->id)
                        ->update(['grupo_id' => 21]);
                }
            }
        }

        // Migrar cualquier otra referencia de clave foránea de grupo 2 a grupo 21
        foreach (['convenios', 'proyectos', 'grupo_oferta', 'sustituciones'] as $tbl) {
            if (DB::getSchemaBuilder()->hasTable($tbl)) {
                DB::table($tbl)->where('grupo_id', 2)->update(['grupo_id' => 21]);
            }
        }

        // Eliminar grupo 2 ya unificado
        DB::table('grupos')->where('id', 2)->delete();

        // 4. Normalizar nombres de grupos existentes al formato "Xº CODIGO - Turno"
        DB::table('grupos')
            ->where('id', 19)
            ->update([
                'nombre' => '1º GBI - Mañana',
                'updated_at' => now(),
            ]);

        DB::table('grupos')
            ->where('id', 20)
            ->update([
                'nombre' => '2º GBI - Mañana',
                'updated_at' => now(),
            ]);

        // 5. Normalizar nombres de líneas
        DB::table('lineas')->where('id', 5)->update(['nombre' => 'SMR - Mañana']);
        DB::table('lineas')->where('id', 6)->update(['nombre' => 'SMR - Tarde']);
        DB::table('lineas')->where('id', 7)->update(['nombre' => 'GBI - Mañana']);

        // 6. Desactivar grupos de cursos académicos no activos para evitar duplicados en listas de selección
        DB::table('grupos')
            ->where('curso_academico_id', '!=', $cursoActivoId)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        // 7. Asegurar que todos los grupos estándar para el curso activo existen con el formato unificado
        $gruposCursoActivo = [
            ['linea_id' => 1, 'numero' => 1, 'nombre' => '1º DAW - Mañana'],
            ['linea_id' => 1, 'numero' => 2, 'nombre' => '2º DAW - Mañana'],
            ['linea_id' => 2, 'numero' => 1, 'nombre' => '1º DAW - Tarde'],
            ['linea_id' => 2, 'numero' => 2, 'nombre' => '2º DAW - Tarde'],
            ['linea_id' => 3, 'numero' => 1, 'nombre' => '1º DAM - Mañana'],
            ['linea_id' => 3, 'numero' => 2, 'nombre' => '2º DAM - Mañana'],
            ['linea_id' => 4, 'numero' => 1, 'nombre' => '1º DAM - Tarde'],
            ['linea_id' => 4, 'numero' => 2, 'nombre' => '2º DAM - Tarde'],
            ['linea_id' => 5, 'numero' => 1, 'nombre' => '1º SMR - Mañana'],
            ['linea_id' => 5, 'numero' => 2, 'nombre' => '2º SMR - Mañana'],
            ['linea_id' => 6, 'numero' => 1, 'nombre' => '1º SMR - Tarde'],
            ['linea_id' => 6, 'numero' => 2, 'nombre' => '2º SMR - Tarde'],
            ['linea_id' => 7, 'numero' => 1, 'nombre' => '1º GBI - Mañana'],
            ['linea_id' => 7, 'numero' => 2, 'nombre' => '2º GBI - Mañana'],
        ];

        foreach ($gruposCursoActivo as $def) {
            if (!DB::table('lineas')->where('id', $def['linea_id'])->exists()) {
                continue;
            }

            $grupoExistente = DB::table('grupos')
                ->where('linea_id', $def['linea_id'])
                ->where('numero', $def['numero'])
                ->where('curso_academico_id', $cursoActivoId)
                ->first();

            if ($grupoExistente) {
                DB::table('grupos')
                    ->where('id', $grupoExistente->id)
                    ->update([
                        'nombre' => $def['nombre'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('grupos')->insert([
                    'linea_id' => $def['linea_id'],
                    'curso_academico_id' => $cursoActivoId,
                    'numero' => $def['numero'],
                    'nombre' => $def['nombre'],
                    'tutor_id' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir datos destructivamente
    }
};
