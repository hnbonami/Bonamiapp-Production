<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        // Update alle prestaties direct met SQL voor snelheid
        DB::statement('
            UPDATE prestaties 
            SET commissie_percentage = (100 - commissie_percentage)
            WHERE commissie_percentage > 50
        ');
        
        \Log::info('Commissie percentages succesvol gecorrigeerd voor alle prestaties');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Draai de correctie terug als nodig
        DB::statement('
            UPDATE prestaties 
            SET commissie_percentage = (100 - commissie_percentage)
            WHERE commissie_percentage < 50
        ');
    }
};
