<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeSjablonenController extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:analyze-sjablonen-controller';

    /**
     * The console command description.
     */
    protected $description = 'Analyseer SjablonenController om template replacement te vinden';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Analyzing SjablonenController...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            
            // Extract all methods
            preg_match_all('/public function ([a-zA-Z_][a-zA-Z0-9_]*)\s*\([^)]*\)\s*\{/', $content, $matches);
            
            $this->info('📋 Alle public methods in SjablonenController:');
            foreach ($matches[1] as $methodName) {
                $this->line("  - {$methodName}()");
            }
            
            $this->info('');
            $this->info('🔍 Zoeken naar template replacement logica...');
            
            // Zoek naar methods die waarschijnlijk template replacement doen
            $suspiciousMethods = ['generate', 'report', 'preview', 'pdf', 'render'];
            
            foreach ($suspiciousMethods as $keyword) {
                foreach ($matches[1] as $methodName) {
                    if (stripos($methodName, $keyword) !== false) {
                        $this->info("🎯 Verdachte method gevonden: {$methodName}()");
                        $this->analyzeMethod($content, $methodName);
                    }
                }
            }
            
            // Zoek ook naar str_replace, preg_replace patterns
            $this->info('');
            $this->info('🔍 Zoeken naar replacement patterns...');
            
            if (preg_match_all('/(str_replace|preg_replace).*?\{\{.*?\}\}.*?;/s', $content, $replacementMatches)) {
                $this->info('✅ Template replacements gevonden:');
                foreach ($replacementMatches[0] as $replacement) {
                    $this->line("  " . trim($replacement));
                }
            } else {
                $this->warn('⚠️ Geen template replacements gevonden met {{}}');
            }
            
            // Zoek naar bikefit references
            $this->info('');
            $this->info('🚴‍♂️ Zoeken naar bikefit references...');
            
            if (preg_match_all('/bikefit[^;]*/i', $content, $bikefitMatches)) {
                $this->info('✅ Bikefit references gevonden:');
                foreach (array_unique($bikefitMatches[0]) as $bikefitRef) {
                    $this->line("  " . trim($bikefitRef));
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function analyzeMethod(string $content, string $methodName): void
    {
        // Extract the specific method content
        $pattern = "/public function {$methodName}\s*\([^)]*\)\s*\{((?:[^{}]|{(?:[^{}]|{[^}]*})*})*)\}/s";
        
        if (preg_match($pattern, $content, $matches)) {
            $methodContent = $matches[1];
            
            $this->line("📄 Method {$methodName}() content (eerste 300 chars):");
            $this->line("  " . substr(trim($methodContent), 0, 300) . '...');
            
            // Check for template replacement patterns
            if (strpos($methodContent, '{{') !== false || strpos($methodContent, 'replace') !== false) {
                $this->info("  ✅ Bevat mogelijk template replacement logica");
            }
            
            // Check for bikefit usage
            if (stripos($methodContent, 'bikefit') !== false) {
                $this->info("  🚴‍♂️ Bevat bikefit references");
            }
            
            $this->line('');
        }
    }
}