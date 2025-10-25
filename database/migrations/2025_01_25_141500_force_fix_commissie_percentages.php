<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * FORCEER correctie van ALLE commissie percentages
     * 
     * Als commissie_percentage NIET tussen 10-30% ligt, draai het om (100 - percentage)
     */
    public function up(): void
    {
        // Log huidige staat
        $prestaties = DB::table('prestaties')->get();
        \Log::info('=== VOOR CORRECTIE ===');
        foreach ($prestaties as $p) {
            \Log::info("Prestatie {$p->id}: {$p->bruto_prijs} @ {$p->commissie_percentage}%");
        }
        
        // Update ALLE prestaties waar commissie_percentage > 50% (medewerker percentage ipv organisatie)
        DB::table('prestaties')
            ->where('commissie_percentage', '>', 50)
            ->update([
                'commissie_percentage' => DB::raw('(100 - commissie_percentage)')
            ]);
        
        // Log na correctie
        $prestaties = DB::table('prestaties')->get();
        \Log::info('=== NA CORRECTIE ===');
        foreach ($prestaties as $p) {
            \Log::info("Prestatie {$p->id}: {$p->bruto_prijs} @ {$p->commissie_percentage}%");
        }
    }

    public function down(): void
    {
        // Draai terug
        DB::table('prestaties')
            ->where('commissie_percentage', '<', 50)
            ->update([
                'commissie_percentage' => DB::raw('(100 - commissie_percentage)')
            ]);
    }
};
