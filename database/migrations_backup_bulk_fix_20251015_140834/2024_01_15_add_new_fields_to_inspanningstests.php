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
            // Lichaamssamenstelling velden - volgorde speelt nu geen rol
            $table->decimal('vetpercentage', 5, 2)->nullable();
            $table->integer('buikomtrek_cm')->nullable();
            
            // Drempelwaarden velden
            $table->decimal('aerobe_drempel_vermogen', 8, 2)->nullable();
            $table->decimal('aerobe_drempel_hartslag', 8, 2)->nullable();
            
            // AI analyses
            $table->text('complete_ai_analyse')->nullable();
            
            // Trainingszones data (JSON)
            $table->json('trainingszones_data')->nullable();
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
