<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Prestatie;

return new class extends Migration
{
    /**
     * Run de migrations - Fix commissie percentages
     * 
     * Het commissie_percentage veld bevat nu verkeerde waarden (83% ipv 17%)
     * Dit moet worden omgedraaid naar de correcte Bonami commissie percentages
     */
    public function up(): void
    {
        // Haal alle prestaties op en corrigeer de percentages
        Prestatie::chunk(100, function ($prestaties) {
            foreach ($prestaties as $prestatie) {
                // Het huidige percentage is het medewerker percentage (83%)
                // We moeten dit omdraaien naar het organisatie percentage (17%)
                $huidigPercentage = $prestatie->commissie_percentage;
                $correctPercentage = 100 - $huidigPercentage;
                
                $prestatie->update([
                    'commissie_percentage' => $correctPercentage
                ]);
            }
        });
        
        \Log::info('Commissie percentages succesvol gecorrigeerd voor alle prestaties');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Draai de correctie terug als nodig
        Prestatie::chunk(100, function ($prestaties) {
            foreach ($prestaties as $prestatie) {
                $huidigPercentage = $prestatie->commissie_percentage;
                $oudPercentage = 100 - $huidigPercentage;
                
                $prestatie->update([
                    'commissie_percentage' => $oudPercentage
                ]);
            }
        });
    }
};
