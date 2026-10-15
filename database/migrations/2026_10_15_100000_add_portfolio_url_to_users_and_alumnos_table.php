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
        if (!Schema::hasColumn('users', 'portfolio_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('portfolio_url')->nullable()->after('github_url');
            });
        }

        if (!Schema::hasColumn('alumnos', 'portfolio_url')) {
            Schema::table('alumnos', function (Blueprint $table) {
                $table->string('portfolio_url')->nullable()->after('linkedin_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'portfolio_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('portfolio_url');
            });
        }

        if (!Schema::hasColumn('alumnos', 'portfolio_url')) {
            Schema::table('alumnos', function (Blueprint $table) {
                $table->dropColumn('portfolio_url');
            });
        }
    }
};
