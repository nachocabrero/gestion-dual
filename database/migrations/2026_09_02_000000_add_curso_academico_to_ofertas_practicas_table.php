<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ofertas_practicas', 'curso_academico_id')) {
            Schema::table('ofertas_practicas', function (Blueprint $table) {
                $table->foreignId('curso_academico_id')
                    ->nullable()
                    ->after('empresa_id')
                    ->constrained('cursos_academicos')
                    ->onDelete('set null');
            });
        }

        // Backfill: asignar a las ofertas existentes el curso académico según su created_at.
        $cursoActual = DB::table('cursos_academicos')
            ->where('is_active', true)
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        foreach (DB::table('ofertas_practicas')->whereNull('curso_academico_id')->get() as $oferta) {
            $curso = DB::table('cursos_academicos')
                ->where('fecha_inicio', '<=', $oferta->created_at)
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            $cursoId = $curso->id ?? $cursoActual->id ?? null;
            if ($cursoId) {
                DB::table('ofertas_practicas')->where('id', $oferta->id)->update(['curso_academico_id' => $cursoId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ofertas_practicas', 'curso_academico_id')) {
            Schema::table('ofertas_practicas', function (Blueprint $table) {
                $table->dropForeign(['curso_academico_id']);
                $table->dropColumn('curso_academico_id');
            });
        }
    }
};