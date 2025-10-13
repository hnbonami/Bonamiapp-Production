<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixSjablonenControllerTemplateKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-sjablonen-controller';

    /**
     * The console command description.
     */
    protected $description = 'Fix SjablonenController om database template keys te gebruiken';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing SjablonenController template keys...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            $originalContent = $content;
            
            // Backup maken
            $backupPath = $controllerPath . '.backup.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $originalContent);
            $this->info("📄 Backup gemaakt: {$backupPath}");
            
            // Replace de hardcoded templateKeys array met database call
            $oldPattern = '/templateKeys = collect\(\[(.*?)\]\);/s';
            $newReplacement = 'templateKeys = \App\Models\TemplateKey::all()->groupBy(\'category\');';
            
            if (preg_match($oldPattern, $content)) {
                $content = preg_replace($oldPattern, $newReplacement, $content);
                $this->info('✅ Hardcoded templateKeys array vervangen door database call');
                
                // Schrijf de nieuwe content
                File::put($controllerPath, $content);
                $this->info('✅ SjablonenController geüpdatet');
                
                // Verifieer dat het gewerkt heeft
                $newContent = File::get($controllerPath);
                if (strpos($newContent, '\App\Models\TemplateKey::all()->groupBy') !== false) {
                    $this->info('🎉 Verificatie succesvol - database call aanwezig');
                } else {
                    $this->error('❌ Verificatie gefaald - database call niet gevonden');
                }
                
            } else {
                $this->warn('⚠️ Hardcoded templateKeys array niet gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing controller: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}