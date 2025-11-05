<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bikefits', function (Blueprint $table) {
            // Wijzig alle custom result kolommen van decimal(8,2) naar decimal(8,1)
            $columns = [
                'prognose_zadelhoogte', 'prognose_zadelterugstand', 'prognose_zadelterugstand_top',
                'prognose_horizontale_reach', 'prognose_reach', 'prognose_drop', 
                'prognose_cranklengte', 'prognose_stuurbreedte',
                'voor_zadelhoogte', 'voor_zadelterugstand', 'voor_zadelterugstand_top',
                'voor_horizontale_reach', 'voor_reach', 'voor_drop',
                'voor_cranklengte', 'voor_stuurbreedte',
                'na_zadelhoogte', 'na_zadelterugstand', 'na_zadelterugstand_top',
                'na_horizontale_reach', 'na_reach', 'na_drop',
                'na_cranklengte', 'na_stuurbreedte'
            ];
            
            foreach ($columns as $column) {
                $table->decimal($column, 8, 1)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bikefits', function (Blueprint $table) {
            // Zet terug naar decimal(8,2)
            $columns = [
                'prognose_zadelhoogte', 'prognose_zadelterugstand', 'prognose_zadelterugstand_top',
                'prognose_horizontale_reach', 'prognose_reach', 'prognose_drop', 
                'prognose_cranklengte', 'prognose_stuurbreedte',
                'voor_zadelhoogte', 'voor_zadelterugstand', 'voor_zadelterugstand_top',
                'voor_horizontale_reach', 'voor_reach', 'voor_drop',
                'voor_cranklengte', 'voor_stuurbreedte',
                'na_zadelhoogte', 'na_zadelterugstand', 'na_zadelterugstand_top',
                'na_horizontale_reach', 'na_reach', 'na_drop',
                'na_cranklengte', 'na_stuurbreedte'
            ];
            
            foreach ($columns as $column) {
                $table->decimal($column, 8, 2)->nullable()->change();
            }
        });
    }
};