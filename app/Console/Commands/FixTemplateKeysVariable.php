<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixTemplateKeysVariable extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-templatekeys-variable';

    /**
     * The console command description.
     */
    protected $description = 'Fix templateKeys variabele probleem in SjablonenController';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing templateKeys variable issue...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            $lines = explode("\n", $content);
            
            // Zoek naar de edit method en return statement
            $this->info('🔍 Zoeken naar edit method return statement...');
            
            for ($i = 0; $i < count($lines); $i++) {
                if (strpos($lines[$i], "return view('sjablonen.edit'") !== false) {
                    $this->info("Gevonden return statement op lijn " . ($i + 1) . ":");
                    $this->line($lines[$i]);
                    
                    // Check of compact correct is
                    if (strpos($lines[$i], 'compact(') !== false) {
                        // Replace compact with explicit array
                        $newLine = "        return view('sjablonen.edit', ['sjabloon' => \$sjabloon, 'templateKeys' => \$templateKeys]);";
                        
                        // Backup
                        $backupPath = $controllerPath . '.backup.templatekeys-fix.' . date('Y-m-d-H-i-s');
                        File::put($backupPath, $content);
                        $this->info("📄 Backup gemaakt: {$backupPath}");
                        
                        // Replace the line
                        $lines[$i] = $newLine;
                        $newContent = implode("\n", $lines);
                        
                        File::put($controllerPath, $newContent);
                        
                        $this->info('✅ Return statement gerepareerd:');
                        $this->line($newLine);
                        
                        return Command::SUCCESS;
                    }
                }
            }
            
            $this->warn('⚠️ Return statement niet gevonden');
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}