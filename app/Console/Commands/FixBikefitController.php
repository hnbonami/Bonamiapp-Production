<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixBikefitController extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-bikefit-controller';

    /**
     * The console command description.
     */
    protected $description = 'Voeg nieuwe template replacements toe aan BikefitController';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Adding new template replacements to BikefitController...');

        try {
            $controllerPath = app_path('Http/Controllers/BikefitController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('BikefitController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            
            // Zoek naar bestaande template replacements
            $this->info('🔍 Zoeken naar bestaande template replacements...');
            
            $lines = explode("\n", $content);
            $replacementFound = false;
            $insertLine = -1;
            
            // Zoek naar een lijn die lijkt op template replacement
            for ($i = 0; $i < count($lines); $i++) {
                if (strpos($lines[$i], "str_replace('{{bikefit.") !== false ||
                    strpos($lines[$i], "str_replace(\"{{bikefit.") !== false) {
                    
                    $this->info("✅ Gevonden template replacement op lijn " . ($i + 1) . ":");
                    $this->line("  " . trim($lines[$i]));
                    $replacementFound = true;
                    
                    // Zoek de laatste replacement in deze block
                    for ($j = $i; $j < count($lines); $j++) {
                        if (strpos($lines[$j], "str_replace('{{bikefit.") !== false ||
                            strpos($lines[$j], "str_replace(\"{{bikefit.") !== false) {
                            $insertLine = $j;
                        } else if (trim($lines[$j]) !== '' && strpos($lines[$j], 'str_replace') === false) {
                            // We've found a non-empty line that's not a replacement, stop here
                            break;
                        }
                    }
                    
                    break;
                }
            }
            
            if (!$replacementFound) {
                $this->warn('⚠️ Geen bestaande template replacements gevonden in BikefitController');
                $this->info('🔍 Zoeken naar generateSjabloonReport method...');
                
                // Zoek naar generateSjabloonReport method
                for ($i = 0; $i < count($lines); $i++) {
                    if (strpos($lines[$i], 'function generateSjabloonReport') !== false) {
                        $this->info("✅ Gevonden generateSjabloonReport method op lijn " . ($i + 1));
                        
                        // Zoek naar het einde van de method om daar replacements toe te voegen
                        for ($j = $i; $j < count($lines); $j++) {
                            if (strpos($lines[$j], 'return ') !== false && 
                                (strpos($lines[$j], 'view(') !== false || strpos($lines[$j], 'response') !== false)) {
                                $insertLine = $j - 1; // Insert before return statement
                                $this->info("📍 Zal nieuwe replacements toevoegen voor lijn " . ($j + 1));
                                break;
                            }
                        }
                        break;
                    }
                }
            }
            
            if ($insertLine === -1) {
                $this->error('❌ Kon geen geschikte plek vinden om replacements toe te voegen');
                return Command::FAILURE;
            }
            
            // Genereer nieuwe replacement code
            $newReplacements = "
        
        // NIEUWE BIKEFIT TEMPLATE KEYS - toegevoegd door bonami:fix-bikefit-controller
        \$content = str_replace('{{bikefit.rotatie_aanpassingen}}', \$bikefit->rotatie_aanpassingen ?? '', \$content);
        \$content = str_replace('{{bikefit.inclinatie_aanpassingen}}', \$bikefit->inclinatie_aanpassingen ?? '', \$content);
        \$content = str_replace('{{bikefit.aanpassingen_stuurpen_pre}}', \$bikefit->aanpassingen_stuurpen_pre ?? '', \$content);
        \$content = str_replace('{{bikefit.aanpassingen_stuurpen_post}}', \$bikefit->aanpassingen_stuurpen_post ?? '', \$content);
        \$content = str_replace('{{bikefit.type_fitting}}', \$bikefit->type_fitting ?? '', \$content);
        \$content = str_replace('{{bikefit.type_fiets}}', \$bikefit->type_fiets ?? '', \$content);
        \$content = str_replace('{{bikefit.frametype}}', \$bikefit->frametype ?? '', \$content);
        \$content = str_replace('{{bikefit.type_zadel}}', \$bikefit->type_zadel ?? '', \$content);
        \$content = str_replace('{{bikefit.nieuw_testzadel}}', \$bikefit->nieuw_testzadel ?? '', \$content);
        \$content = str_replace('{{bikefit.lenigheid_hamstrings}}', \$bikefit->lenigheid_hamstrings ?? '', \$content);
        \$content = str_replace('{{bikefit.voetpositie}}', \$bikefit->voetpositie ?? '', \$content);
        \$content = str_replace('{{bikefit.aanpassingen_stuurpen_aan}}', \$bikefit->aanpassingen_stuurpen_aan ?? '', \$content);
        \$content = str_replace('{{bikefit.zadeltil}}', \$bikefit->zadeltil ?? '', \$content);
        \$content = str_replace('{{bikefit.zadelbreedte}}', \$bikefit->zadelbreedte ?? '', \$content);
        \$content = str_replace('{{bikefit.one_leg_squat_links}}', \$bikefit->one_leg_squat_links ?? '', \$content);
        \$content = str_replace('{{bikefit.one_leg_squat_rechts}}', \$bikefit->one_leg_squat_rechts ?? '', \$content);
        \$content = str_replace('{{bikefit.straight_leg_raise_links}}', \$bikefit->straight_leg_raise_links ?? '', \$content);
        \$content = str_replace('{{bikefit.straight_leg_raise_rechts}}', \$bikefit->straight_leg_raise_rechts ?? '', \$content);
        \$content = str_replace('{{bikefit.knieflexie_links}}', \$bikefit->knieflexie_links ?? '', \$content);
        \$content = str_replace('{{bikefit.knieflexie_rechts}}', \$bikefit->knieflexie_rechts ?? '', \$content);
        \$content = str_replace('{{bikefit.heup_endorotatie_links}}', \$bikefit->heup_endorotatie_links ?? '', \$content);
        \$content = str_replace('{{bikefit.heup_endorotatie_rechts}}', \$bikefit->heup_endorotatie_rechts ?? '', \$content);
        \$content = str_replace('{{bikefit.heup_exorotatie_links}}', \$bikefit->heup_exorotatie_links ?? '', \$content);
        \$content = str_replace('{{bikefit.heup_exorotatie_rechts}}', \$bikefit->heup_exorotatie_rechts ?? '', \$content);
        \$content = str_replace('{{bikefit.enkeldorsiflexie_links}}', \$bikefit->enkeldorsiflexie_links ?? '', \$content);
        \$content = str_replace('{{bikefit.enkeldorsiflexie_rechts}}', \$bikefit->enkeldorsiflexie_rechts ?? '', \$content);
        \$content = str_replace('{{bikefit.interne_opmerkingen}}', \$bikefit->interne_opmerkingen ?? '', \$content);";
            
            // Voeg nieuwe replacements toe
            $lines[$insertLine] = $lines[$insertLine] . $newReplacements;
            $newContent = implode("\n", $lines);
            
            // Backup maken
            $backupPath = $controllerPath . '.backup.bikefit-fix.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $content);
            $this->info("📄 Backup gemaakt: {$backupPath}");
            
            // Schrijf nieuwe content
            File::put($controllerPath, $newContent);
            $this->info("✅ 25 nieuwe bikefit template replacements toegevoegd aan BikefitController!");
            
            $this->info('');
            $this->info('🎯 TEST NU HET RAPPORT:');
            $this->info('1. Ga naar: http://127.0.0.1:8000/bikefit/29/16/sjabloon-rapport');
            $this->info('2. De nieuwe template keys zouden nu moeten werken!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}