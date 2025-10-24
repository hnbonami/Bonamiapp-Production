<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use App\Models\Organisatie;

class PrestatiesFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Voeg prestaties feature toe met Feature model
        $feature = Feature::updateOrCreate(
            ['key' => 'prestaties'],
            [
                'naam' => 'Prestaties & Commissies',
                'beschrijving' => 'Coach prestaties bijhouden, commissies beheren en kwartaaloverzichten genereren',
                'categorie' => 'beheer',
                'icoon' => 'chart-bar-square',
                'is_premium' => false,
                'is_actief' => true,
                'sorteer_volgorde' => 55,
            ]
        );

        $this->command->info('✅ Prestaties feature aangemaakt: ' . $feature->naam);

        // Automatisch activeren voor de eerste organisatie (Bonami Sportcoaching)
        $bonami = Organisatie::first();
        
        if ($bonami && !$bonami->hasFeature('prestaties')) {
            $bonami->features()->attach($feature->id, [
                'is_actief' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('✅ Prestaties feature geactiveerd voor: ' . $bonami->naam);
        }

        // Basis diensten toevoegen (alleen als diensten tabel bestaat)
        if (!\Schema::hasTable('diensten')) {
            $this->command->warn('⚠️  Diensten tabel bestaat nog niet. Run eerst: php artisan migrate --path=database/migrations/2025_01_27_*');
            return;
        }

        $diensten = [
            [
                'naam' => 'Bikefit',
                'beschrijving' => 'Volledige bikefit meting en advies',
                'standaard_prijs' => 120.00,
                'btw_percentage' => 21.00,
                'is_actief' => true,
                'sorteer_volgorde' => 1,
            ],
            [
                'naam' => 'Inspanningstest',
                'beschrijving' => 'VO2max en lactaattest',
                'standaard_prijs' => 110.00,
                'btw_percentage' => 21.00,
                'is_actief' => true,
                'sorteer_volgorde' => 2,
            ],
            [
                'naam' => 'Schema (met feedback)',
                'beschrijving' => 'Trainingsschema inclusief feedback en begeleiding',
                'standaard_prijs' => 120.00,
                'btw_percentage' => 21.00,
                'is_actief' => true,
                'sorteer_volgorde' => 3,
            ],
            [
                'naam' => 'Schema (zonder feedback)',
                'beschrijving' => 'Trainingsschema zonder feedback',
                'standaard_prijs' => 40.00,
                'btw_percentage' => 21.00,
                'is_actief' => true,
                'sorteer_volgorde' => 4,
            ],
            [
                'naam' => 'Compositie meting',
                'beschrijving' => 'Lichaamssamenstelling analyse',
                'standaard_prijs' => 30.00,
                'btw_percentage' => 21.00,
                'is_actief' => true,
                'sorteer_volgorde' => 5,
            ],
        ];

        foreach ($diensten as $dienstData) {
            $dienst = \App\Models\Dienst::updateOrCreate(
                ['naam' => $dienstData['naam']],
                $dienstData
            );
            
            $this->command->info('✅ Dienst aangemaakt: ' . $dienst->naam . ' (€' . $dienst->standaard_prijs . ')');
        }

        $this->command->info('🎉 Prestaties systeem succesvol opgezet!');
    }
}
