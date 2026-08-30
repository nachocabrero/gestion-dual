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
        Schema::table('convenios', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['ciclo_id']);
                $table->dropColumn(['ciclo_id', 'curso_academico']);
            }

            $table->foreignId('alumno_id')->after('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_laboral_id')->after('alumno_id')->nullable()->constrained('tutores_laborales')->nullOnDelete();
            $table->foreignId('tutor_docente_id')->after('tutor_laboral_id')->nullable()->constrained('profesores')->nullOnDelete();
            $table->foreignId('grupo_id')->after('tutor_docente_id')->constrained()->cascadeOnDelete();
            $table->integer('numero_horas')->after('grupo_id')->default(0);
            $table->date('fecha_inicio')->after('numero_horas')->nullable();
            $table->date('fecha_fin')->after('fecha_inicio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->foreignId('ciclo_id')->constrained('ciclos')->cascadeOnDelete();
            $table->string('curso_academico')->nullable();

            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['alumno_id']);
                $table->dropForeign(['tutor_laboral_id']);
                $table->dropForeign(['tutor_docente_id']);
                $table->dropForeign(['grupo_id']);
            }
            
            $table->dropColumn(['alumno_id', 'tutor_laboral_id', 'tutor_docente_id', 'grupo_id', 'numero_horas', 'fecha_inicio', 'fecha_fin']);
        });
    }
};
