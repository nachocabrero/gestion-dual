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
        if (Schema::hasTable('alumnos')) {
            Schema::table('alumnos', function (Blueprint $table) {
                if (!Schema::hasColumn('alumnos', 'empresa_practicas_primero')) {
                    $table->string('empresa_practicas_primero', 255)->nullable()->after('tutor_practicas_id');
                }
                if (!Schema::hasColumn('alumnos', 'horas_practicas_primero')) {
                    $table->unsignedInteger('horas_practicas_primero')->nullable()->after('empresa_practicas_primero');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('alumnos')) {
            Schema::table('alumnos', function (Blueprint $table) {
                if (Schema::hasColumn('alumnos', 'horas_practicas_primero')) {
                    $table->dropColumn('horas_practicas_primero');
                }
                if (Schema::hasColumn('alumnos', 'empresa_practicas_primero')) {
                    $table->dropColumn('empresa_practicas_primero');
                }
            });
        }
    }
};
