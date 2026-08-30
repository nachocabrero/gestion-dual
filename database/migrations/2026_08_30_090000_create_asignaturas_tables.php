<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asignaturas')) return;
        if (Schema::hasTable('profesor_asignatura')) return;

        Schema::create('asignaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained()->onDelete('cascade');
            $table->string('codigo')->unique(); // Ej: "DAWES", "DAMC"
            $table->string('nombre');
            $table->unsignedTinyInteger('horas_semanales')->default(4);
            $table->boolean('es_practicas')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('profesor_asignatura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained()->onDelete('cascade');
            $table->foreignId('asignatura_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['profesor_id', 'asignatura_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesor_asignatura');
        Schema::dropIfExists('asignaturas');
    }
};