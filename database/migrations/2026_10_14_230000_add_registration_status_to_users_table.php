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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'registration_status')) {
                $table->string('registration_status', 20)->default('approved')->after('is_active');
            }
            if (!Schema::hasColumn('users', 'registration_approved_at')) {
                $table->timestamp('registration_approved_at')->nullable()->after('registration_status');
            }
            if (!Schema::hasColumn('users', 'registration_rejected_at')) {
                $table->timestamp('registration_rejected_at')->nullable()->after('registration_approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'registration_rejected_at')) {
                $table->dropColumn('registration_rejected_at');
            }
            if (Schema::hasColumn('users', 'registration_approved_at')) {
                $table->dropColumn('registration_approved_at');
            }
            if (Schema::hasColumn('users', 'registration_status')) {
                $table->dropColumn('registration_status');
            }
        });
    }
};
