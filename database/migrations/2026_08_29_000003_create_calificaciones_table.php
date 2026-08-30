<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('asignatura_id')->constrained('asignaturas')->onDelete('cascade');
            $table->string('evaluacion')->default('primera'); // primera, segunda, tercera
            $table->decimal('nota', 4, 2)->nullable(); // 0-10
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Un alumno no puede tener dos notas en la misma evaluación para la misma asignatura
            $table->unique(['alumno_id', 'asignatura_id', 'evaluacion'], 'calificaciones_unique');
            $table->index(['alumno_id', 'evaluacion']);
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};