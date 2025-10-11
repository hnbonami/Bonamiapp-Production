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
            // Add klant_id column if it doesn't exist
            if (!Schema::hasColumn('users', 'klant_id')) {
                $table->unsignedBigInteger('klant_id')->nullable()->after('role');
                $table->foreign('klant_id')->references('id')->on('klanten')->onDelete('set null');
            }
            
            // Add medewerker_id column if it doesn't exist  
            if (!Schema::hasColumn('users', 'medewerker_id')) {
                $table->unsignedBigInteger('medewerker_id')->nullable()->after('klant_id');
                $table->foreign('medewerker_id')->references('id')->on('medewerkers')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'klant_id')) {
                $table->dropForeign(['klant_id']);
                $table->dropColumn('klant_id');
            }
            
            if (Schema::hasColumn('users', 'medewerker_id')) {
                $table->dropForeign(['medewerker_id']);
                $table->dropColumn('medewerker_id');
            }
        });
    }
};