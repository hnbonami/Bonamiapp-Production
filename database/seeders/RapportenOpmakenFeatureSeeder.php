<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;

class RapportenOpmakenFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Feature::updateOrCreate(
            ['key' => 'rapporten_opmaken'],
            [
                'slug' => 'rapporten-opmaken',
                'naam' => 'Rapporten Opmaken',
                'beschrijving' => 'Personaliseer rapporten met eigen header, footer, logo, kleuren en contactgegevens. Maak professionele rapporten in jouw huisstijl!',
                'categorie' => 'Rapporten & PDF',
                'icoon' => '📄',
                'is_premium' => true,
                'prijs_per_maand' => 19.99,
                'is_actief' => true,
                'sorteer_volgorde' => 60,
            ]
        );

        $this->command->info('✅ Feature "Rapporten Opmaken" aangemaakt!');
    }
}
