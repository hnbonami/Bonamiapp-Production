<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg medewerker_id kolom toe aan prestaties tabel
     */
    public function up(): void
    {
        Schema::table('prestaties', function (Blueprint $table) {
            $table->unsignedBigInteger('medewerker_id')->nullable()->after('user_id');
            
            // Foreign key constraint (optioneel, afhankelijk van je setup)
            $table->foreign('medewerker_id')
                  ->references('id')
                  ->on('medewerkers')
                  ->onDelete('set null');
            
            // Index voor snellere queries
            $table->index('medewerker_id');
        });
        
        // Update bestaande prestaties: koppel user_id aan medewerker_id
        \DB::statement('
            UPDATE prestaties p
            INNER JOIN users u ON p.user_id = u.id
            SET p.medewerker_id = u.medewerker_id
            WHERE u.medewerker_id IS NOT NULL
        ');
        
        \Log::info('Medewerker_id kolom toegevoegd aan prestaties tabel');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestaties', function (Blueprint $table) {
            $table->dropForeign(['medewerker_id']);
            $table->dropIndex(['medewerker_id']);
            $table->dropColumn('medewerker_id');
        });
    }
};