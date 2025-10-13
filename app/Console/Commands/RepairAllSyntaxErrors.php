<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RepairAllSyntaxErrors extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:repair-all-syntax';

    /**
     * The console command description.
     */
    protected $description = 'Repareer alle syntax errors en voeg nieuwe template keys correct toe';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Repairing all syntax errors and adding template keys...');

        try {
            // Repareer BikefitController
            $this->repairBikefitController();
            
            // Repareer SjablonenController  
            $this->repairSjablonenController();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function repairBikefitController(): void
    {
        $controllerPath = app_path('Http/Controllers/BikefitController.php');
        
        // Zoek naar de nieuwste backup
        $backupFiles = glob($controllerPath . '.backup.bikefit-fix.*');
        
        if (!empty($backupFiles)) {
            rsort($backupFiles);
            $latestBackup = $backupFiles[0];
            
            $this->info("📄 Herstellen BikefitController van backup: " . basename($latestBackup));
            
            $backupContent = File::get($latestBackup);
            File::put($controllerPath, $backupContent);
            
            $this->info('✅ BikefitController hersteld');
        } else {
            $this->warn('⚠️ Geen backup gevonden voor BikefitController');
        }
    }

    private function repairSjablonenController(): void
    {
        $controllerPath = app_path('Http/Controllers/SjablonenController.php');
        
        // Zoek naar de nieuwste backup
        $backupFiles = glob($controllerPath . '.backup.working-fix.*');
        
        if (!empty($backupFiles)) {
            rsort($backupFiles);
            $latestBackup = $backupFiles[0];
            
            $this->info("📄 Herstellen SjablonenController van backup: " . basename($latestBackup));
            
            $backupContent = File::get($latestBackup);
            File::put($controllerPath, $backupContent);
            
            $this->info('✅ SjablonenController hersteld');
            
            // Nu voeg ik de ONTBREKENDE template keys toe (die nog niet in de bestaande lijst staan)
            $this->addMissingTemplateKeys($controllerPath);
            
        } else {
            $this->warn('⚠️ Geen backup gevonden voor SjablonenController');
        }
    }

    private function addMissingTemplateKeys(string $controllerPath): void
    {
        $content = File::get($controllerPath);
        
        // Zoek naar de laatste nieuwe bikefit replacement die we al hebben toegevoegd
        if (strpos($content, "str_replace('{{bikefit.interne_opmerkingen}}'") !== false) {
            $this->info('✅ Nieuwe template keys zijn al toegevoegd aan SjablonenController');
            return;
        }
        
        // Zoek naar de laatste bikefit replacement in generatePagesForBikefit
        $lines = explode("\n", $content);
        $lastReplacementLine = -1;
        
        for ($i = 0; $i < count($lines); $i++) {
            if (strpos($lines[$i], "str_replace('{{bikefit.") !== false) {
                $lastReplacementLine = $i;
            }
        }
        
        if ($lastReplacementLine === -1) {
            $this->warn('⚠️ Kon geen bestaande bikefit replacements vinden');
            return;
        }
        
        $this->info("📍 Gevonden laatste replacement op lijn " . ($lastReplacementLine + 1));
        
        // Voeg ALLEEN de ontbrekende nieuwe template keys toe
        $newKeys = "
                // ONTBREKENDE NIEUWE BIKEFIT TEMPLATE KEYS - toegevoegd door repair-all-syntax
                \$content = str_replace('{{bikefit.type_fitting}}', \$bikefit->type_fitting ?? '', \$content);
                \$content = str_replace('{{bikefit.type_fiets}}', \$bikefit->type_fiets ?? '', \$content);
                \$content = str_replace('{{bikefit.frametype}}', \$bikefit->frametype ?? '', \$content);
                \$content = str_replace('{{bikefit.type_zadel}}', \$bikefit->type_zadel ?? '', \$content);
                \$content = str_replace('{{bikefit.nieuw_testzadel}}', \$bikefit->nieuw_testzadel ?? '', \$content);
                \$content = str_replace('{{bikefit.lenigheid_hamstrings}}', \$bikefit->lenigheid_hamstrings ?? '', \$content);
                \$content = str_replace('{{bikefit.voetpositie}}', \$bikefit->voetpositie ?? '', \$content);
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
                \$content = str_replace('{{bikefit.enkeldorsiflexie_rechts}}', \$bikefit->enkeldorsiflexie_rechts ?? '', \$content);";
        
        // Voeg de nieuwe keys toe na de laatste replacement
        $lines[$lastReplacementLine] = $lines[$lastReplacementLine] . $newKeys;
        
        $newContent = implode("\n", $lines);
        File::put($controllerPath, $newContent);
        
        $this->info("✅ Ontbrekende template keys toegevoegd aan SjablonenController");
        $this->info('');
        $this->info('🎯 NU TESTEN:');
        $this->info('http://127.0.0.1:8000/bikefit/29/16/sjabloon-rapport');
        $this->info('');
        $this->info('💡 Als je nog steeds rauwe template keys ziet, voer dan uit:');
        $this->info('php artisan bonami:verify-template-keys');
    }
}