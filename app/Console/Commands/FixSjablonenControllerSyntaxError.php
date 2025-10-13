<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixSjablonenControllerSyntaxError extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-controller-syntax';

    /**
     * The console command description.
     */
    protected $description = 'Fix syntax error in SjablonenController door hardcoded restanten te verwijderen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing SjablonenController syntax error...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            $originalContent = $content;
            
            // Backup maken
            $backupPath = $controllerPath . '.backup.syntax-fix.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $originalContent);
            $this->info("📄 Backup gemaakt: {$backupPath}");
            
            // Find the edit method and repair it
            if (preg_match('/(public function edit\(.*?\)\s*\{.*?templateKeys = \\\\App\\\\Models\\\\TemplateKey::all\(\)->groupBy\(\'category\'\);)(.*?)(?=return view|$)/s', $content, $matches)) {
                
                $beforeTemplate = $matches[1];
                $afterTemplate = $matches[2] ?? '';
                
                $this->info('🔍 Gevonden problematische sectie na templateKeys assignment');
                $this->line('Lengte restant: ' . strlen($afterTemplate) . ' karakters');
                
                // Zoek naar het einde van de method (return view statement)
                if (preg_match('/(return view\(.*?\);)/s', $content, $returnMatches)) {
                    $returnStatement = $returnMatches[1];
                    
                    // Rebuild the method met alleen de correcte delen
                    $cleanEditMethod = $beforeTemplate . "\n\n        " . $returnStatement;
                    
                    // Replace in content
                    $pattern = '/public function edit\(.*?\)\s*\{.*?return view\(.*?\);/s';
                    $content = preg_replace($pattern, $cleanEditMethod, $content);
                    
                    $this->info('✅ Edit method gerepareerd');
                    
                } else {
                    $this->warn('⚠️ Return statement niet gevonden');
                }
                
            } else {
                $this->warn('⚠️ Edit method pattern niet gevonden');
            }
            
            // Alternative approach: Remove lines that start with (object)[ after templateKeys assignment
            $lines = explode("\n", $content);
            $cleanedLines = [];
            $inEditMethod = false;
            $foundTemplateKeys = false;
            
            foreach ($lines as $line) {
                if (strpos($line, 'public function edit(') !== false) {
                    $inEditMethod = true;
                }
                
                if ($inEditMethod && strpos($line, 'templateKeys = \App\Models\TemplateKey::all()->groupBy') !== false) {
                    $foundTemplateKeys = true;
                    $cleanedLines[] = $line;
                    continue;
                }
                
                if ($foundTemplateKeys && $inEditMethod) {
                    // Skip lines that are remnants of the old hardcoded array
                    if (preg_match('/^\s*\(object\)\[/', trim($line)) || 
                        preg_match('/^\s*\]/', trim($line)) ||
                        preg_match('/^\s*\),/', trim($line))) {
                        continue; // Skip this line
                    }
                    
                    // If we hit return view, we're done cleaning
                    if (strpos($line, 'return view(') !== false) {
                        $foundTemplateKeys = false;
                        $inEditMethod = false;
                    }
                }
                
                if (strpos($line, 'public function') !== false && $inEditMethod && strpos($line, 'public function edit(') === false) {
                    $inEditMethod = false;
                    $foundTemplateKeys = false;
                }
                
                $cleanedLines[] = $line;
            }
            
            $content = implode("\n", $cleanedLines);
            
            // Write the repaired content
            File::put($controllerPath, $content);
            $this->info('✅ SjablonenController syntax gerepareerd');
            
            // Verify the syntax
            $output = shell_exec("php -l {$controllerPath} 2>&1");
            if (strpos($output, 'No syntax errors') !== false) {
                $this->info('🎉 Syntax check succesvol - geen errors');
            } else {
                $this->error('❌ Er zijn nog steeds syntax errors:');
                $this->line($output);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing syntax: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}