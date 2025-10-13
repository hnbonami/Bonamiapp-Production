<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddMissingFromBladeFile extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:add-from-blade-file';

    /**
     * The console command description.
     */
    protected $description = 'Analyseer edit_fixed.blade.php en voeg ontbrekende replacements toe';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Analyseren van edit_fixed.blade.php template...');

        try {
            $templatePath = resource_path('views/sjablonen/edit_fixed.blade.php');
            
            if (!File::exists($templatePath)) {
                $this->error('Template bestand niet gevonden: ' . $templatePath);
                return Command::FAILURE;
            }
            
            $content = File::get($templatePath);
            $this->info("📄 Template bestand gelezen (" . strlen($content) . " characters)");
            
            // Zoek alle template keys
            preg_match_all('/\{\{[^}]+\}\}/', $content, $matches);
            
            if (empty($matches[0])) {
                $this->warn('Geen template keys gevonden in bestand');
                return Command::SUCCESS;
            }
            
            $allKeys = array_unique($matches[0]);
            $bikefitKeys = array_filter($allKeys, function($key) {
                return strpos($key, 'bikefit.') !== false || strpos($key, 'inclinatie.') !== false;
            });
            
            $this->info('🎯 Gevonden bikefit template keys (' . count($bikefitKeys) . '):');
            sort($bikefitKeys);
            
            foreach ($bikefitKeys as $key) {
                $this->line("  {$key}");
            }
            
            // Bepaal welke keys ontbreken in onze replacements
            $existingReplacements = [
                '{{bikefit.datum}}', '{{bikefit.testtype}}', '{{bikefit.lengte_cm}}', 
                '{{bikefit.binnenbeenlengte_cm}}', '{{bikefit.opmerkingen}}', 
                '{{bikefit.zadel_trapas_hoek}}', '{{bikefit.zadel_trapas_afstand}}',
                '{{bikefit.rotatie_aanpassingen}}', '{{bikefit.inclinatie_aanpassingen}}',
                '{{bikefit.type_fitting}}', '{{bikefit.type_fiets}}', '{{bikefit.frametype}}'
            ];
            
            $missingKeys = array_diff($bikefitKeys, $existingReplacements);
            
            if (empty($missingKeys)) {
                $this->info('✅ Alle template keys hebben al een replacement!');
                return Command::SUCCESS;
            }
            
            $this->info('');
            $this->error('❌ Ontbrekende replacements (' . count($missingKeys) . '):');
            foreach ($missingKeys as $key) {
                $this->line("  {$key}");
            }
            
            // Genereer replacement code voor ontbrekende keys
            $this->info('');
            $this->info('🔧 Voeg deze replacements toe aan SjablonenController...');
            
            $replacementCode = "
                // ONTBREKENDE TEMPLATE KEYS uit edit_fixed.blade.php - automatisch gegenereerd";
                
            foreach ($missingKeys as $key) {
                $fieldName = str_replace(['{{bikefit.', '{{inclinatie.', '}}'], '', $key);
                
                // Bepaal de juiste variabele gebaseerd op het prefix
                if (strpos($key, '{{inclinatie.') !== false) {
                    $variable = "\$bikefit->{$fieldName}";
                } else {
                    $variable = "\$bikefit->{$fieldName}";
                }
                
                $replacementCode .= "
                \$content = str_replace('{$key}', {$variable} ?? '', \$content);";
            }
            
            // Voeg replacements toe aan SjablonenController
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            $controllerContent = File::get($controllerPath);
            
            // Zoek de laatste bikefit replacement
            $lines = explode("\n", $controllerContent);
            $lastReplacementLine = -1;
            
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                if (strpos($lines[$i], "str_replace('{{bikefit.") !== false) {
                    $lastReplacementLine = $i;
                    break;
                }
            }
            
            if ($lastReplacementLine === -1) {
                $this->error('Kon geen bestaande replacements vinden in SjablonenController');
                return Command::FAILURE;
            }
            
            // Voeg de nieuwe replacements toe
            $lines[$lastReplacementLine] = $lines[$lastReplacementLine] . $replacementCode;
            $newControllerContent = implode("\n", $lines);
            
            // Backup maken
            $backupPath = $controllerPath . '.backup.blade-fix.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $controllerContent);
            $this->info("📄 Backup gemaakt: " . basename($backupPath));
            
            // Schrijf nieuwe content
            File::put($controllerPath, $newControllerContent);
            
            $this->info("✅ " . count($missingKeys) . " ontbrekende replacements toegevoegd aan SjablonenController!");
            $this->info('');
            $this->info('🎯 TEST NU HET RAPPORT:');
            $this->info('http://127.0.0.1:8000/bikefit/29/16/sjabloon-rapport');
            $this->info('');
            $this->info('🎉 ALLE template keys uit het Blade bestand zouden nu moeten werken!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}