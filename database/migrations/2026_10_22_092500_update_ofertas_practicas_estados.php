<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ofertas_practicas MODIFY COLUMN estado VARCHAR(30) NOT NULL DEFAULT 'pendiente'");
            DB::table('ofertas_practicas')->where('estado', 'activa')->update(['estado' => 'abierta']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('ofertas_practicas')->where('estado', 'abierta')->update(['estado' => 'activa']);
            DB::statement("ALTER TABLE ofertas_practicas MODIFY COLUMN estado ENUM('pendiente', 'activa', 'cerrada') NOT NULL DEFAULT 'pendiente'");
        }
    }
};
