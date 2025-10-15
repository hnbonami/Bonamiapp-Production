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
        Schema::table('login_activities', function (Blueprint $table) {
            // Voeg alleen toe als de kolom nog niet bestaat
            if (!Schema::hasColumn('login_activities', 'logged_out_at')) {
                $table->timestamp('logged_out_at')->nullable()->after('logged_in_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_activities', function (Blueprint $table) {
            $table->dropColumn('logged_out_at');
        });
    }
};