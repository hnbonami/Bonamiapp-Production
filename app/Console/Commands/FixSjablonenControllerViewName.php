<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixSjablonenControllerViewName extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-view-name';

    /**
     * The console command description.
     */
    protected $description = 'Repareer de view naam in SjablonenController edit method';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing SjablonenController view name...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            $originalContent = $content;
            
            // Backup maken
            $backupPath = $controllerPath . '.backup.view-fix.' . date('Y-m-d-H-i-s');
            File::put($backupPath, $originalContent);
            $this->info("📄 Backup gemaakt: {$backupPath}");
            
            // Find return view statements in edit method
            if (preg_match('/return view\([^;]+;/', $content, $matches)) {
                $returnStatement = $matches[0];
                $this->info('🔍 Gevonden return statement:');
                $this->line($returnStatement);
                
                // Fix verschillende mogelijke verkeerde view namen
                $fixes = [
                    'sjabloon.index' => 'sjablonen.edit',
                    'sjabloon.edit' => 'sjablonen.edit',
                    'sjablonen.index' => 'sjablonen.edit'
                ];
                
                $newContent = $content;
                foreach ($fixes as $wrong => $correct) {
                    if (strpos($returnStatement, $wrong) !== false) {
                        $newReturn = str_replace($wrong, $correct, $returnStatement);
                        $newContent = str_replace($returnStatement, $newReturn, $content);
                        
                        $this->info("✅ Fixed view name: {$wrong} → {$correct}");
                        $this->info("New return statement: {$newReturn}");
                        break;
                    }
                }
                
                if ($newContent !== $content) {
                    File::put($controllerPath, $newContent);
                    $this->info('✅ SjablonenController view name gerepareerd');
                } else {
                    $this->warn('⚠️ Geen view name reparaties nodig');
                }
                
            } else {
                $this->warn('⚠️ Return view statement niet gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing view name: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}