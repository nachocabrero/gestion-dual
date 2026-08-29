<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('especialidad')->nullable();
            $table->boolean('es_tutor')->default(false);
            $table->boolean('es_coordinador_dual')->default(false);
            $table->timestamps();
        });

        Schema::create('asignaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained('ciclos')->onDelete('cascade');
            $table->string('codigo');
            $table->string('nombre');
            $table->unsignedInteger('horas_semanales')->default(4);
            $table->boolean('es_practicas')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('profesor_asignatura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('profesores')->onDelete('cascade');
            $table->foreignId('asignatura_id')->constrained('asignaturas')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('profesor_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('profesores')->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('sustituciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_titular_id')->constrained('profesores')->onDelete('cascade');
            $table->foreignId('profesor_sustituto_id')->constrained('profesores')->onDelete('cascade');
            $table->foreignId('asignatura_id')->nullable()->constrained('asignaturas')->onDelete('set null');
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sustituciones');
        Schema::dropIfExists('profesor_grupo');
        Schema::dropIfExists('profesor_asignatura');
        Schema::dropIfExists('asignaturas');
        Schema::dropIfExists('profesores');
    }
};