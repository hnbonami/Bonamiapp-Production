<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TemplateKey;

class AddMissingBikefitKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:add-missing-bikefit-keys';

    /**
     * The console command description.
     */
    protected $description = 'Voeg alle ontbrekende bikefit template keys toe';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Adding missing bikefit template keys...');

        try {
            // Alle bikefit velden die beschikbaar moeten zijn
            $bikefitKeys = [
                // Basis gegevens
                'datum' => 'Datum',
                'testtype' => 'Test Type',
                'type_fitting' => 'Type Fitting',
                
                // Fiets informatie
                'fietsmerk' => 'Fiets Merk',
                'kadermaat' => 'Kadermaat',
                'bouwjaar' => 'Bouwjaar', 
                'type_fiets' => 'Type Fiets',
                'frametype' => 'Frametype',
                
                // Lichaamsmaten
                'lengte_cm' => 'Lengte (cm)',
                'binnenbeenlengte_cm' => 'Binnenbeenlengte (cm)',
                'armlengte_cm' => 'Armlengte (cm)',
                'romplengte_cm' => 'Romplengte (cm)',
                'schouderbreedte_cm' => 'Schouderbreedte (cm)',
                
                // Zitpositie metingen
                'zadel_trapas_hoek' => 'Zadel-trapas Hoek',
                'zadel_trapas_afstand' => 'Zadel-trapas Afstand',
                'stuur_trapas_hoek' => 'Stuur-trapas Hoek',
                'stuur_trapas_afstand' => 'Stuur-trapas Afstand',
                'zadel_lengte_center_top' => 'Zadel Lengte Center-Top',
                
                // Aanpassingen - BELANGRIJKE ONTBREKENDE VELDEN!
                'aanpassingen_zadel' => 'Aanpassingen Zadel (cm)',
                'aanpassingen_setback' => 'Aanpassingen Setback (cm)',
                'aanpassingen_reach' => 'Aanpassingen Reach (cm)',
                'aanpassingen_drop' => 'Aanpassingen Drop (cm)',
                
                // Stuurpen aanpassingen - BELANGRIJKE ONTBREKENDE VELDEN!
                'aanpassingen_stuurpen_aan' => 'Stuurpen Aanpassingen (Ja/Nee)',
                'aanpassingen_stuurpen_pre' => 'Stuurpen Voor (Pre)',
                'aanpassingen_stuurpen_post' => 'Stuurpen Na (Post)',
                
                // Zadel specificaties
                'type_zadel' => 'Type Zadel',
                'zadeltil' => 'Zadeltil',
                'zadelbreedte' => 'Zadelbreedte (mm)',
                'nieuw_testzadel' => 'Nieuw Testzadel',
                
                // Schoenplaatjes aanpassingen - BELANGRIJKE ONTBREKENDE VELDEN!
                'rotatie_aanpassingen' => 'Rotatie Aanpassingen Schoenplaatjes',
                'inclinatie_aanpassingen' => 'Inclinatie Aanpassingen Schoenplaatjes',
                'ophoging_li' => 'Ophoging Links (mm)',
                'ophoging_re' => 'Ophoging Rechts (mm)',
                
                // Anamnese
                'algemene_klachten' => 'Algemene Klachten',
                'beenlengteverschil' => 'Beenlengteverschil (Ja/Nee)',
                'beenlengteverschil_cm' => 'Beenlengteverschil (cm)',
                'lenigheid_hamstrings' => 'Lenigheid Hamstrings',
                'steunzolen' => 'Steunzolen (Ja/Nee)',
                'steunzolen_reden' => 'Steunzolen Reden',
                
                // Voetmeting
                'schoenmaat' => 'Schoenmaat',
                'voetbreedte' => 'Voetbreedte (cm)',
                'voetpositie' => 'Voetpositie',
                
                // Functionele mobiliteit
                'straight_leg_raise_links' => 'Straight Leg Raise Links',
                'straight_leg_raise_rechts' => 'Straight Leg Raise Rechts',
                'knieflexie_links' => 'Knieflexie Links',
                'knieflexie_rechts' => 'Knieflexie Rechts',
                'heup_endorotatie_links' => 'Heup Endorotatie Links',
                'heup_endorotatie_rechts' => 'Heup Endorotatie Rechts',
                'heup_exorotatie_links' => 'Heup Exorotatie Links',
                'heup_exorotatie_rechts' => 'Heup Exorotatie Rechts',
                'enkeldorsiflexie_links' => 'Enkeldorsiflexie Links',
                'enkeldorsiflexie_rechts' => 'Enkeldorsiflexie Rechts',
                'one_leg_squat_links' => 'One Leg Squat Links',
                'one_leg_squat_rechts' => 'One Leg Squat Rechts',
                
                // Opmerkingen
                'opmerkingen' => 'Opmerkingen',
                'interne_opmerkingen' => 'Interne Opmerkingen'
            ];

            // Check welke keys al bestaan
            $existingKeys = TemplateKey::where('category', 'bikefit')->get();
            $existingPlaceholders = $existingKeys->pluck('key')->map(function($key) {
                return str_replace(['{{bikefit.', '}}'], '', $key);
            })->toArray();

            $this->info("📊 Bestaande bikefit keys: " . count($existingPlaceholders));
            
            $addedCount = 0;
            $skippedCount = 0;

            foreach ($bikefitKeys as $veld => $displayName) {
                $placeholder = "{{bikefit.{$veld}}}";
                
                if (!in_array($veld, $existingPlaceholders)) {
                    // Voeg nieuwe key toe
                    TemplateKey::create([
                        'key' => $placeholder,
                        'description' => $displayName,
                        'category' => 'bikefit'
                    ]);
                    
                    $this->info("✅ Added: {$displayName} ({$placeholder})");
                    $addedCount++;
                } else {
                    $this->line("⏭️ Skipped: {$displayName} (already exists)");
                    $skippedCount++;
                }
            }

            $this->info('');
            $this->info("🎉 Summary:");
            $this->info("   ✅ Added: {$addedCount} new bikefit template keys");
            $this->info("   ⏭️ Skipped: {$skippedCount} existing keys");
            $this->info("   📊 Total bikefit keys now: " . (count($existingPlaceholders) + $addedCount));

            $this->info('');
            $this->info('🚀 Alle ontbrekende bikefit template keys zijn toegevoegd!');
            $this->info('Je kunt ze nu gebruiken in de sjabloon editor.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error adding bikefit keys: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Geef een voorbeeld waarde voor een veld
     */
    private function getExampleValue($veld): string
    {
        $examples = [
            'datum' => '2025-01-15',
            'testtype' => 'standaard bikefit',
            'type_fitting' => 'comfort',
            'fietsmerk' => 'Trek',
            'kadermaat' => '56',
            'bouwjaar' => '2023',
            'type_fiets' => 'racefiets',
            'frametype' => 'carbon',
            'lengte_cm' => '180',
            'binnenbeenlengte_cm' => '85',
            'armlengte_cm' => '65',
            'romplengte_cm' => '95',
            'schouderbreedte_cm' => '42',
            'zadel_trapas_hoek' => '75.0',
            'zadel_trapas_afstand' => '74.5',
            'stuur_trapas_hoek' => '80.0',
            'stuur_trapas_afstand' => '65.0',
            'zadel_lengte_center_top' => '14.0',
            'aanpassingen_zadel' => '2.5',
            'aanpassingen_setback' => '1.0',
            'aanpassingen_reach' => '0.5',
            'aanpassingen_drop' => '0.0',
            'aanpassingen_stuurpen_aan' => 'Ja',
            'aanpassingen_stuurpen_pre' => '100',
            'aanpassingen_stuurpen_post' => '90',
            'type_zadel' => 'Selle Italia',
            'zadeltil' => '0.5',
            'zadelbreedte' => '143',
            'nieuw_testzadel' => 'Fizik Antares',
            'rotatie_aanpassingen' => 'geen',
            'inclinatie_aanpassingen' => 'geen',
            'ophoging_li' => '0',
            'ophoging_re' => '0',
            'algemene_klachten' => 'geen klachten',
            'beenlengteverschil' => 'Nee',
            'beenlengteverschil_cm' => '0',
            'lenigheid_hamstrings' => 'gemiddeld',
            'steunzolen' => 'Nee',
            'steunzolen_reden' => '',
            'schoenmaat' => '43',
            'voetbreedte' => '10.5',
            'voetpositie' => 'neutraal',
            'straight_leg_raise_links' => 'Hoog',
            'straight_leg_raise_rechts' => 'Hoog',
            'knieflexie_links' => 'Gemiddeld',
            'knieflexie_rechts' => 'Gemiddeld',
            'opmerkingen' => 'Bikefit succesvol afgerond',
            'interne_opmerkingen' => 'Klant tevreden met aanpassingen'
        ];

        return $examples[$veld] ?? 'N/A';
    }
}