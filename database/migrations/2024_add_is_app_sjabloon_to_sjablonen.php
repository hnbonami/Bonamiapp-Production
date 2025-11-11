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
        Schema::table('sjablonen', function (Blueprint $table) {
            // Voeg is_app_sjabloon kolom toe na is_actief
            $table->boolean('is_app_sjabloon')->default(0)->after('is_actief')
                ->comment('1 = App Sjabloon (zichtbaar voor alle organisaties), 0 = Privé sjabloon (alleen eigen organisatie)');
        });
        
        // Update bestaande sjablonen: alle actieve sjablonen van org 1 worden app sjablonen
        DB::table('sjablonen')
            ->where('organisatie_id', 1)
            ->where('is_actief', 1)
            ->update(['is_app_sjabloon' => 1]);
            
        \Log::info('✅ Migration: is_app_sjabloon kolom toegevoegd en bestaande app sjablonen gemarkeerd');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sjablonen', function (Blueprint $table) {
            $table->dropColumn('is_app_sjabloon');
        });
    }
};
