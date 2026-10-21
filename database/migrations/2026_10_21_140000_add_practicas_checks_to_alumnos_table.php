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
                if (!Schema::hasColumn('alumnos', 'sin_practicas_primero')) {
                    $table->boolean('sin_practicas_primero')->default(false)->after('horas_practicas_primero');
                }
                if (!Schema::hasColumn('alumnos', 'repitiendo_modulos_primero')) {
                    $table->boolean('repitiendo_modulos_primero')->default(false)->after('sin_practicas_primero');
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
                if (Schema::hasColumn('alumnos', 'repitiendo_modulos_primero')) {
                    $table->dropColumn('repitiendo_modulos_primero');
                }
                if (Schema::hasColumn('alumnos', 'sin_practicas_primero')) {
                    $table->dropColumn('sin_practicas_primero');
                }
            });
        }
    }
};
