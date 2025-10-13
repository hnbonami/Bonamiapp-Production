<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixTestzadelStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-testzadel-status';

    /**
     * The console command description.
     */
    protected $description = 'Fix testzadel status van nieuw naar uitgeleend in BikefitController';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing testzadel status in BikefitController...');

        try {
            $controllerPath = app_path('Http/Controllers/BikefitController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('❌ BikefitController niet gevonden');
                return Command::FAILURE;
            }

            $content = File::get($controllerPath);
            $originalContent = $content;

            // Fix 1: Vervang 'status' => 'nieuw' naar 'status' => 'uitgeleend' bij testzadel creation
            $content = preg_replace(
                "/'status'\s*=>\s*'nieuw'/",
                "'status' => 'uitgeleend'",
                $content
            );

            // Fix 2: Als er geen status wordt gezet, voeg het toe
            $content = preg_replace(
                "/(Testzadel::create\s*\(\s*\[.*?)'klant_id'/",
                "$1'status' => 'uitgeleend',\n                'klant_id'",
                $content
            );

            // Fix 3: Zoek naar testzadel creation patterns en zorg dat status uitgeleend is
            if (preg_match_all('/(Testzadel::create\s*\(\s*\[)(.*?)(\]\s*\))/s', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $fullMatch = $match[0];
                    $arrayContent = $match[2];
                    
                    // Als er geen status in staat, voeg het toe
                    if (stripos($arrayContent, "'status'") === false && stripos($arrayContent, '"status"') === false) {
                        $newArrayContent = "'status' => 'uitgeleend',\n                " . ltrim($arrayContent);
                        $newFullMatch = $match[1] . $newArrayContent . $match[3];
                        $content = str_replace($fullMatch, $newFullMatch, $content);
                        
                        $this->info('✅ Added status uitgeleend to testzadel creation');
                    }
                }
            }

            // Backup maken
            $backupPath = $controllerPath . '.backup.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $originalContent);
            $this->info("📄 Backup gemaakt: {$backupPath}");

            // Schrijf de gefixte content terug
            if ($content !== $originalContent) {
                File::put($controllerPath, $content);
                $this->info('✅ BikefitController geüpdated met correcte testzadel status');
                
                // Toon de wijzigingen
                $this->info('🔍 Wijzigingen gemaakt:');
                $this->line('- Alle testzadel creaties zetten nu status naar "uitgeleend"');
                
            } else {
                $this->info('ℹ️ Geen wijzigingen nodig - status al correct');
            }

            // Nu ook de bestaande testzadel met ID 3 fixen
            $this->info('');
            $this->info('🔧 Fixing bestaande testzadel ID 3...');
            
            $testzadel = \App\Models\Testzadel::find(3);
            if ($testzadel && $testzadel->status === 'nieuw') {
                $testzadel->status = 'uitgeleend';
                $testzadel->save();
                
                $this->info('✅ Testzadel ID 3 status veranderd van "nieuw" naar "uitgeleend"');
            } else if ($testzadel) {
                $this->info('ℹ️ Testzadel ID 3 heeft al de correcte status: ' . $testzadel->status);
            } else {
                $this->warn('⚠️ Testzadel ID 3 niet gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing testzadel status: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}