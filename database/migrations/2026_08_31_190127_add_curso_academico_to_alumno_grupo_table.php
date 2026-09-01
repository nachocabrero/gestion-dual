<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La columna ya pudo crearse en una ejecución previa fallida (MySQL DDL no transaccional).
        if (!Schema::hasColumn('alumno_grupo', 'curso_academico_id')) {
            Schema::table('alumno_grupo', function (Blueprint $table) {
                $table->foreignId('curso_academico_id')
                    ->nullable()
                    ->after('grupo_id')
                    ->constrained('cursos_academicos')
                    ->nullOnDelete();
            });
        }

        // Backfill: asigna a cada pertenencia el curso académico de su grupo (si lo tiene)
        foreach (DB::table('alumno_grupo')->whereNull('curso_academico_id')->get() as $fila) {
            $cursoId = DB::table('grupos')->where('id', $fila->grupo_id)->value('curso_academico_id');
            if ($cursoId) {
                DB::table('alumno_grupo')
                    ->where('id', $fila->id)
                    ->update(['curso_academico_id' => $cursoId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('alumno_grupo', 'curso_academico_id')) {
            Schema::table('alumno_grupo', function (Blueprint $table) {
                $table->dropForeign(['curso_academico_id']);
                $table->dropColumn('curso_academico_id');
            });
        }
    }
};
