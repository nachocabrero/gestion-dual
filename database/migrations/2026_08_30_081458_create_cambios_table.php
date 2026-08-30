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
        Schema::create('cambios', function (Blueprint $table) {
            $table->id();
            $table->string('registrable_type');  // Modelo completo: App\Models\User
            $table->unsignedBigInteger('registrable_id');  // ID del registro
            $table->string('accion');  // created, updated, deleted, estado_cambiado, asignado, anotado
            $table->text('campo')->nullable();  // Nombre del campo modificado
            $table->text('antes')->nullable();  // Valor anterior (JSON)
            $table->text('despues')->nullable();  // Valor nuevo (JSON)
            $table->text('descripcion')->nullable();  // Descripción legible
            $table->unsignedBigInteger('usuario_id')->nullable();  // Quién hizo el cambio
            $table->timestamps();

            $table->index(['registrable_type', 'registrable_id']);
            $table->index('usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cambios');
    }
};
