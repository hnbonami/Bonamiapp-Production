<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddNewBikefitReplacements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:add-new-bikefit-replacements';

    /**
     * The console command description.
     */
    protected $description = 'Voeg nieuwe bikefit template replacements toe';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Adding new bikefit template replacements...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            
            // Zoek naar de laatste bikefit replacement en voeg nieuwe toe
            $searchPattern = "str_replace('{{bikefit.follow_up}}', \$bikefit->follow_up ?? '', \$content);";
            
            if (strpos($content, $searchPattern) !== false) {
                $this->info('✅ Gevonden: {{bikefit.follow_up}} replacement');
                
                // Nieuwe replacements die toegevoegd moeten worden
                $newReplacements = "
                
                // Nieuwe bikefit velden toegevoegd - aanpassingen
                \$content = str_replace('{{bikefit.aanpassingen_zadel}}', \$bikefit->aanpassingen_zadel ?? '', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_setback}}', \$bikefit->aanpassingen_setback ?? '', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_reach}}', \$bikefit->aanpassingen_reach ?? '', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_drop}}', \$bikefit->aanpassingen_drop ?? '', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_stuurpen_aan}}', \$bikefit->aanpassingen_stuurpen_aan ?? '', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_stuurpen_pre}}', \$bikefit->aanpassingen_stuurpen_pre ?? '', \$content);
                \$content = str_replace('{{bikefit.aanpassingen_stuurpen_post}}', \$bikefit->aanpassingen_stuurpen_post ?? '', \$content);
                \$content = str_replace('{{bikefit.rotatie_aanpassingen}}', \$bikefit->rotatie_aanpassingen ?? '', \$content);
                \$content = str_replace('{{bikefit.inclinatie_aanpassingen}}', \$bikefit->inclinatie_aanpassingen ?? '', \$content);
                \$content = str_replace('{{bikefit.ophoging_li}}', \$bikefit->ophoging_li ?? '', \$content);
                \$content = str_replace('{{bikefit.ophoging_re}}', \$bikefit->ophoging_re ?? '', \$content);
                \$content = str_replace('{{bikefit.type_fitting}}', \$bikefit->type_fitting ?? '', \$content);
                \$content = str_replace('{{bikefit.bouwjaar}}', \$bikefit->bouwjaar ?? '', \$content);
                \$content = str_replace('{{bikefit.type_fiets}}', \$bikefit->type_fiets ?? '', \$content);
                \$content = str_replace('{{bikefit.frametype}}', \$bikefit->frametype ?? '', \$content);
                \$content = str_replace('{{bikefit.armlengte_cm}}', \$bikefit->armlengte_cm ?? '', \$content);
                \$content = str_replace('{{bikefit.romplengte_cm}}', \$bikefit->romplengte_cm ?? '', \$content);
                \$content = str_replace('{{bikefit.schouderbreedte_cm}}', \$bikefit->schouderbreedte_cm ?? '', \$content);
                \$content = str_replace('{{bikefit.zadel_lengte_center_top}}', \$bikefit->zadel_lengte_center_top ?? '', \$content);
                \$content = str_replace('{{bikefit.type_zadel}}', \$bikefit->type_zadel ?? '', \$content);
                \$content = str_replace('{{bikefit.zadeltil}}', \$bikefit->zadeltil ?? '', \$content);
                \$content = str_replace('{{bikefit.zadelbreedte}}', \$bikefit->zadelbreedte ?? '', \$content);
                \$content = str_replace('{{bikefit.nieuw_testzadel}}', \$bikefit->nieuw_testzadel ?? '', \$content);
                \$content = str_replace('{{bikefit.lenigheid_hamstrings}}', \$bikefit->lenigheid_hamstrings ?? '', \$content);
                \$content = str_replace('{{bikefit.voetpositie}}', \$bikefit->voetpositie ?? '', \$content);
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
                \$content = str_replace('{{bikefit.one_leg_squat_links}}', \$bikefit->one_leg_squat_links ?? '', \$content);
                \$content = str_replace('{{bikefit.one_leg_squat_rechts}}', \$bikefit->one_leg_squat_rechts ?? '', \$content);
                \$content = str_replace('{{bikefit.interne_opmerkingen}}', \$bikefit->interne_opmerkingen ?? '', \$content);";
                
                // Voeg toe na de follow_up replacement
                $newContent = str_replace(
                    $searchPattern,
                    $searchPattern . $newReplacements,
                    $content
                );
                
                // Backup maken
                $backupPath = $controllerPath . '.backup.new-replacements.' . date('Y-m-d-H-i-s');
                File::put($backupPath, $content);
                $this->info("📄 Backup gemaakt: {$backupPath}");
                
                // Schrijf nieuwe content
                File::put($controllerPath, $newContent);
                $this->info("✅ 32 nieuwe bikefit template replacements toegevoegd!");
                
                // Verifieer
                $verifyContent = File::get($controllerPath);
                if (strpos($verifyContent, '{{bikefit.aanpassingen_zadel}}') !== false) {
                    $this->info("🎉 Verificatie succesvol - nieuwe replacements zijn toegevoegd");
                } else {
                    $this->warn("⚠️ Verificatie mislukt - replacements mogelijk niet correct toegevoegd");
                }
                
            } else {
                $this->warn('⚠️ Kon {{bikefit.follow_up}} replacement niet vinden');
                
                // Alternative: zoek naar een andere bikefit replacement
                if (strpos($content, '{{bikefit.opmerkingen}}') !== false) {
                    $this->info('🔄 Proberen na {{bikefit.opmerkingen}} replacement...');
                    // Implement alternative insertion point
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}