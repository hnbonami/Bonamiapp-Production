<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg organisatie_id toe aan klanten tabel
     */
    public function up(): void
    {
        Schema::table('klanten', function (Blueprint $table) {
            $table->foreignId('organisatie_id')->nullable()->after('id')->constrained('organisaties')->cascadeOnDelete();
        });
    }

    /**
     * Draai de migration terug
     */
    public function down(): void
    {
        Schema::table('klanten', function (Blueprint $table) {
            $table->dropForeign(['organisatie_id']);
            $table->dropColumn('organisatie_id');
        });
    }
};
