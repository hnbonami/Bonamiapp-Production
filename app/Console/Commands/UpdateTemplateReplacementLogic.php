<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\TemplateKey;

class UpdateTemplateReplacementLogic extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:update-template-replacement';

    /**
     * The console command description.
     */
    protected $description = 'Update template replacement logica om nieuwe bikefit keys te ondersteunen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Updating template replacement logic...');

        try {
            // Zoek naar SjabloonHelper of report generator
            $possibleFiles = [
                app_path('Helpers/SjabloonHelper.php'),
                app_path('Services/SjabloonService.php'),
                app_path('Services/TemplateService.php'),
                app_path('Http/Controllers/SjablonenController.php'),
            ];

            $foundFiles = [];
            foreach ($possibleFiles as $file) {
                if (File::exists($file)) {
                    $foundFiles[] = $file;
                    $this->info("✅ Gevonden: {$file}");
                }
            }

            if (empty($foundFiles)) {
                $this->warn('⚠️ Geen template replacement bestanden gevonden');
                $this->info('📍 Zoeken naar bestanden met template replacement logica...');
                
                // Zoek in alle PHP bestanden naar template replacement
                $searchResults = $this->searchForTemplateReplacement();
                
                if (!empty($searchResults)) {
                    foreach ($searchResults as $file => $matches) {
                        $this->info("🔍 Template replacement gevonden in: {$file}");
                        foreach ($matches as $match) {
                            $this->line("   - {$match}");
                        }
                    }
                }
            } else {
                // Analyseer elk gevonden bestand
                foreach ($foundFiles as $file) {
                    $this->analyzeTemplateFile($file);
                }
            }

            // Toon alle beschikbare template keys
            $this->info('');
            $this->info('📋 Beschikbare template keys die vervangen moeten worden:');
            $templateKeys = TemplateKey::all();
            
            $keysByCategory = $templateKeys->groupBy('category');
            foreach ($keysByCategory as $category => $keys) {
                $this->info("  {$category}:");
                foreach ($keys as $key) {
                    $this->line("    - {$key->key} ({$key->description})");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function searchForTemplateReplacement(): array
    {
        $results = [];
        $appPath = app_path();
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($appPath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                
                // Zoek naar template replacement patterns
                $patterns = [
                    '/str_replace.*\{\{/',
                    '/preg_replace.*\{\{/',
                    '/\{\{.*\}\}/',
                    '/placeholder.*replace/',
                    '/template.*replace/',
                    '/bikefit\./i'
                ];
                
                $matches = [];
                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $patternMatches)) {
                        $matches = array_merge($matches, $patternMatches[0]);
                    }
                }
                
                if (!empty($matches)) {
                    $results[$file->getPathname()] = array_unique($matches);
                }
            }
        }
        
        return $results;
    }

    private function analyzeTemplateFile(string $filePath): void
    {
        $this->info("🔍 Analyzing: {$filePath}");
        
        $content = File::get($filePath);
        $lines = explode("\n", $content);
        
        // Zoek naar template replacement logica
        foreach ($lines as $lineNumber => $line) {
            if (stripos($line, 'replace') !== false && 
                (stripos($line, '{{') !== false || stripos($line, 'template') !== false)) {
                $this->line("  Line " . ($lineNumber + 1) . ": " . trim($line));
            }
        }
    }
}