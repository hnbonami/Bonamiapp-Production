<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixSjablonenControllerCompactError extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-compact-error';

    /**
     * The console command description.
     */
    protected $description = 'Fix compact() error in SjablonenController edit method';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing SjablonenController compact() error...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            $originalContent = $content;
            
            // Backup maken
            $backupPath = $controllerPath . '.backup.compact-fix.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $originalContent);
            $this->info("📄 Backup gemaakt: {$backupPath}");
            
            // Find and show the return view statement
            if (preg_match('/return view\([^;]+;/', $content, $matches)) {
                $this->info('🔍 Gevonden return statement:');
                $this->line($matches[0]);
                
                // Check voor compact() errors
                if (strpos($matches[0], 'compact(') !== false) {
                    $this->info('📋 Compact statement gevonden, analyseren...');
                    
                    // Replace verkeerde variabele namen
                    $fixes = [
                        'compact(\'sjablonen\'' => 'compact(\'sjabloon\'',
                        '$sjablonen' => '$sjabloon',
                    ];
                    
                    $newContent = $content;
                    foreach ($fixes as $wrong => $correct) {
                        if (strpos($newContent, $wrong) !== false) {
                            $newContent = str_replace($wrong, $correct, $newContent);
                            $this->info("✅ Fixed: {$wrong} → {$correct}");
                        }
                    }
                    
                    // Als er geen specifieke fixes waren, probeer algemene compact reparatie
                    if ($newContent === $content) {
                        // Zoek naar de return view statement en repareer
                        $pattern = '/return view\([^;]+;/';
                        if (preg_match($pattern, $content, $returnMatch)) {
                            $returnStatement = $returnMatch[0];
                            
                            // Maak een nieuwe, correcte return statement
                            $newReturn = "return view('sjablonen.edit', compact('sjabloon', 'templateKeys'));";
                            
                            $newContent = str_replace($returnStatement, $newReturn, $content);
                            $this->info("✅ Return statement gerepareerd naar: {$newReturn}");
                        }
                    }
                    
                    if ($newContent !== $content) {
                        File::put($controllerPath, $newContent);
                        $this->info('✅ Controller gerepareerd');
                    } else {
                        $this->warn('⚠️ Geen reparaties nodig of mogelijk');
                    }
                    
                } else {
                    $this->info('ℹ️ Geen compact() statement gevonden in return');
                }
            } else {
                $this->warn('⚠️ Return view statement niet gevonden');
            }
            
            // Toon de volledige edit method voor debugging
            if (preg_match('/(public function edit\([^{]*\{[^}]+\})/s', $content, $methodMatch)) {
                $this->info('');
                $this->info('📄 Volledige edit method na reparatie:');
                $this->line($methodMatch[0]);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing compact error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}