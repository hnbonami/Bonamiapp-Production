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
            // Custom result kolommen voor PROGNOSE context (aan einde van tabel)
            $table->decimal('prognose_zadelhoogte', 8, 2)->nullable();
            $table->decimal('prognose_zadelterugstand', 8, 2)->nullable();
            $table->decimal('prognose_zadelterugstand_top', 8, 2)->nullable();
            $table->decimal('prognose_horizontale_reach', 8, 2)->nullable();
            $table->decimal('prognose_reach', 8, 2)->nullable();
            $table->decimal('prognose_drop', 8, 2)->nullable();
            $table->decimal('prognose_cranklengte', 8, 2)->nullable();
            $table->decimal('prognose_stuurbreedte', 8, 2)->nullable();
            
            // Custom result kolommen voor VOOR context
            $table->decimal('voor_zadelhoogte', 8, 2)->nullable();
            $table->decimal('voor_zadelterugstand', 8, 2)->nullable();
            $table->decimal('voor_zadelterugstand_top', 8, 2)->nullable();
            $table->decimal('voor_horizontale_reach', 8, 2)->nullable();
            $table->decimal('voor_reach', 8, 2)->nullable();
            $table->decimal('voor_drop', 8, 2)->nullable();
            $table->decimal('voor_cranklengte', 8, 2)->nullable();
            $table->decimal('voor_stuurbreedte', 8, 2)->nullable();
            
            // Custom result kolommen voor NA context (voor volledigheid)
            $table->decimal('na_zadelhoogte', 8, 2)->nullable();
            $table->decimal('na_zadelterugstand', 8, 2)->nullable();
            $table->decimal('na_zadelterugstand_top', 8, 2)->nullable();
            $table->decimal('na_horizontale_reach', 8, 2)->nullable();
            $table->decimal('na_reach', 8, 2)->nullable();
            $table->decimal('na_drop', 8, 2)->nullable();
            $table->decimal('na_cranklengte', 8, 2)->nullable();
            $table->decimal('na_stuurbreedte', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bikefits', function (Blueprint $table) {
            // Drop prognose kolommen
            $table->dropColumn([
                'prognose_zadelhoogte',
                'prognose_zadelterugstand',
                'prognose_zadelterugstand_top',
                'prognose_horizontale_reach',
                'prognose_reach',
                'prognose_drop',
                'prognose_cranklengte',
                'prognose_stuurbreedte',
            ]);
            
            // Drop voor kolommen
            $table->dropColumn([
                'voor_zadelhoogte',
                'voor_zadelterugstand',
                'voor_zadelterugstand_top',
                'voor_horizontale_reach',
                'voor_reach',
                'voor_drop',
                'voor_cranklengte',
                'voor_stuurbreedte',
            ]);
            
            // Drop na kolommen
            $table->dropColumn([
                'na_zadelhoogte',
                'na_zadelterugstand',
                'na_zadelterugstand_top',
                'na_horizontale_reach',
                'na_reach',
                'na_drop',
                'na_cranklengte',
                'na_stuurbreedte',
            ]);
        });
    }
};
