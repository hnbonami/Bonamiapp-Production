<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddToPreviewMethod extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:add-to-preview';

    /**
     * The console command description.
     */
    protected $description = 'Voeg nieuwe bikefit replacements toe aan preview method';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Adding new replacements to preview() method...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            $content = File::get($controllerPath);
            
            // Zoek naar preview method en daarin naar bikefit.opmerkingen
            $lines = explode("\n", $content);
            $foundPreviewOpmerkingen = false;
            
            for ($i = 0; $i < count($lines); $i++) {
                // Check if we're in preview method and found bikefit.opmerkingen
                if (strpos($lines[$i], '{{bikefit.opmerkingen}}') !== false) {
                    // Check if we're in preview method by looking backwards
                    $inPreviewMethod = false;
                    for ($j = $i; $j >= 0; $j--) {
                        if (preg_match('/function\s+preview\s*\(/', $lines[$j])) {
                            $inPreviewMethod = true;
                            break;
                        }
                        if (preg_match('/function\s+\w+\s*\(/', $lines[$j]) && !preg_match('/function\s+preview\s*\(/', $lines[$j])) {
                            // We found another function first, so we're not in preview
                            break;
                        }
                    }
                    
                    if ($inPreviewMethod) {
                        $this->info("✅ Gevonden {{bikefit.opmerkingen}} in preview() method op lijn " . ($i + 1));
                        
                        // Check if nieuwe replacements al bestaan
                        if (strpos($content, 'NIEUWE BIKEFIT VELDEN - preview method') !== false) {
                            $this->warn('⚠️ Nieuwe replacements al toegevoegd aan preview method');
                            return Command::SUCCESS;
                        }
                        
                        // Voeg nieuwe replacements toe na opmerkingen lijn
                        $targetLine = trim($lines[$i]);
                        $newReplacements = "
                
                // NIEUWE BIKEFIT VELDEN - preview method toegevoegd
                \$content = str_replace('{{bikefit.rotatie_aanpassingen}}', \$dummyBikefit->rotatie_aanpassingen ?? 'Voorbeeld rotatie', \$content);
                \$content = str_replace('{{bikefit.inclinatie_aanpassingen}}', \$dummyBikefit->inclinatie_aanpassingen ?? 'Voorbeeld inclinatie', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_stuurpen_pre}}', \$dummyBikefit->aanpassingen_stuurpen_pre ?? '100mm', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_stuurpen_post}}', \$dummyBikefit->aanpassingen_stuurpen_post ?? '110mm', \$content);
                \$content = str_replace('{{bikefit.type_fitting}}', \$dummyBikefit->type_fitting ?? 'Bikefit Type', \$content);
                \$content = str_replace('{{bikefit.type_fiets}}', \$dummyBikefit->type_fiets ?? 'Racefiets', \$content);
                \$content = str_replace('{{bikefit.frametype}}', \$dummyBikefit->frametype ?? 'Carbon', \$content);
                \$content = str_replace('{{bikefit.type_zadel}}', \$dummyBikefit->type_zadel ?? 'Fizik Arione', \$content);
                \$content = str_replace('{{bikefit.nieuw_testzadel}}', \$dummyBikefit->nieuw_testzadel ?? 'Selle Italia', \$content);
                \$content = str_replace('{{bikefit.lenigheid_hamstrings}}', \$dummyBikefit->lenigheid_hamstrings ?? 'Goed', \$content);
                \$content = str_replace('{{bikefit.voetpositie}}', \$dummyBikefit->voetpositie ?? 'Neutraal', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_stuurpen_aan}}', \$dummyBikefit->aanpassingen_stuurpen_aan ?? 'Ja', \$content);
                \$content = str_replace('{{bikefit.zadeltil}}', \$dummyBikefit->zadeltil ?? '0°', \$content);
                \$content = str_replace('{{bikefit.zadelbreedte}}', \$dummyBikefit->zadelbreedte ?? '143mm', \$content);
                \$content = str_replace('{{bikefit.one_leg_squat_links}}', \$dummyBikefit->one_leg_squat_links ?? 'Stabiel', \$content);
                \$content = str_replace('{{bikefit.one_leg_squat_rechts}}', \$dummyBikefit->one_leg_squat_rechts ?? 'Stabiel', \$content);
                \$content = str_replace('{{bikefit.straight_leg_raise_links}}', \$dummyBikefit->straight_leg_raise_links ?? '75°', \$content);
                \$content = str_replace('{{bikefit.straight_leg_raise_rechts}}', \$dummyBikefit->straight_leg_raise_rechts ?? '75°', \$content);
                \$content = str_replace('{{bikefit.knieflexie_links}}', \$dummyBikefit->knieflexie_links ?? '120°', \$content);
                \$content = str_replace('{{bikefit.knieflexie_rechts}}', \$dummyBikefit->knieflexie_rechts ?? '120°', \$content);
                \$content = str_replace('{{bikefit.heup_endorotatie_links}}', \$dummyBikefit->heup_endorotatie_links ?? '45°', \$content);
                \$content = str_replace('{{bikefit.heup_endorotatie_rechts}}', \$dummyBikefit->heup_endorotatie_rechts ?? '45°', \$content);
                \$content = str_replace('{{bikefit.heup_exorotatie_links}}', \$dummyBikefit->heup_exorotatie_links ?? '50°', \$content);
                \$content = str_replace('{{bikefit.heup_exorotatie_rechts}}', \$dummyBikefit->heup_exorotatie_rechts ?? '50°', \$content);
                \$content = str_replace('{{bikefit.enkeldorsiflexie_links}}', \$dummyBikefit->enkeldorsiflexie_links ?? '15°', \$content);
                \$content = str_replace('{{bikefit.enkeldorsiflexie_rechts}}', \$dummyBikefit->enkeldorsiflexie_rechts ?? '15°', \$content);
                \$content = str_replace('{{bikefit.interne_opmerkingen}}', \$dummyBikefit->interne_opmerkingen ?? 'Interne notities', \$content);";
                        
                        $newContent = str_replace($targetLine, $targetLine . $newReplacements, $content);
                        
                        // Backup maken
                        $backupPath = $controllerPath . '.backup.preview-fix.' . date('Y-m-d-H-i-s');
                        File::put($backupPath, $content);
                        $this->info("📄 Backup gemaakt: {$backupPath}");
                        
                        // Schrijf nieuwe content
                        File::put($controllerPath, $newContent);
                        $this->info("✅ 25 nieuwe bikefit replacements toegevoegd aan preview() method!");
                        
                        $foundPreviewOpmerkingen = true;
                        break;
                    }
                }
            }
            
            if (!$foundPreviewOpmerkingen) {
                $this->warn('⚠️ Geen {{bikefit.opmerkingen}} gevonden in preview() method');
                $this->info('💡 Het rapport gebruikt mogelijk al de generatePagesForBikefit() method');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}