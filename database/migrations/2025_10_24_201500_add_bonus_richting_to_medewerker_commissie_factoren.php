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
        Schema::table('medewerker_commissie_factoren', function (Blueprint $table) {
            // Voeg bonus_richting kolom toe na ancienniteit_factor
            if (!Schema::hasColumn('medewerker_commissie_factoren', 'bonus_richting')) {
                $table->enum('bonus_richting', ['plus', 'min'])
                      ->default('plus')
                      ->after('ancienniteit_factor')
                      ->comment('Plus = meer naar medewerker, Min = meer naar organisatie');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medewerker_commissie_factoren', function (Blueprint $table) {
            if (Schema::hasColumn('medewerker_commissie_factoren', 'bonus_richting')) {
                $table->dropColumn('bonus_richting');
            }
        });
    }
};
