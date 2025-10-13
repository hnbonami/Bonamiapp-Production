<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixBikefitTemplateReplacement extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-bikefit-replacement';

    /**
     * The console command description.
     */
    protected $description = 'Fix bikefit template replacement om nieuwe velden te ondersteunen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing bikefit template replacement...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            
            // Zoek naar de methode die template replacement doet
            if (preg_match('/private function replaceTemplateKeys.*?\{(.*?)(?=private function|\}$)/s', $content, $matches)) {
                $this->info('✅ replaceTemplateKeys method gevonden');
                $method = $matches[0];
                
                // Toon huidige method
                $this->info('📋 Huidige replaceTemplateKeys method (eerste 500 chars):');
                $this->line(substr($method, 0, 500) . '...');
                
                // Check of bikefit replacement al bestaat
                if (strpos($method, 'bikefit.') !== false) {
                    $this->info('✅ Bikefit replacement gevonden');
                    
                    // Voeg nieuwe bikefit velden toe
                    $this->addNewBikefitFields($controllerPath, $content);
                } else {
                    $this->warn('⚠️ Geen bikefit replacement gevonden - voeg volledig toe');
                    $this->addFullBikefitReplacement($controllerPath, $content);
                }
                
            } else {
                $this->warn('⚠️ replaceTemplateKeys method niet gevonden');
                $this->info('🔍 Zoeken naar andere replacement methods...');
                
                // Zoek naar andere replacement patterns
                if (preg_match_all('/(function.*replace.*\{.*?\})/s', $content, $allMatches)) {
                    foreach ($allMatches[0] as $match) {
                        $this->line("Gevonden: " . substr($match, 0, 100) . '...');
                    }
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function addNewBikefitFields(string $controllerPath, string $content): void
    {
        $this->info('🔧 Adding new bikefit fields to existing replacement...');
        
        // Nieuwe bikefit velden die toegevoegd moeten worden
        $newFields = [
            'aanpassingen_zadel' => 'Aanpassingen Zadel',
            'aanpassingen_setback' => 'Aanpassingen Setback', 
            'aanpassingen_reach' => 'Aanpassingen Reach',
            'aanpassingen_drop' => 'Aanpassingen Drop',
            'aanpassingen_stuurpen_aan' => 'Stuurpen Aanpassingen (Ja/Nee)',
            'aanpassingen_stuurpen_pre' => 'Stuurpen Voor (Pre)',
            'aanpassingen_stuurpen_post' => 'Stuurpen Na (Post)',
            'rotatie_aanpassingen' => 'Rotatie Aanpassingen',
            'inclinatie_aanpassingen' => 'Inclinatie Aanpassingen',
            'ophoging_li' => 'Ophoging Links',
            'ophoging_re' => 'Ophoging Rechts',
            'type_fitting' => 'Type Fitting',
            'fietsmerk' => 'Fiets Merk',
            'kadermaat' => 'Kadermaat',
            'bouwjaar' => 'Bouwjaar',
            'type_fiets' => 'Type Fiets',
            'frametype' => 'Frametype',
            'armlengte_cm' => 'Armlengte (cm)',
            'romplengte_cm' => 'Romplengte (cm)',
            'schouderbreedte_cm' => 'Schouderbreedte (cm)',
            'zadel_lengte_center_top' => 'Zadel Lengte Center-Top',
            'type_zadel' => 'Type Zadel',
            'zadeltil' => 'Zadeltil',
            'zadelbreedte' => 'Zadelbreedte',
            'nieuw_testzadel' => 'Nieuw Testzadel',
            'algemene_klachten' => 'Algemene Klachten',
            'beenlengteverschil' => 'Beenlengteverschil (Ja/Nee)',
            'beenlengteverschil_cm' => 'Beenlengteverschil (cm)',
            'lenigheid_hamstrings' => 'Lenigheid Hamstrings',
            'steunzolen' => 'Steunzolen (Ja/Nee)',
            'steunzolen_reden' => 'Steunzolen Reden',
            'schoenmaat' => 'Schoenmaat',
            'voetbreedte' => 'Voetbreedte (cm)',
            'voetpositie' => 'Voetpositie',
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
            'interne_opmerkingen' => 'Interne Opmerkingen'
        ];
        
        // Zoek naar de plek waar bikefit replacements staan
        $pattern = '/(\$content = str_replace\(\'\{\{bikefit\..*?\}\}\', .*?, \$content\);)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $lastBikefitReplacement = $matches[1];
            $this->info("✅ Laatste bikefit replacement gevonden: " . substr($lastBikefitReplacement, 0, 100));
            
            // Genereer nieuwe replacement regels
            $newReplacements = '';
            foreach ($newFields as $field => $description) {
                $newReplacements .= "\n            \$content = str_replace('{{bikefit.{$field}}}', \$bikefit->{$field} ?? 'N/A', \$content);";
            }
            
            // Voeg nieuwe replacements toe na de laatste bestaande
            $newContent = str_replace($lastBikefitReplacement, $lastBikefitReplacement . $newReplacements, $content);
            
            // Backup maken
            $backupPath = $controllerPath . '.backup.bikefit-fields.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $content);
            $this->info("📄 Backup gemaakt: {$backupPath}");
            
            // Schrijf nieuwe content
            File::put($controllerPath, $newContent);
            $this->info("✅ " . count($newFields) . " nieuwe bikefit velden toegevoegd aan replacement logica");
            
        } else {
            $this->warn('⚠️ Kon bestaande bikefit replacements niet vinden voor uitbreiding');
        }
    }

    private function addFullBikefitReplacement(string $controllerPath, string $content): void
    {
        $this->info('🔧 Adding full bikefit replacement logic...');
        // Implementatie voor als er helemaal geen bikefit replacement is
        $this->warn('⚠️ Full bikefit replacement implementatie nog niet geïmplementeerd');
    }
}