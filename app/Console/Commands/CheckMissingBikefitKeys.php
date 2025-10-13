<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TemplateKey;

class CheckMissingBikefitKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-missing-bikefit-keys';

    /**
     * The console command description.
     */
    protected $description = 'Check welke bikefit template keys ontbreken';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Controleren bestaande template keys...');

        try {
            // Toon alle bestaande bikefit keys
            $existingKeys = TemplateKey::where('category', 'bikefit')->get();
            
            $this->info('📋 Bestaande bikefit template keys:');
            if ($existingKeys->count() > 0) {
                foreach ($existingKeys as $key) {
                    $this->line("- {$key->description}: {$key->key}");
                }
            } else {
                $this->line("  Geen bestaande bikefit keys gevonden.");
            }
            
            $this->info('');
            $this->info('🔍 Alle bikefit database velden die beschikbaar zijn:');
            
            // Alle bikefit velden uit de database schema
            $bikefitVelden = [
                // Basis gegevens
                'datum' => 'Datum',
                'testtype' => 'Test Type',
                'type_fitting' => 'Type Fitting',
                
                // Fiets info
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
                'zadel_trapas_hoek' => 'Zadel-trapas hoek',
                'zadel_trapas_afstand' => 'Zadel-trapas afstand',
                'stuur_trapas_hoek' => 'Stuur-trapas hoek',
                'stuur_trapas_afstand' => 'Stuur-trapas afstand',
                'zadel_lengte_center_top' => 'Zadel lengte center-top',
                
                // Aanpassingen
                'aanpassingen_zadel' => 'Aanpassingen Zadel',
                'aanpassingen_setback' => 'Aanpassingen Setback',
                'aanpassingen_reach' => 'Aanpassingen Reach',
                'aanpassingen_drop' => 'Aanpassingen Drop',
                
                // Stuurpen aanpassingen
                'aanpassingen_stuurpen_aan' => 'Stuurpen Aanpassingen (Ja/Nee)',
                'aanpassingen_stuurpen_pre' => 'Stuurpen Voor (Pre)',
                'aanpassingen_stuurpen_post' => 'Stuurpen Na (Post)',
                
                // Zadel specificaties
                'type_zadel' => 'Type Zadel',
                'zadeltil' => 'Zadeltil',
                'zadelbreedte' => 'Zadelbreedte',
                'nieuw_testzadel' => 'Nieuw Testzadel',
                
                // Schoenplaatjes aanpassingen
                'rotatie_aanpassingen' => 'Rotatie Aanpassingen',
                'inclinatie_aanpassingen' => 'Inclinatie Aanpassingen',
                'ophoging_li' => 'Ophoging Links',
                'ophoging_re' => 'Ophoging Rechts',
                
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
            
            // Check welke keys ontbreken
            $existingPlaceholders = $existingKeys->pluck('key')->map(function($key) {
                // Extract field name from {{bikefit.field_name}}
                return str_replace(['{{bikefit.', '}}'], '', $key);
            })->toArray();
            
            $this->info('');
            $this->info('❌ Ontbrekende bikefit template keys:');
            
            $missingCount = 0;
            foreach ($bikefitVelden as $veld => $displayName) {
                if (!in_array($veld, $existingPlaceholders)) {
                    $this->line("- {$displayName}: {{bikefit.{$veld}}}");
                    $missingCount++;
                }
            }
            
            $this->info('');
            $this->info("📊 Totaal ontbrekende keys: {$missingCount}");
            $this->info("📊 Totaal bestaande keys: " . $existingKeys->count());
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error checking missing keys: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}