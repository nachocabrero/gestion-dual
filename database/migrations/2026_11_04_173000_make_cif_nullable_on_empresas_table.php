<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropUnique('empresas_cif_unique');
                $table->string('cif')->nullable()->change();
                $table->index('cif', 'empresas_cif_index');
            } else {
                $table->string('cif')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropIndex('empresas_cif_index');
                $table->string('cif')->nullable(false)->change();
                $table->unique('cif', 'empresas_cif_unique');
            } else {
                $table->string('cif')->nullable(false)->change();
            }
        });
    }
};
