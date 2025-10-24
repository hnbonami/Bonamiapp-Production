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
        Schema::table('diensten', function (Blueprint $table) {
            // Check welke kolommen al bestaan en voeg alleen ontbrekende toe
            if (!Schema::hasColumn('diensten', 'btw_percentage')) {
                $table->decimal('btw_percentage', 5, 2)->default(21.00)->after('standaard_prijs');
            }
            
            if (!Schema::hasColumn('diensten', 'prijs_incl_btw')) {
                $table->decimal('prijs_incl_btw', 10, 2)->nullable()->after('btw_percentage');
            }
            
            if (!Schema::hasColumn('diensten', 'prijs_excl_btw')) {
                $table->decimal('prijs_excl_btw', 10, 2)->nullable()->after('prijs_incl_btw');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diensten', function (Blueprint $table) {
            $table->dropColumn([
                'btw_percentage',
                'prijs_incl_btw',
                'prijs_excl_btw',
                'prijs_incl_btw'
            ]);
        });
    }
};
