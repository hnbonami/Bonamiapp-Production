<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Eerst controleren welke kolommen er al zijn
        $existingColumns = Schema::getColumnListing('testzadels');
        
        Schema::table('testzadels', function (Blueprint $table) use ($existingColumns) {
            // Voeg alleen de nieuwe uitleensysteem kolommen toe
            if (!in_array('onderdeel_type', $existingColumns)) {
                $table->enum('onderdeel_type', ['testzadel', 'nieuw zadel', 'zooltjes', 'Lake schoenen'])
                      ->default('testzadel')
                      ->after('bikefit_id');
                \Log::info('Added onderdeel_type column');
            }
            
            if (!in_array('onderdeel_status', $existingColumns)) {
                $table->enum('onderdeel_status', ['nieuw', 'test', 'besteld'])
                      ->nullable()
                      ->after('onderdeel_type');
                \Log::info('Added onderdeel_status column');
            }
            
            if (!in_array('automatisch_mailtje', $existingColumns)) {
                $table->boolean('automatisch_mailtje')
                      ->default(false)
                      ->after('onderdeel_status');
                \Log::info('Added automatisch_mailtje column');
            }
            
            if (!in_array('onderdeel_omschrijving', $existingColumns)) {
                $table->string('onderdeel_omschrijving')
                      ->nullable()
                      ->after('automatisch_mailtje');
                \Log::info('Added onderdeel_omschrijving column');
            }
        });
    }

    public function down()
    {
        Schema::table('testzadels', function (Blueprint $table) {
            $existingColumns = Schema::getColumnListing('testzadels');
            
            if (in_array('onderdeel_type', $existingColumns)) {
                $table->dropColumn('onderdeel_type');
            }
            if (in_array('onderdeel_status', $existingColumns)) {
                $table->dropColumn('onderdeel_status');
            }
            if (in_array('automatisch_mailtje', $existingColumns)) {
                $table->dropColumn('automatisch_mailtje');
            }
            if (in_array('onderdeel_omschrijving', $existingColumns)) {
                $table->dropColumn('onderdeel_omschrijving');
            }
        });
    }
};