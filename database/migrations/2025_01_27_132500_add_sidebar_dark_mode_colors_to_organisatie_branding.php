<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisatie_brandings', function (Blueprint $table) {
            // Sidebar kleuren
            $table->string('sidebar_achtergrond', 7)->nullable();
            $table->string('sidebar_tekst_kleur', 7)->nullable();
            $table->string('sidebar_actief_achtergrond', 7)->nullable();
            $table->string('sidebar_actief_lijn', 7)->nullable();
            
            // Dark mode kleuren
            $table->string('dark_achtergrond', 7)->nullable();
            $table->string('dark_tekst', 7)->nullable();
            $table->string('dark_navbar_achtergrond', 7)->nullable();
            $table->string('dark_sidebar_achtergrond', 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('organisatie_brandings', function (Blueprint $table) {
            $table->dropColumn([
                'sidebar_achtergrond',
                'sidebar_tekst_kleur',
                'sidebar_actief_achtergrond',
                'sidebar_actief_lijn',
                'dark_achtergrond',
                'dark_tekst',
                'dark_navbar_achtergrond',
                'dark_sidebar_achtergrond',
            ]);
        });
    }
};