<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('ofertas_practicas', 'token')) {
            Schema::table('ofertas_practicas', function (Blueprint $table) {
                $table->string('token', 64)->nullable()->unique()->after('estado');
            });
        }

        // Backfill: asignar un token aleatorio y seguro a las ofertas que no dispongan de él
        $ofertasSinToken = DB::table('ofertas_practicas')->whereNull('token')->get();
        foreach ($ofertasSinToken as $oferta) {
            DB::table('ofertas_practicas')->where('id', $oferta->id)->update([
                'token' => Str::random(40),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ofertas_practicas', 'token')) {
            Schema::table('ofertas_practicas', function (Blueprint $table) {
                $table->dropColumn('token');
            });
        }
    }
};
