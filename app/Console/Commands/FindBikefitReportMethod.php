<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class FindBikefitReportMethod extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:find-bikefit-report';

    /**
     * The console command description.
     */
    protected $description = 'Vind de juiste controller voor bikefit rapport URL';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Zoeken naar bikefit rapport method...');

        try {
            // Zoek naar routes die overeenkomen met het patroon
            $this->info('📋 Zoeken naar routes met "sjabloon-rapport" of vergelijkbaar...');
            
            $routes = Route::getRoutes();
            $found = false;
            
            foreach ($routes as $route) {
                $uri = $route->uri();
                if (strpos($uri, 'sjabloon-rapport') !== false || 
                    strpos($uri, 'bikefit') !== false && strpos($uri, 'rapport') !== false) {
                    
                    $this->info("✅ Gevonden route: {$uri}");
                    $this->line("   Controller: " . $route->getActionName());
                    $found = true;
                }
            }
            
            if (!$found) {
                $this->info('🔍 Geen specifieke routes gevonden, zoeken in BikefitController...');
            }
            
            // Zoek in BikefitController
            $bikefitControllerPath = app_path('Http/Controllers/BikefitController.php');
            
            if (File::exists($bikefitControllerPath)) {
                $this->info('📄 BikefitController gevonden, zoeken naar rapport methods...');
                
                $content = File::get($bikefitControllerPath);
                
                // Zoek naar methods die lijken op rapport generatie
                $reportMethods = [];
                if (preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\([^)]*\)[^{]*\{/', $content, $matches)) {
                    foreach ($matches[1] as $methodName) {
                        if (stripos($methodName, 'rapport') !== false || 
                            stripos($methodName, 'report') !== false || 
                            stripos($methodName, 'sjabloon') !== false ||
                            stripos($methodName, 'generate') !== false) {
                            $reportMethods[] = $methodName;
                        }
                    }
                }
                
                $this->info('🎯 Gevonden rapport-gerelateerde methods in BikefitController:');
                foreach ($reportMethods as $method) {
                    $this->line("  - {$method}()");
                }
                
                // Zoek naar template replacement in BikefitController
                if (strpos($content, '{{bikefit.') !== false) {
                    $this->info('');
                    $this->info('✅ BikefitController bevat template replacement!');
                    
                    // Zoek specifiek naar bikefit.opmerkingen
                    if (strpos($content, '{{bikefit.opmerkingen}}') !== false) {
                        $this->info('🎯 Gevonden {{bikefit.opmerkingen}} replacement in BikefitController');
                        
                        // Toon context
                        $lines = explode("\n", $content);
                        foreach ($lines as $lineNum => $line) {
                            if (strpos($line, '{{bikefit.opmerkingen}}') !== false) {
                                $this->info("📍 Gevonden op lijn " . ($lineNum + 1) . ":");
                                for ($i = max(0, $lineNum - 2); $i <= min(count($lines) - 1, $lineNum + 5); $i++) {
                                    $prefix = $i == $lineNum ? ">>> " : "    ";
                                    $this->line($prefix . "Lijn " . ($i + 1) . ": " . trim($lines[$i]));
                                }
                                break;
                            }
                        }
                        
                        $this->info('');
                        $this->info('🔧 OPLOSSING: Voeg nieuwe replacements toe aan BikefitController');
                        $this->info('Voer uit: php artisan bonami:fix-bikefit-controller');
                        
                    } else {
                        $this->warn('⚠️ Geen {{bikefit.opmerkingen}} gevonden in BikefitController');
                    }
                } else {
                    $this->warn('⚠️ Geen template replacement gevonden in BikefitController');
                }
                
            } else {
                $this->error('❌ BikefitController niet gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}