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
        Schema::table('users', function (Blueprint $table) {
            // Adresgegevens
            if (!Schema::hasColumn('users', 'straatnaam')) {
                $table->string('straatnaam')->nullable();
            }
            if (!Schema::hasColumn('users', 'huisnummer')) {
                $table->string('huisnummer', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'postcode')) {
                $table->string('postcode', 10)->nullable();
            }
            if (!Schema::hasColumn('users', 'stad')) {
                $table->string('stad')->nullable();
            }
            
            // Werkgerelateerde informatie
            if (!Schema::hasColumn('users', 'functie')) {
                $table->string('functie')->nullable();
            }
            if (!Schema::hasColumn('users', 'startdatum')) {
                $table->date('startdatum')->nullable();
            }
            if (!Schema::hasColumn('users', 'contract_type')) {
                $table->string('contract_type')->nullable();
            }
            
            // Rechten en toegang
            if (!Schema::hasColumn('users', 'bikefit')) {
                $table->boolean('bikefit')->default(false);
            }
            if (!Schema::hasColumn('users', 'inspanningstest')) {
                $table->boolean('inspanningstest')->default(false);
            }
            if (!Schema::hasColumn('users', 'upload_documenten')) {
                $table->boolean('upload_documenten')->default(false);
            }
            
            // Opmerkingen/notities
            if (!Schema::hasColumn('users', 'notities')) {
                $table->text('notities')->nullable();
            }
        });
        
        // Status kolom moet apart aangepast worden als die al bestaat
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                // Wijzig bestaande status kolom naar string
                \DB::statement('ALTER TABLE users MODIFY status VARCHAR(255) DEFAULT "Actief"');
            } else {
                // Voeg toe als nieuwe kolom
                $table->string('status')->default('Actief');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'straatnaam', 'huisnummer', 'postcode', 'stad',
                'functie', 'startdatum', 'contract_type', 'status',
                'bikefit', 'inspanningstest', 'upload_documenten', 'notities'
            ]);
        });
    }
};