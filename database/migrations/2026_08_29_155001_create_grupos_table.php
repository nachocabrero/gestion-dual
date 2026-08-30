<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linea_id')->constrained()->onDelete('cascade');
            $table->foreignId('curso_academico_id')->nullable()->constrained('cursos_academicos')->onDelete('set null');
            $table->unsignedTinyInteger('numero'); // Ej: 1, 2, 3
            $table->string('nombre')->nullable(); // Ej: "1º DAW-Manana"
            $table->foreignId('tutor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};