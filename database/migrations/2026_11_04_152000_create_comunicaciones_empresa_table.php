<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comunicaciones_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('profesor_id')->nullable()->constrained('profesores')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('curso_academico_id')->nullable()->constrained('cursos_academicos')->nullOnDelete();
            $table->string('tipo', 20); // 'telefono', 'email', 'presencial'
            $table->text('motivo');
            $table->text('respuesta')->nullable();
            $table->dateTime('fecha');
            $table->timestamps();

            $table->index(['empresa_id', 'curso_academico_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicaciones_empresa');
    }
};
