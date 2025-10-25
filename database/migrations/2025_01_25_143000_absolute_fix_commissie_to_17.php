<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * ABSOLUTE FIX - Forceer ALLE commissie percentages naar 17%
     */
    public function up(): void
    {
        // Set ALLE prestaties naar 17% (Bonami commissie)
        DB::table('prestaties')->update(['commissie_percentage' => 17.0]);
        
        \Log::info('✅ ALLE prestaties geforceerd naar 17% commissie');
    }

    public function down(): void
    {
        // Rollback niet nodig
    }
};
