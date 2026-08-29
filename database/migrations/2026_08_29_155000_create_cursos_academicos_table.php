<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // ej: "26/27", "27/28"
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos_academicos');
    }
};