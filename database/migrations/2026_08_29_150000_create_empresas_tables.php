<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cif')->unique();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('responsable_nombre')->nullable();
            $table->string('responsable_dni')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tutores_laborales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->timestamps();

            $table->index(['empresa_id']);
        });

        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('ciclo_id')->constrained('ciclos')->onDelete('cascade');
            $table->string('curso_academico');
            $table->enum('estado', ['no_firmado', 'firmado'])->default('no_firmado');
            $table->date('fecha_firma')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'ciclo_id', 'curso_academico'], 'unique_empresa_ciclo_curso');
            $table->index(['estado', 'curso_academico']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios');
        Schema::dropIfExists('tutores_laborales');
        Schema::dropIfExists('empresas');
    }
};