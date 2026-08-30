<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sustituciones')) return;

        Schema::create('sustituciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_titular_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('profesor_sustituto_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('asignatura_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('grupo_id')->nullable()->constrained()->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('motivo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sustituciones');
    }
};