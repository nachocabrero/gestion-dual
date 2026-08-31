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
        // Eliminar el índice antiguo unique_empresa_ciclo_curso (si existe)
        try {
            Schema::table('convenios', function (Blueprint $table) {
                $table->dropUnique('unique_empresa_ciclo_curso');
            });
        } catch (\Throwable $e) {
            // El índice puede no existir si la tabla se crea desde cero
        }

        // Crear nuevo índice único empresa-alumno
        try {
            Schema::table('convenios', function (Blueprint $table) {
                $table->unique(['empresa_id', 'alumno_id'], 'unique_empresa_alumno');
            });
        } catch (\Throwable $e) {
            // El índice puede ya existir
        }

        if (DB::getDriverName() === 'sqlite') {
            // SQLite no soporta DROP COLUMN con FK — recrear tabla
            $originalData = DB::table('convenios')->get()->toArray();

            Schema::dropIfExists('convenios');
            Schema::create('convenios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
                $table->foreignId('alumno_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tutor_laboral_id')->nullable()->constrained('tutores_laborales')->nullOnDelete();
                $table->foreignId('tutor_docente_id')->nullable()->constrained('profesores')->nullOnDelete();
                $table->foreignId('grupo_id')->constrained()->cascadeOnDelete();
                $table->integer('numero_horas')->default(0);
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->string('estado')->default('no_firmado');
                $table->date('fecha_firma')->nullable();
                $table->timestamps();
            });

            // Restaurar datos (sin ciclo_id ni curso_academico)
            foreach ($originalData as $row) {
                DB::table('convenios')->insert([
                    'id' => $row->id,
                    'empresa_id' => $row->empresa_id,
                    'alumno_id' => $row->alumno_id ?? null,
                    'tutor_laboral_id' => $row->tutor_laboral_id,
                    'tutor_docente_id' => $row->tutor_docente_id,
                    'grupo_id' => $row->grupo_id ?? null,
                    'numero_horas' => $row->numero_horas ?? 0,
                    'fecha_inicio' => $row->fecha_inicio,
                    'fecha_fin' => $row->fecha_fin,
                    'estado' => $row->estado,
                    'fecha_firma' => $row->fecha_firma,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        } else {
            Schema::table('convenios', function (Blueprint $table) {
                $table->dropForeign(['ciclo_id']);
                $table->dropColumn(['ciclo_id', 'curso_academico']);

                $table->foreignId('alumno_id')->after('empresa_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tutor_laboral_id')->after('alumno_id')->nullable()->constrained('tutores_laborales')->nullOnDelete();
                $table->foreignId('tutor_docente_id')->after('tutor_laboral_id')->nullable()->constrained('profesores')->nullOnDelete();
                $table->foreignId('grupo_id')->after('tutor_docente_id')->constrained()->cascadeOnDelete();
                $table->integer('numero_horas')->after('grupo_id')->default(0);
                $table->date('fecha_inicio')->after('numero_horas')->nullable();
                $table->date('fecha_fin')->after('fecha_inicio')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->foreignId('ciclo_id')->constrained('ciclos')->cascadeOnDelete();
            $table->string('curso_academico')->nullable();

            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['alumno_id']);
                $table->dropForeign(['tutor_laboral_id']);
                $table->dropForeign(['tutor_docente_id']);
                $table->dropForeign(['grupo_id']);
            }
            
            $table->dropColumn(['alumno_id', 'tutor_laboral_id', 'tutor_docente_id', 'grupo_id', 'numero_horas', 'fecha_inicio', 'fecha_fin']);
        });
    }
};
