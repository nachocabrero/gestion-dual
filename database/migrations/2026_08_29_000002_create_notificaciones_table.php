<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo'); // empresa_asignada, estado_acuerdo, proyecto_calificado, etc.
            $table->string('titulo');
            $table->text('mensaje');
            $table->json('datos')->nullable(); // datos adicionales del suceso
            $table->string('enlace')->nullable(); // URL al suceso
            $table->boolean('es_leida')->default(false);
            $table->timestamp('expira_en')->nullable(); // null = permanente
            $table->timestamps();

            $table->index(['usuario_id', 'es_leida']);
            $table->index(['usuario_id', 'expira_en']);
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};