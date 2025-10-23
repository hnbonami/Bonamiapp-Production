<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Geef alle klanten zonder organisatie_id de standaard organisatie (ID 1)
        \DB::table('klanten')
            ->whereNull('organisatie_id')
            ->update(['organisatie_id' => 1]);
            
        \Log::info('✅ Alle klanten zonder organisatie_id zijn nu gekoppeld aan organisatie 1');
    }

    public function down(): void
    {
        // Rollback: zet organisatie_id terug naar null voor organisatie 1 klanten
        // (alleen als ze oorspronkelijk null waren - maar dat kunnen we niet meer weten)
    }
};