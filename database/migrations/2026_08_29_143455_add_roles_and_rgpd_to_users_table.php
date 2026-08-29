<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * RGPD Compliance:
     * - roles: array JSON para roles múltiples
     * - is_active: boolean para desactivar usuarios (derecho de suspensión)
     * - consent_rgpd: boolean para consentimiento explícito (Art. 6.1.a RGPD)
     * - consent_rgpd_at: timestamp para registrar cuándo se dio el consentimiento
     * - data_deletion_requested_at: timestamp para derecho de supresión (Art. 17 RGPD)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('roles');
            $table->boolean('consent_rgpd')->default(false)->after('is_active');
            $table->timestamp('consent_rgpd_at')->nullable()->after('consent_rgpd');
            $table->timestamp('data_deletion_requested_at')->nullable()->after('consent_rgpd_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['roles', 'is_active', 'consent_rgpd', 'consent_rgpd_at', 'data_deletion_requested_at']);
        });
    }
};