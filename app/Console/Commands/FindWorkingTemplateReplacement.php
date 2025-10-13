<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindWorkingTemplateReplacement extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:find-working-replacement';

    /**
     * The console command description.
     */
    protected $description = 'Vind de werkende template replacement logica en voeg nieuwe velden toe';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Zoeken naar de WERKENDE template replacement logica...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            $content = File::get($controllerPath);
            
            // Zoek naar de WERKENDE opmerkingen replacement
            $this->info('🎯 Zoeken naar {{bikefit.opmerkingen}} replacement...');
            
            if (preg_match("/str_replace\('{{bikefit\.opmerkingen}}.*?;/", $content, $matches)) {
                $workingReplacement = $matches[0];
                $this->info("✅ GEVONDEN werkende replacement:");
                $this->line("  {$workingReplacement}");
                
                // Zoek de context rond deze replacement
                $lines = explode("\n", $content);
                foreach ($lines as $lineNum => $line) {
                    if (strpos($line, "{{bikefit.opmerkingen}}") !== false) {
                        $this->info("");
                        $this->info("📍 Gevonden op lijn " . ($lineNum + 1) . " in context:");
                        
                        // Toon context
                        for ($i = max(0, $lineNum - 3); $i <= min(count($lines) - 1, $lineNum + 10); $i++) {
                            $prefix = $i == $lineNum ? ">>> " : "    ";
                            $this->line($prefix . "Lijn " . ($i + 1) . ": " . trim($lines[$i]));
                        }
                        
                        // Voeg nieuwe replacements toe NA de opmerkingen replacement
                        $newReplacements = "
                // NIEUWE BIKEFIT VELDEN - toegevoegd door bonami:find-working-replacement
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
                        
                        // Voeg toe na de opmerkingen lijn
                        $targetLine = trim($lines[$lineNum]);
                        $newContent = str_replace($targetLine, $targetLine . $newReplacements, $content);
                        
                        // Backup maken
                        $backupPath = $controllerPath . '.backup.working-fix.' . date('Y-m-d-H-i-s');
                        File::put($backupPath, $content);
                        $this->info("");
                        $this->info("📄 Backup gemaakt: {$backupPath}");
                        
                        // Schrijf nieuwe content
                        File::put($controllerPath, $newContent);
                        $this->info("✅ 23 nieuwe bikefit replacements toegevoegd NA de werkende opmerkingen replacement!");
                        
                        break;
                    }
                }
                
            } else {
                $this->error("❌ Kon de werkende {{bikefit.opmerkingen}} replacement niet vinden");
                return Command::FAILURE;
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}