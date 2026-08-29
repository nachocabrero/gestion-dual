<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añadir curso_academico_id a la tabla practicas.
     * Permite agrupar horas por curso académico.
     */
    public function up(): void
    {
        Schema::table('practicas', function (Blueprint $table) {
            $table->foreignId('curso_academico_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('cursos_academicos')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practicas', function (Blueprint $table) {
            $table->dropColumn('curso_academico_id');
        });
    }
};