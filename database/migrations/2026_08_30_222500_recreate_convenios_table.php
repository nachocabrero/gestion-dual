<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite no soporta DROP TABLE con FK — solo asegurar que la tabla existe con la estructura correcta
            if (!Schema::hasTable('convenios')) {
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
                    $table->unique(['empresa_id', 'alumno_id'], 'unique_empresa_alumno');
                });
            }
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Guardar datos actuales
        $data = DB::table('convenios')->get()->toArray();

        // Eliminar tabla
        Schema::dropIfExists('convenios');

        // Recrear con índice correcto
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
            $table->unique(['empresa_id', 'alumno_id'], 'unique_empresa_alumno');
        });

        // Restaurar datos
        foreach ($data as $row) {
            DB::table('convenios')->insert([
                'id' => $row->id,
                'empresa_id' => $row->empresa_id,
                'alumno_id' => $row->alumno_id,
                'tutor_laboral_id' => $row->tutor_laboral_id,
                'tutor_docente_id' => $row->tutor_docente_id,
                'grupo_id' => $row->grupo_id,
                'numero_horas' => $row->numero_horas,
                'fecha_inicio' => $row->fecha_inicio,
                'fecha_fin' => $row->fecha_fin,
                'estado' => $row->estado,
                'fecha_firma' => $row->fecha_firma,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $data = DB::table('convenios')->get()->toArray();
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

        foreach ($data as $row) {
            DB::table('convenios')->insert([
                'id' => $row->id,
                'empresa_id' => $row->empresa_id,
                'alumno_id' => $row->alumno_id,
                'tutor_laboral_id' => $row->tutor_laboral_id,
                'tutor_docente_id' => $row->tutor_docente_id,
                'grupo_id' => $row->grupo_id,
                'numero_horas' => $row->numero_horas,
                'fecha_inicio' => $row->fecha_inicio,
                'fecha_fin' => $row->fecha_fin,
                'estado' => $row->estado,
                'fecha_firma' => $row->fecha_firma,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};