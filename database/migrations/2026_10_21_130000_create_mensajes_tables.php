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
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remitente_id')->constrained('users')->onDelete('cascade');
            $table->string('asunto');
            $table->text('cuerpo');
            $table->string('tipo_destinatario'); // 'grupos', 'alumnos_grupo', 'profesores_ciclos'
            $table->text('resumen_destinatarios')->nullable();
            $table->unsignedInteger('total_destinatarios')->default(0);
            $table->timestamps();

            $table->index(['remitente_id', 'created_at']);
        });

        Schema::create('mensaje_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensaje_id')->constrained('mensajes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('leido_at')->nullable();
            $table->timestamps();

            $table->unique(['mensaje_id', 'user_id']);
            $table->index(['user_id', 'leido_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensaje_destinatarios');
        Schema::dropIfExists('mensajes');
    }
};
