<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindSjablonenControllerTemplateKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:find-sjablonen-controller-keys';

    /**
     * The console command description.
     */
    protected $description = 'Zoek hoe SjablonenController template keys ophaalt';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Zoeken naar SjablonenController template keys implementatie...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            
            // Zoek naar edit method
            if (preg_match('/public function edit\(.*?\)\s*\{(.*?)(?=public function|\}$)/s', $content, $matches)) {
                $this->info('📝 Edit method gevonden:');
                $editMethod = $matches[0];
                
                // Kijk naar templateKeys implementatie
                if (stripos($editMethod, 'templateKeys') !== false) {
                    $this->info('✅ templateKeys wordt gebruikt in edit method');
                    
                    // Extract de templateKeys sectie
                    if (preg_match('/templateKeys.*?=.*?(?=;|\n)/s', $editMethod, $templateMatch)) {
                        $this->line('📋 TemplateKeys implementatie:');
                        $this->line($templateMatch[0]);
                    }
                } else {
                    $this->warn('⚠️ templateKeys wordt NIET gebruikt in edit method');
                }
                
                // Toon de hele edit method (verkort)
                $this->info('');
                $this->info('📄 Edit method (eerste 1000 chars):');
                $this->line(substr($editMethod, 0, 1000) . '...');
                
            } else {
                $this->warn('Edit method niet gevonden');
            }
            
            // Zoek ook naar andere methods die templateKeys gebruiken
            $this->info('');
            $this->info('🔍 Andere methods die templateKeys gebruiken:');
            
            if (preg_match_all('/templateKeys.*?=.*?(?=;|\n)/s', $content, $allMatches)) {
                foreach ($allMatches[0] as $match) {
                    $this->line("  - {$match}");
                }
            } else {
                $this->warn('Geen templateKeys implementaties gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}