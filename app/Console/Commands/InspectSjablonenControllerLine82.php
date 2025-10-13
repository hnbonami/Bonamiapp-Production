<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InspectSjablonenControllerLine82 extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:inspect-line-82';

    /**
     * The console command description.
     */
    protected $description = 'Inspecteer en repareer lijn 82 van SjablonenController';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Inspecting SjablonenController lijn 82...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $lines = file($controllerPath);
            
            $this->info('📄 Context rond lijn 82:');
            for ($i = 78; $i <= 85; $i++) {
                if (isset($lines[$i - 1])) {
                    $line = rtrim($lines[$i - 1]);
                    $marker = $i == 82 ? ' ← ERROR LIJN' : '';
                    $this->line("Lijn {$i}: {$line}{$marker}");
                }
            }
            
            // Toon de problematische lijn
            if (isset($lines[81])) { // Array is 0-indexed
                $problematicLine = trim($lines[81]);
                $this->info("\n🚨 Problematische lijn 82:");
                $this->line("'{$problematicLine}'");
                
                // Fix the line
                if (strpos($problematicLine, 'sjablonen') !== false) {
                    $fixedLine = str_replace('sjablonen', 'sjabloon', $problematicLine);
                    $this->info("\n✅ Gerepareerde lijn:");
                    $this->line("'{$fixedLine}'");
                    
                    // Replace in file
                    $lines[81] = $fixedLine . "\n";
                    $newContent = implode('', $lines);
                    
                    // Backup
                    $backupPath = $controllerPath . '.backup.line82-fix.' . date('Y-m-d-H-i-s');
                    File::put($backupPath, File::get($controllerPath));
                    $this->info("📄 Backup gemaakt: {$backupPath}");
                    
                    // Write fix
                    File::put($controllerPath, $newContent);
                    $this->info('✅ Lijn 82 gerepareerd!');
                    
                } else {
                    $this->warn('⚠️ Geen "sjablonen" gevonden in lijn 82');
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}