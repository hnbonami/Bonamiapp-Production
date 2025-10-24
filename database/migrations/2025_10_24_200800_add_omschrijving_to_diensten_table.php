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
        Schema::table('diensten', function (Blueprint $table) {
            // Controleer eerst of de kolom al bestaat
            if (!Schema::hasColumn('diensten', 'omschrijving')) {
                $table->text('omschrijving')->nullable()->after('naam');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diensten', function (Blueprint $table) {
            if (Schema::hasColumn('diensten', 'omschrijving')) {
                $table->dropColumn('omschrijving');
            }
        });
    }
};
