<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Voeg Analytics feature toe aan features tabel
        DB::table('features')->insert([
            'key' => 'analytics',
            'naam' => 'Analytics Dashboard',
            'beschrijving' => 'Uitgebreide statistieken & grafieken. KPI\'s, omzet trends, diensten verdeling en medewerker prestaties inzichtelijk maken.',
            'categorie' => 'rapportage',
            'icoon' => 'chart-bar',
            'is_premium' => true,
            'prijs_per_maand' => 19.99,
            'is_actief' => true,
            'sorteer_volgorde' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('features')->where('key', 'analytics')->delete();
    }
};
