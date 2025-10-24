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
        Schema::table('klant_documenten', function (Blueprint $table) {
            // Toegangsrechten kolom toevoegen
            if (!Schema::hasColumn('klant_documenten', 'toegang')) {
                $table->string('toegang', 50)->default('alle_medewerkers')->after('upload_datum');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('klant_documenten', function (Blueprint $table) {
            $table->dropColumn('toegang');
        });
    }
};
