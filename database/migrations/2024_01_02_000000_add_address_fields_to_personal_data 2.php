<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deze migratie voegt adres velden toe aan personal_data tabel als ze nog niet bestaan.
     */
    public function up(): void
    {
        // Controleer of de tabel bestaat voordat we kolommen toevoegen
        if (Schema::hasTable('personal_data')) {
            Schema::table('personal_data', function (Blueprint $table) {
                // Voeg alleen kolommen toe als ze nog niet bestaan
                if (!Schema::hasColumn('personal_data', 'adres')) {
                    $table->string('adres')->nullable()->after('telefoon');
                    \Log::info('✅ Adres kolom toegevoegd aan personal_data tabel');
                }
                
                if (!Schema::hasColumn('personal_data', 'postcode')) {
                    $table->string('postcode')->nullable()->after('adres');
                    \Log::info('✅ Postcode kolom toegevoegd aan personal_data tabel');
                }
                
                if (!Schema::hasColumn('personal_data', 'woonplaats')) {
                    $table->string('woonplaats')->nullable()->after('postcode');
                    \Log::info('✅ Woonplaats kolom toegevoegd aan personal_data tabel');
                }
                
                if (!Schema::hasColumn('personal_data', 'land')) {
                    $table->string('land')->nullable()->default('België')->after('woonplaats');
                    \Log::info('✅ Land kolom toegevoegd aan personal_data tabel');
                }
            });
            \Log::info('✅ Address fields migratie voltooid');
        } else {
            \Log::warning('⚠️ Personal_data tabel bestaat niet - migratie overgeslagen');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('personal_data')) {
            Schema::table('personal_data', function (Blueprint $table) {
                $columnsToRemove = ['adres', 'postcode', 'woonplaats', 'land'];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('personal_data', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};