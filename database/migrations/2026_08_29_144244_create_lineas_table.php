<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained()->onDelete('cascade');
            $table->string('nombre'); // Ej: "Línea A", "Línea B"
            $table->enum('turno', ['manana', 'tarde'])->default('manana');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lineas');
    }
};