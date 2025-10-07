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
        Schema::table('bikefits', function (Blueprint $table) {
            // Check if columns don't exist yet to prevent errors
            if (!Schema::hasColumn('bikefits', 'zadelhoogte_override')) {
                // Override velden voor berekende waarden
                $table->decimal('zadelhoogte_override', 8, 2)->nullable()->comment('Manual override for calculated saddle height');
                $table->decimal('zadelterugstand_override', 8, 2)->nullable()->comment('Manual override for calculated saddle setback');
                $table->decimal('zadelterugstand_top_override', 8, 2)->nullable()->comment('Manual override for calculated saddle setback top');
                $table->decimal('reach_override', 8, 2)->nullable()->comment('Manual override for calculated reach');
                $table->decimal('directe_reach_override', 8, 2)->nullable()->comment('Manual override for calculated direct reach');
                $table->decimal('drop_override', 8, 2)->nullable()->comment('Manual override for calculated drop');
                $table->decimal('cranklengte_override', 8, 2)->nullable()->comment('Manual override for calculated crank length');
                
                // Timestamp wanneer overrides zijn gemaakt
                $table->timestamp('overrides_updated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bikefits', function (Blueprint $table) {
            $table->dropColumn([
                'zadelhoogte_override',
                'zadelterugstand_override', 
                'zadelterugstand_top_override',
                'reach_override',
                'directe_reach_override',
                'drop_override',
                'cranklengte_override',
                'overrides_updated_at'
            ]);
        });
    }
};