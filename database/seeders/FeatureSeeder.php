<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            // METINGEN & TESTS
            [
                'key' => 'bikefits',
                'naam' => 'Bikefit Metingen',
                'beschrijving' => 'Bikefit metingen aanmaken, bewerken en rapportages genereren',
                'categorie' => 'metingen',
                'icoon' => 'adjustments-horizontal',
                'is_premium' => false,
                'sorteer_volgorde' => 10,
            ],
            [
                'key' => 'inspanningstesten',
                'naam' => 'Inspanningstesten',
                'beschrijving' => 'Inspanningstesten uitvoeren en analyseren met AI ondersteuning',
                'categorie' => 'metingen',
                'icoon' => 'chart-bar',
                'is_premium' => false,
                'sorteer_volgorde' => 20,
            ],
            [
                'key' => 'veldtesten',
                'naam' => 'Veldtesten',
                'beschrijving' => 'Veldtesten voor lopen, fietsen en zwemmen',
                'categorie' => 'metingen',
                'icoon' => 'play',
                'is_premium' => true,
                'prijs_per_maand' => 15.00,
                'sorteer_volgorde' => 30,
            ],

            // BEHEER
            [
                'key' => 'klanten_beheer',
                'naam' => 'Klantenbeheer',
                'beschrijving' => 'Klanten toevoegen, bewerken en beheren',
                'categorie' => 'beheer',
                'icoon' => 'users',
                'is_premium' => false,
                'sorteer_volgorde' => 40,
            ],
            [
                'key' => 'medewerkers_beheer',
                'naam' => 'Medewerkerbeheer',
                'beschrijving' => 'Medewerkers aanmaken en rechten beheren',
                'categorie' => 'beheer',
                'icoon' => 'user-group',
                'is_premium' => false,
                'sorteer_volgorde' => 50,
            ],
            [
                'key' => 'testzadels_beheer',
                'naam' => 'Testzadel Uitleensysteem',
                'beschrijving' => 'Testzadels beheren en uitlenen aan klanten',
                'categorie' => 'beheer',
                'icoon' => 'cube',
                'is_premium' => true,
                'prijs_per_maand' => 10.00,
                'sorteer_volgorde' => 60,
            ],

            // RAPPORTAGE
            [
                'key' => 'sjablonen',
                'naam' => 'Rapport Sjablonen',
                'beschrijving' => 'Aangepaste rapport sjablonen maken en beheren',
                'categorie' => 'rapportage',
                'icoon' => 'document-text',
                'is_premium' => false,
                'sorteer_volgorde' => 70,
            ],
            [
                'key' => 'pdf_generatie',
                'naam' => 'PDF Rapporten',
                'beschrijving' => 'PDF rapporten genereren voor klanten',
                'categorie' => 'rapportage',
                'icoon' => 'document-arrow-down',
                'is_premium' => false,
                'sorteer_volgorde' => 80,
            ],
            [
                'key' => 'email_templates',
                'naam' => 'Email Templates',
                'beschrijving' => 'Aangepaste email templates en automatische verzending',
                'categorie' => 'rapportage',
                'icoon' => 'envelope',
                'is_premium' => true,
                'prijs_per_maand' => 8.00,
                'sorteer_volgorde' => 90,
            ],

            // GEAVANCEERD
            [
                'key' => 'database_tools',
                'naam' => 'Database Tools',
                'beschrijving' => 'Geavanceerde database import/export tools',
                'categorie' => 'geavanceerd',
                'icoon' => 'circle-stack',
                'is_premium' => true,
                'prijs_per_maand' => 20.00,
                'sorteer_volgorde' => 100,
            ],
            [
                'key' => 'api_toegang',
                'naam' => 'API Toegang',
                'beschrijving' => 'REST API voor integratie met externe systemen',
                'categorie' => 'geavanceerd',
                'icoon' => 'code-bracket',
                'is_premium' => true,
                'prijs_per_maand' => 25.00,
                'sorteer_volgorde' => 110,
            ],
            [
                'key' => 'branding_layout',
                'naam' => 'Branding & Layout',
                'beschrijving' => 'Eigen logo, kleuren en huisstijl aanpassen in de applicatie',
                'categorie' => 'geavanceerd',
                'icoon' => 'paint-brush',
                'is_premium' => true,
                'prijs_per_maand' => 12.00,
                'sorteer_volgorde' => 120,
            ],
            [
                'key' => 'analytics',
                'naam' => 'Analytics Dashboard',
                'beschrijving' => 'Uitgebreide statistieken en analyse dashboard',
                'categorie' => 'geavanceerd',
                'icoon' => 'chart-pie',
                'is_premium' => true,
                'prijs_per_maand' => 18.00,
                'sorteer_volgorde' => 130,
            ],
        ];

        foreach ($features as $feature) {
            // Auto-genereer slug als deze niet bestaat
            if (!isset($feature['slug'])) {
                $feature['slug'] = \Illuminate\Support\Str::slug($feature['key']); // Gebruik key voor consistentie
            }
            
            Feature::updateOrCreate(
                ['key' => $feature['key']], // Match op key
                $feature // Update alle velden inclusief slug
            );
        }

        if ($this->command) {
            $this->command->info('✅ Features succesvol aangemaakt!');
        }
    }
}
