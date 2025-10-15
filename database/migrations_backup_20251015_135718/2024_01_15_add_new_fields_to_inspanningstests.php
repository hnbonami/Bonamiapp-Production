<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - VEILIG: Check eerst of kolom al bestaat
     */
    public function up(): void
    {
        Schema::table('inspanningstests', function (Blueprint $table) {
            // Vetpercentage veld
            if (!Schema::hasColumn('inspanningstests', 'vetpercentage')) {
                $table->decimal('vetpercentage', 5, 2)->nullable()->after('bmi');
            }
            
            // Complete AI analyse veld (long text voor uitgebreide analyse)
            if (!Schema::hasColumn('inspanningstests', 'complete_ai_analyse')) {
                $table->longText('complete_ai_analyse')->nullable()->after('anaerobe_drempel_hartslag');
            }
            
            // Trainingszones data (JSON voor flexibele opslag)
            if (!Schema::hasColumn('inspanningstests', 'trainingszones_data')) {
                $table->json('trainingszones_data')->nullable()->after('complete_ai_analyse');
            }
            
            // Trainingszones configuratie velden
            if (!Schema::hasColumn('inspanningstests', 'zones_methode')) {
                $table->string('zones_methode', 50)->nullable()->after('trainingszones_data');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'zones_aantal')) {
                $table->tinyInteger('zones_aantal')->nullable()->after('zones_methode');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'zones_eenheid')) {
                $table->string('zones_eenheid', 50)->nullable()->after('zones_aantal');
            }
        });
        
        \Log::info('✅ Migration add_new_fields_to_inspanningstests voltooid');
    }

    /**
     * Reverse the migrations - VEILIG: Check eerst of kolom bestaat voordat je verwijdert
     */
    public function down(): void
    {
        Schema::table('inspanningstests', function (Blueprint $table) {
            if (Schema::hasColumn('inspanningstests', 'vetpercentage')) {
                $table->dropColumn('vetpercentage');
            }
            
            if (Schema::hasColumn('inspanningstests', 'complete_ai_analyse')) {
                $table->dropColumn('complete_ai_analyse');
            }
            
            if (Schema::hasColumn('inspanningstests', 'trainingszones_data')) {
                $table->dropColumn('trainingszones_data');
            }
            
            if (Schema::hasColumn('inspanningstests', 'zones_methode')) {
                $table->dropColumn('zones_methode');
            }
            
            if (Schema::hasColumn('inspanningstests', 'zones_aantal')) {
                $table->dropColumn('zones_aantal');
            }
            
            if (Schema::hasColumn('inspanningstests', 'zones_eenheid')) {
                $table->dropColumn('zones_eenheid');
            }
        });
    }
};
