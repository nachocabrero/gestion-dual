<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('alumno_ciclo_matricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained()->onDelete('cascade');
            $table->foreignId('ciclo_id')->constrained()->onDelete('cascade');
            $table->string('curso_academico'); // Ej: "2026-2027"
            $table->timestamp('matriculado_at')->nullable();
            $table->timestamp('graduado_at')->nullable();
            $table->timestamps();

            $table->unique(['alumno_id', 'ciclo_id', 'curso_academico']);
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_ciclo_matricula');
    }
};