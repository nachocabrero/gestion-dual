<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un profesor imparte una asignatura concreta en cada grupo.
     * Se añade asignatura_id al pivot grupo_profesor.
     */
    public function up(): void
    {
        Schema::table('grupo_profesor', function (Blueprint $table) {
            if (!Schema::hasColumn('grupo_profesor', 'asignatura_id')) {
                $table->foreignId('asignatura_id')->nullable()->after('profesor_id')->constrained('asignaturas')->nullOnDelete();
            } else {
                $table->foreignId('asignatura_id')->nullable()->change();
            }
        });

        Schema::table('grupo_profesor', function (Blueprint $table) {
            $table->unique(['grupo_id', 'profesor_id', 'asignatura_id']);
            $table->dropUnique('grupo_profesor_grupo_id_profesor_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('grupo_profesor', function (Blueprint $table) {
            $table->dropUnique('grupo_profesor_grupo_id_profesor_id_asignatura_id_unique');
            $table->dropConstrainedForeignId('asignatura_id');
            $table->unique(['grupo_id', 'profesor_id']);
        });
    }
};
