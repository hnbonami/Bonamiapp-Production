<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindAllTemplateReplacements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:find-all-replacements';

    /**
     * The console command description.
     */
    protected $description = 'Vind ALLE template replacement methods en identificeer welke wordt gebruikt';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Zoeken naar ALLE template replacement methods...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            $content = File::get($controllerPath);
            
            // Zoek naar alle methods die {{bikefit.opmerkingen}} bevatten
            $lines = explode("\n", $content);
            $foundMethods = [];
            
            for ($i = 0; $i < count($lines); $i++) {
                if (strpos($lines[$i], '{{bikefit.opmerkingen}}') !== false) {
                    // Zoek de method naam door terug te gaan
                    $methodName = 'Unknown';
                    for ($j = $i; $j >= 0; $j--) {
                        if (preg_match('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $lines[$j], $matches)) {
                            $methodName = $matches[1];
                            break;
                        }
                    }
                    
                    $foundMethods[] = [
                        'method' => $methodName,
                        'line' => $i + 1,
                        'context' => trim($lines[$i])
                    ];
                }
            }
            
            $this->info("📋 Gevonden " . count($foundMethods) . " methods met {{bikefit.opmerkingen}} replacement:");
            
            foreach ($foundMethods as $index => $method) {
                $this->info("");
                $this->info("Method " . ($index + 1) . ": {$method['method']}() (lijn {$method['line']})");
                $this->line("  {$method['context']}");
                
                // Toon context rond deze method
                $lineNum = $method['line'] - 1;
                $this->line("  Context:");
                for ($k = max(0, $lineNum - 2); $k <= min(count($lines) - 1, $lineNum + 2); $k++) {
                    $prefix = $k == $lineNum ? ">>> " : "    ";
                    $this->line($prefix . "Lijn " . ($k + 1) . ": " . trim($lines[$k]));
                }
            }
            
            $this->info("");
            $this->info("🎯 VOLGENDE STAPPEN:");
            $this->info("1. Bekijk welke method wordt gebruikt voor rapport generatie");
            $this->info("2. Vaak is het generatePagesForBikefit() of generateReport()");
            $this->info("3. Voeg de nieuwe replacements toe aan DE JUISTE method");
            
            // Zoek ook naar preview/generate methods
            $this->info("");
            $this->info("🔍 Andere verdachte methods die rapport genereren:");
            
            $suspiciousMethods = ['generatePagesForBikefit', 'generateReport', 'preview', 'generatePagesForPreview'];
            
            foreach ($suspiciousMethods as $methodName) {
                if (strpos($content, "function {$methodName}") !== false) {
                    $this->line("  ✅ Gevonden: {$methodName}()");
                } else {
                    $this->line("  ❌ Niet gevonden: {$methodName}()");
                }
            }
            
            // Laat gebruiker kiezen welke method om aan te passen
            $this->info("");
            $this->info("💡 OPLOSSING:");
            $this->info("Voer dit commando uit om een specifieke method aan te passen:");
            $this->info("php artisan bonami:add-to-specific-method [METHOD_NAME]");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}