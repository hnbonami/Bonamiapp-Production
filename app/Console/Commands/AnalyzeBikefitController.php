<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeBikefitController extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:analyze-bikefit-controller';

    /**
     * The console command description.  
     */
    protected $description = 'Analyseer BikefitController om testzadel aanmaak logica te vinden';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Analyzing BikefitController voor testzadel aanmaak...');

        try {
            $controllerPath = app_path('Http/Controllers/BikefitController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('❌ BikefitController niet gevonden op: ' . $controllerPath);
                return Command::FAILURE;
            }

            $content = File::get($controllerPath);
            
            // Zoek naar testzadel gerelateerde code
            $this->info('🔍 Zoeken naar testzadel gerelateerde code...');
            
            $lines = explode("\n", $content);
            $testzadelLines = [];
            
            foreach ($lines as $lineNumber => $line) {
                if (stripos($line, 'testzadel') !== false || 
                    stripos($line, 'zadel') !== false ||
                    stripos($line, 'uitgeleend') !== false) {
                    $testzadelLines[] = [
                        'line' => $lineNumber + 1,
                        'content' => trim($line)
                    ];
                }
            }

            if (empty($testzadelLines)) {
                $this->warn('⚠️ Geen testzadel gerelateerde code gevonden in BikefitController');
            } else {
                $this->info('📝 Testzadel gerelateerde code gevonden:');
                foreach ($testzadelLines as $item) {
                    $this->line("Lijn {$item['line']}: {$item['content']}");
                }
            }

            // Zoek naar store method
            $this->info('');
            $this->info('🔍 Zoeken naar store method...');
            
            if (preg_match('/public function store\(.*?\)\s*\{(.*?)\n\s*\}/s', $content, $matches)) {
                $storeMethod = $matches[0];
                $this->info('📝 Store method gevonden. Zoeken naar testzadel logica...');
                
                if (stripos($storeMethod, 'testzadel') !== false) {
                    $this->info('✅ Testzadel logica gevonden in store method');
                    
                    // Log de hele store method voor analyse
                    $this->line('Store method (eerste 1000 chars):');
                    $this->line(substr($storeMethod, 0, 1000) . '...');
                } else {
                    $this->info('⚠️ Geen testzadel logica gevonden in store method');
                }
            }

            // Zoek naar alle methods die Testzadel::create bevatten
            $this->info('');
            $this->info('🔍 Zoeken naar Testzadel::create calls...');
            
            if (preg_match_all('/Testzadel::create\s*\(\s*\[(.*?)\]/s', $content, $matches, PREG_SET_ORDER)) {
                $this->info('✅ Testzadel::create calls gevonden:');
                foreach ($matches as $i => $match) {
                    $this->line("Create call " . ($i + 1) . ":");
                    $this->line($match[0]);
                    $this->line('---');
                }
            } else {
                $this->info('⚠️ Geen Testzadel::create calls gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error analyzing BikefitController: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}