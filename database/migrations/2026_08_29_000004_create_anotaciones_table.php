<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('anotaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('profesor_id')->nullable()->constrained('profesores')->onDelete('cascade');
            $table->string('titulo');
            $table->text('contenido');
            $table->boolean('es_publica')->default(false);
            $table->timestamps();

            $table->index(['alumno_id', 'profesor_id']);
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('anotaciones');
    }
};