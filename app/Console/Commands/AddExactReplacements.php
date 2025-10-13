<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddExactReplacements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:add-exact-replacements';

    /**
     * The console command description.
     */
    protected $description = 'Voeg exact de template keys uit de screenshot toe';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Adding exact template replacements from screenshot...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            $content = File::get($controllerPath);
            
            // De exacte keys die je ziet in het rapport
            $exactReplacements = [
                // Stuurpen keys
                'bikefit.stuurpen_voor' => 'stuur_trapas_hoek', // beste gok voor mapping
                'bikefit.stuurpen_na' => 'stuur_trapas_afstand', // beste gok voor mapping
                
                // Zadel keys  
                'bikefit.inclinatie_zadel' => 'zadeltil', // beste gok voor mapping
                
                // Schoenplaatjes keys
                'bikefit.rotatie_schoenplaatjes' => 'rotatie_aanpassingen',
                'inclinatie.rotatie_schoenplaatjes' => 'inclinatie_aanpassingen',
            ];
            
            $this->info('🎯 Voeg deze exacte replacements toe:');
            
            $replacementCode = "
                
                // EXACTE TEMPLATE KEYS uit screenshot - direct toegevoegd";
                
            foreach ($exactReplacements as $templateKey => $databaseField) {
                $this->line("  {{{{{$templateKey}}}}} → \$bikefit->{$databaseField}");
                $replacementCode .= "
                \$content = str_replace('{{{$templateKey}}}', \$bikefit->{$databaseField} ?? '', \$content);";
            }
            
            // Zoek de laatste bikefit replacement
            $lines = explode("\n", $content);
            $lastReplacementLine = -1;
            
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                if (strpos($lines[$i], "str_replace('{{bikefit.") !== false) {
                    $lastReplacementLine = $i;
                    break;
                }
            }
            
            if ($lastReplacementLine === -1) {
                $this->error('Kon geen bestaande replacements vinden');
                return Command::FAILURE;
            }
            
            // Voeg de nieuwe replacements toe
            $lines[$lastReplacementLine] = $lines[$lastReplacementLine] . $replacementCode;
            $newContent = implode("\n", $lines);
            
            // Backup maken
            $backupPath = $controllerPath . '.backup.exact-fix.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $content);
            $this->info("📄 Backup gemaakt: " . basename($backupPath));
            
            // Schrijf nieuwe content
            File::put($controllerPath, $newContent);
            
            $this->info("✅ " . count($exactReplacements) . " exacte replacements toegevoegd!");
            $this->info('');
            $this->info('🎯 TEST NU HET RAPPORT:');
            $this->info('http://127.0.0.1:8000/bikefit/29/16/sjabloon-rapport');
            $this->info('');
            $this->info('💡 Template key mapping:');
            $this->info('- {{bikefit.stuurpen_voor}} → stuur_trapas_hoek');
            $this->info('- {{bikefit.stuurpen_na}} → stuur_trapas_afstand'); 
            $this->info('- {{bikefit.inclinatie_zadel}} → zadeltil');
            $this->info('- {{bikefit.rotatie_schoenplaatjes}} → rotatie_aanpassingen');
            $this->info('- {{inclinatie.rotatie_schoenplaatjes}} → inclinatie_aanpassingen');
            $this->info('');
            $this->info('🚀 Als deze mapping niet klopt, kunnen we ze aanpassen!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}