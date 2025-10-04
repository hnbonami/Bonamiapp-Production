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
        Schema::table('medewerkers', function (Blueprint $table) {
            // Voeg ontbrekende kolommen toe die in het model staan maar niet in de database
            if (!Schema::hasColumn('medewerkers', 'rol')) {
                $table->string('rol')->nullable()->after('functie');
            }
            if (!Schema::hasColumn('medewerkers', 'afdeling')) {
                $table->string('afdeling')->nullable()->after('rol');
            }
            if (!Schema::hasColumn('medewerkers', 'salaris')) {
                $table->decimal('salaris', 10, 2)->nullable()->after('afdeling');
            }
            if (!Schema::hasColumn('medewerkers', 'toegangsrechten')) {
                $table->text('toegangsrechten')->nullable()->after('salaris');
            }
            if (!Schema::hasColumn('medewerkers', 'toegangsniveau')) {
                $table->string('toegangsniveau')->nullable()->after('toegangsrechten');
            }
            if (!Schema::hasColumn('medewerkers', 'startdatum')) {
                $table->date('startdatum')->nullable()->after('in_dienst_sinds');
            }
            if (!Schema::hasColumn('medewerkers', 'bikefit')) {
                $table->boolean('bikefit')->default(false)->after('avatar_path');
            }
            if (!Schema::hasColumn('medewerkers', 'inspanningstest')) {
                $table->boolean('inspanningstest')->default(false)->after('bikefit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medewerkers', function (Blueprint $table) {
            $table->dropColumn([
                'rol',
                'afdeling', 
                'salaris',
                'toegangsrechten',
                'toegangsniveau',
                'startdatum',
                'bikefit',
                'inspanningstest'
            ]);
        });
    }
};