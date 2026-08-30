<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renombrar ASIR a SMR
        DB::table('ciclos')
            ->where('codigo', 'ASIR')
            ->update([
                'codigo' => 'SMR',
                'nombre' => 'Sistemas Microinformáticos y Redes',
                'grado' => 'media',
            ]);

        // Insertar GBI si no existe
        $familia = DB::table('familias')->where('codigo', 'INFORMATICA')->first();
        if ($familia) {
            DB::table('ciclos')->insertOrIgnore([
                'familia_id' => $familia->id,
                'codigo' => 'GBI',
                'nombre' => 'Informática de Oficina',
                'grado' => 'basica',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ciclos')
            ->where('codigo', 'SMR')
            ->update([
                'codigo' => 'ASIR',
                'nombre' => 'Administración de Sistemas Informáticos en Red',
                'grado' => 'superior',
            ]);

        DB::table('ciclos')->where('codigo', 'GBI')->delete();
    }
};
