<?php

use App\Models\CursoAcademico;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Garantiza que exista un curso académico activo (por defecto 2026-2027)
     * y vincula los grupos, matrículas y ofertas existentes que tengan
     * curso_academico_id nulo para que estén correctamente asignados al curso actual.
     */
    public function up(): void
    {
        // 1. Obtener o crear el curso académico activo actual
        $cursoActivo = CursoAcademico::where('is_active', true)
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        if (!$cursoActivo) {
            $cursoMasReciente = CursoAcademico::orderBy('fecha_inicio', 'desc')->first();
            if ($cursoMasReciente) {
                $cursoMasReciente->update(['is_active' => true]);
                $cursoActivo = $cursoMasReciente;
            } else {
                $cursoActivo = CursoAcademico::create([
                    'nombre' => '2026-2027',
                    'fecha_inicio' => '2026-09-01',
                    'fecha_fin' => '2027-06-30',
                    'is_active' => true,
                ]);
            }
        }

        $cursoId = $cursoActivo->id;

        // 2. Vincular grupos existentes sin curso al curso actual
        DB::table('grupos')
            ->whereNull('curso_academico_id')
            ->update([
                'curso_academico_id' => $cursoId,
                'updated_at' => now(),
            ]);

        // 3. Vincular matrículas de alumnos sin curso al curso actual
        if (DB::getSchemaBuilder()->hasColumn('alumno_grupo', 'curso_academico_id')) {
            DB::table('alumno_grupo')
                ->whereNull('curso_academico_id')
                ->update([
                    'curso_academico_id' => $cursoId,
                    'updated_at' => now(),
                ]);
        }

        // 4. Vincular ofertas de prácticas sin curso al curso actual
        if (DB::getSchemaBuilder()->hasColumn('ofertas_practicas', 'curso_academico_id')) {
            DB::table('ofertas_practicas')
                ->whereNull('curso_academico_id')
                ->update([
                    'curso_academico_id' => $cursoId,
                    'updated_at' => now(),
                ]);
        }

        // 5. Vincular prácticas sin curso al curso actual
        if (DB::getSchemaBuilder()->hasColumn('practicas', 'curso_academico_id')) {
            DB::table('practicas')
                ->whereNull('curso_academico_id')
                ->update([
                    'curso_academico_id' => $cursoId,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir para no desasociar datos válidos
    }
};
