<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anotaciones', function (Blueprint $table) {
            $table->unsignedTinyInteger('puesto')->nullable()->after('contenido');
        });
    }

    public function down(): void
    {
        Schema::table('anotaciones', function (Blueprint $table) {
            $table->dropColumn('puesto');
        });
    }
};
