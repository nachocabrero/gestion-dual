<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('ofertas_practicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
            $table->foreignId('creador_id')->constrained('users')->onDelete('cascade');
            $table->string('creador_type'); // 'profesor' or 'empresa'
            $table->string('especialidad_requerida');
            $table->integer('num_alumnos')->default(1);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['pendiente', 'activa', 'cerrada'])->default('pendiente');
            $table->timestamps();

            $table->index(['estado', 'especialidad_requerida']);
        });

        Schema::create('solicitudes_practicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_id')->constrained('ofertas_practicas')->onDelete('cascade');
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado', 'retirado'])->default('pendiente');
            $table->text('motivo_rechazo')->nullable();
            $table->timestamps();

            $table->unique(['oferta_id', 'alumno_id'], 'unique_oferta_alumno');
            $table->index(['alumno_id', 'estado']);
            $table->index(['oferta_id', 'estado']);
        });

        Schema::create('practicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('oferta_id')->nullable()->constrained('ofertas_practicas')->onDelete('set null');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('tutor_laboral_id')->nullable()->constrained('tutores_laborales')->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->integer('horas_acumuladas')->default(0);
            $table->boolean('convenio_firmado')->default(false);
            $table->timestamps();

            $table->index(['alumno_id', 'convenio_firmado']);
        });

        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('ciclo_id')->constrained('ciclos')->onDelete('cascade');
            $table->foreignId('curso_academico_id')->constrained('cursos_academicos')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('enlace_repositorio')->nullable();
            $table->string('enlace_despliegue')->nullable();
            $table->decimal('calificacion', 4, 2)->nullable();
            $table->boolean('es_destacado')->default(false);
            $table->foreignId('destacado_por_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['ciclo_id', 'curso_academico_id', 'es_destacado']);
        });

        Schema::create('proyecto_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->onDelete('cascade');
            $table->string('url');
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_imagenes');
        Schema::dropIfExists('proyectos');
        Schema::dropIfExists('practicas');
        Schema::dropIfExists('solicitudes_practicas');
        Schema::dropIfExists('ofertas_practicas');
    }
};