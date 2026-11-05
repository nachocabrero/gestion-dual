<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. IDs identificados en la base de datos como duplicados o grupos obsoletos sin alumnos:
        // - ID 1, 3, 4, 5, 6, 7, 8: Duplicados del curso 2025-2026 sin alumnos
        // - ID 9, 10, 11: Grupos de ASIR (ciclo inexistente en el centro)
        // - ID 555: 2º SMR Tarde (en el centro solo existe SMR Mañana)
        // - ID 556, 557: 1º y 2º GBI Tarde (en el centro solo existe GBI Mañana)
        $idsAEliminar = [1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 555, 556, 557];

        // 2. También incluir cualquier grupo que contenga "ASIR", "SMR - Tarde", "GBI - Tarde" sin alumnos
        $extraIds = DB::table('grupos')
            ->where(function ($q) {
                $q->where('nombre', 'like', '%ASIR%')
                  ->orWhere('nombre', 'like', '%SMR%Tarde%')
                  ->orWhere('nombre', 'like', '%GBI%Tarde%');
            })
            ->pluck('id')
            ->toArray();

        $todosIds = array_values(array_unique(array_merge($idsAEliminar, $extraIds)));

        if (!empty($todosIds)) {
            // Eliminar dependencias en tablas pivote o relacionadas
            DB::table('grupo_profesor')->whereIn('grupo_id', $todosIds)->delete();
            DB::table('alumno_grupo')->whereIn('grupo_id', $todosIds)->delete();
            DB::table('grupo_oferta')->whereIn('grupo_id', $todosIds)->delete();
            DB::table('convenios')->whereIn('grupo_id', $todosIds)->delete();
            DB::table('proyectos')->whereIn('grupo_id', $todosIds)->delete();
            DB::table('sustituciones')->whereIn('grupo_id', $todosIds)->delete();

            // Eliminar los grupos no útiles
            DB::table('grupos')->whereIn('id', $todosIds)->delete();
        }

        // 3. Desactivar líneas de turnos que no existen en el centro (SMR Tarde y GBI Tarde)
        DB::table('lineas')
            ->where(function ($q) {
                $q->where('nombre', 'like', '%SMR%Tarde%')
                  ->orWhere('nombre', 'like', '%GBI%Tarde%');
            })
            ->update(['is_active' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir eliminación de datos obsoletos
    }
};
