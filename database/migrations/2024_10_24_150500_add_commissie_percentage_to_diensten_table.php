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
            // Voeg commissie_percentage kolom toe na standaard_prijs
            $table->decimal('commissie_percentage', 5, 2)->default(0)->after('standaard_prijs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diensten', function (Blueprint $table) {
            $table->dropColumn('commissie_percentage');
        });
    }
};
