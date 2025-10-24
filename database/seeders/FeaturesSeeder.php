<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use Illuminate\Support\Facades\DB;

class FeaturesSeeder extends Seeder
{
    /**
     * Seed standaard features
     */
    public function run(): void
    {
        $features = [
            // BEHEER FEATURES
            [
                'key' => 'klantenbeheer',
                'naam' => 'Klantenbeheer',
                'beschrijving' => 'Klanten toevoegen, bewerken en beheren',
                'categorie' => 'beheer',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 10,
            ],
            [
                'key' => 'medewerkerbeheer',
                'naam' => 'Medewerkerbeheer',
                'beschrijving' => 'Medewerkers aanmaken en rechten beheren',
                'categorie' => 'beheer',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 20,
            ],
            
            // METINGEN FEATURES
            [
                'key' => 'bikefits',
                'naam' => 'Bikefit Metingen',
                'beschrijving' => 'Bikefit metingen aanmaken, bewerken en rapportages genereren',
                'categorie' => 'metingen',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 30,
            ],
            [
                'key' => 'inspanningstesten',
                'naam' => 'Inspanningstesten',
                'beschrijving' => 'Inspanningstesten uitvoeren en resultaten bijhouden',
                'categorie' => 'metingen',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 40,
            ],
            [
                'key' => 'veldtesten',
                'naam' => 'Veldtesten',
                'beschrijving' => 'Veldtesten registreren en analyseren',
                'categorie' => 'metingen',
                'is_premium' => true,
                'prijs_per_maand' => 10.00,
                'sorteer_volgorde' => 50,
            ],
            
            // MATERIAAL & UITLENEN
            [
                'key' => 'testzadels',
                'naam' => 'Testzadel Uitleensysteem',
                'beschrijving' => 'Testzadels beheren en uitlenen aan klanten',
                'categorie' => 'beheer',
                'is_premium' => true,
                'prijs_per_maand' => 10.00,
                'sorteer_volgorde' => 60,
            ],
            
            // RAPPORTAGE & COMMUNICATIE
            [
                'key' => 'sjablonen',
                'naam' => 'Sjablonen',
                'beschrijving' => 'Rapport sjablonen beheren voor PDF generatie',
                'categorie' => 'beheer',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 70,
            ],
            
            // PREMIUM FEATURES
            [
                'key' => 'api_toegang',
                'naam' => 'API Toegang',
                'beschrijving' => 'REST API voor integratie met externe systemen',
                'categorie' => 'geavanceerd',
                'is_premium' => true,
                'prijs_per_maand' => 25.00,
                'sorteer_volgorde' => 100,
            ],
            [
                'key' => 'custom_branding',
                'naam' => 'Custom Branding',
                'beschrijving' => 'Eigen logo, kleuren en huisstijl in rapporten',
                'categorie' => 'geavanceerd',
                'is_premium' => true,
                'prijs_per_maand' => 12.00,
                'sorteer_volgorde' => 110,
            ],
            [
                'key' => 'analytics',
                'naam' => 'Analytics Dashboard',
                'beschrijving' => 'Uitgebreide statistieken en analyse dashboard',
                'categorie' => 'geavanceerd',
                'is_premium' => true,
                'prijs_per_maand' => 18.00,
                'sorteer_volgorde' => 120,
            ],
            [
                'key' => 'database_tools',
                'naam' => 'Database Tools',
                'beschrijving' => 'Geavanceerde database import/export tools',
                'categorie' => 'geavanceerd',
                'is_premium' => true,
                'prijs_per_maand' => 20.00,
                'sorteer_volgorde' => 130,
            ],
            
            // MARKETING & SOCIAL
            [
                'key' => 'instagram',
                'naam' => 'Instagram',
                'beschrijving' => 'Instagram integratie en social media management',
                'categorie' => 'geavanceerd',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 80,
            ],
            [
                'key' => 'nieuwsbrief',
                'naam' => 'Nieuwsbrief',
                'beschrijving' => 'Email nieuwsbrieven versturen naar klanten',
                'categorie' => 'geavanceerd',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 90,
            ],
        ];

        foreach ($features as $featureData) {
            Feature::updateOrCreate(
                ['key' => $featureData['key']],
                $featureData
            );
        }

        $this->command->info('✅ Features aangemaakt/bijgewerkt: ' . count($features));
        
        // Geef organisatie ID 1 (Bonami hoofdorganisatie) alle features
        $bonamiOrg = \App\Models\Organisatie::find(1);
        if ($bonamiOrg) {
            $allFeatureIds = Feature::pluck('id')->toArray();
            foreach ($allFeatureIds as $featureId) {
                DB::table('organisatie_features')->updateOrInsert(
                    [
                        'organisatie_id' => 1,
                        'feature_id' => $featureId
                    ],
                    [
                        'is_actief' => true,
                        'expires_at' => null,
                        'notities' => 'Hoofdorganisatie heeft altijd alle features',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
            $this->command->info('✅ Alle features toegewezen aan Bonami (organisatie ID 1)');
        }
    }
}