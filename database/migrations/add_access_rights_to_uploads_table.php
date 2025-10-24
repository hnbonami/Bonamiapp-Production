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
        Schema::table('uploads', function (Blueprint $table) {
            // Toegangsrechten: alleen_mezelf, klant, alle_medewerkers, iedereen
            // Voeg toe na 'path' kolom (of aan het einde als path niet bestaat)
            if (Schema::hasColumn('uploads', 'path')) {
                $table->string('toegang', 50)->default('alle_medewerkers')->after('path');
            } else {
                $table->string('toegang', 50)->default('alle_medewerkers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropColumn('toegang');
        });
    }
};
