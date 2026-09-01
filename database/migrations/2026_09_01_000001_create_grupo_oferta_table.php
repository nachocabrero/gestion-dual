<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot: una oferta de prácticas puede dirigirse a varios grupos (grupo clase).
     */
    public function up(): void
    {
        Schema::create('grupo_oferta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('oferta_practica_id')->constrained('ofertas_practicas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['grupo_id', 'oferta_practica_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_oferta');
    }
};
