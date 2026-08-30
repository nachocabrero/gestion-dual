<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciclos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('familia_id')->constrained()->onDelete('cascade');
            $table->string('codigo')->unique(); // Ej: "DAW", "DAM", "ASIR"
            $table->string('nombre'); // Ej: "Desarrollo de Aplicaciones Web"
            $table->text('descripcion')->nullable();
            $table->enum('grado', ['basica', 'media', 'superior', 'especializacion', 'acreditacion'])->default('superior');
            $table->unsignedTinyInteger('duracion_anos')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciclos');
    }
};