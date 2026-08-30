<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrar datos existentes
        $alumnos = DB::table('alumnos')->whereNotNull('grupo_id')->get();
        foreach ($alumnos as $alumno) {
            DB::table('alumno_grupo')->insertOrIgnore([
                'alumno_id' => $alumno->id,
                'grupo_id' => $alumno->grupo_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('alumnos', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['grupo_id']);
                $table->dropColumn('grupo_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
        });
    }
};
