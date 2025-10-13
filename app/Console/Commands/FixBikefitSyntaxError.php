<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixBikefitSyntaxError extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-syntax-error';

    /**
     * The console command description.
     */
    protected $description = 'Repareer de syntax error in BikefitController';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing syntax error in BikefitController...');

        try {
            $controllerPath = app_path('Http/Controllers/BikefitController.php');
            
            // Herstel van de backup
            $backupFiles = glob($controllerPath . '.backup.bikefit-fix.*');
            
            if (!empty($backupFiles)) {
                // Gebruik de nieuwste backup
                rsort($backupFiles);
                $latestBackup = $backupFiles[0];
                
                $this->info("📄 Herstellen van backup: {$latestBackup}");
                
                $backupContent = File::get($latestBackup);
                File::put($controllerPath, $backupContent);
                
                $this->info('✅ BikefitController hersteld van backup');
                
                // Nu voeg ik de nieuwe template replacements correct toe
                $content = File::get($controllerPath);
                
                // Zoek naar generateSjabloonReport method
                if (strpos($content, 'function generateSjabloonReport') !== false) {
                    $this->info('🔍 Zoeken naar de juiste plek voor template replacements...');
                    
                    $lines = explode("\n", $content);
                    $insertLine = -1;
                    
                    // Zoek naar een return statement in generateSjabloonReport
                    $inMethod = false;
                    for ($i = 0; $i < count($lines); $i++) {
                        if (strpos($lines[$i], 'function generateSjabloonReport') !== false) {
                            $inMethod = true;
                            continue;
                        }
                        
                        if ($inMethod && (strpos($lines[$i], 'return ') !== false)) {
                            $insertLine = $i;
                            $this->info("📍 Gevonden return statement op lijn " . ($i + 1));
                            break;
                        }
                        
                        // Als we een nieuwe method tegenkomen, stoppen
                        if ($inMethod && strpos($lines[$i], 'function ') !== false && strpos($lines[$i], 'function generateSjabloonReport') === false) {
                            break;
                        }
                    }
                    
                    if ($insertLine !== -1) {
                        // Voeg nieuwe template replacements toe VOOR de return statement
                        $newReplacements = "
        
        // NIEUWE BIKEFIT TEMPLATE KEYS - toegevoegd door bonami:fix-syntax-error
        if (isset(\$content)) {
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
            \$content = str_replace('{{bikefit.interne_opmerkingen}}', \$bikefit->interne_opmerkingen ?? '', \$content);
        }";
                        
                        // Voeg de replacements toe voor de return statement
                        $lines[$insertLine] = $newReplacements . "\n\n        " . $lines[$insertLine];
                        
                        $newContent = implode("\n", $lines);
                        File::put($controllerPath, $newContent);
                        
                        $this->info("✅ Template replacements correct toegevoegd voor return statement");
                    } else {
                        $this->warn('⚠️ Kon geen return statement vinden in generateSjabloonReport');
                    }
                } else {
                    $this->warn('⚠️ Kon generateSjabloonReport method niet vinden');
                }
                
            } else {
                $this->error('❌ Geen backup bestanden gevonden');
                return Command::FAILURE;
            }
            
            $this->info('');
            $this->info('🎯 TEST NU OPNIEUW:');
            $this->info('http://127.0.0.1:8000/bikefit/29/16/sjabloon-rapport');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}