<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Testzadel;
use Illuminate\Support\Facades\File;

class DebugTestzadelUpdate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:debug-testzadel-update';

    /**
     * The console command description.
     */
    protected $description = 'Debug testzadel update problemen en onderdeel_type inconsistenties';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Debugging testzadel update problemen...');

        try {
            // Controleer TestzadelsController
            $controllerPath = app_path('Http/Controllers/TestzadelsController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('❌ TestzadelsController niet gevonden op: ' . $controllerPath);
                return Command::FAILURE;
            }

            $this->info('✅ TestzadelsController gevonden');
            
            // Check testzadel data in database
            $this->info('');
            $this->info('📋 Huidige testzadel data:');
            
            $testzadels = Testzadel::with('klant')->orderBy('id', 'desc')->limit(5)->get();
            
            $tableData = [];
            foreach ($testzadels as $testzadel) {
                $tableData[] = [
                    'ID' => $testzadel->id,
                    'Klant' => $testzadel->klant ? $testzadel->klant->naam : 'Geen klant',
                    'Onderdeel Type (DB)' => $testzadel->onderdeel_type ?? 'NULL',
                    'Status (DB)' => $testzadel->status ?? 'NULL',
                    'Zadel Merk' => $testzadel->zadel_merk ?? 'NULL',
                    'Zadel Model' => $testzadel->zadel_model ?? 'NULL',
                    'Created At' => $testzadel->created_at->format('Y-m-d H:i:s'),
                    'Updated At' => $testzadel->updated_at->format('Y-m-d H:i:s')
                ];
            }
            
            $this->table([
                'ID', 'Klant', 'Onderdeel Type (DB)', 'Status (DB)', 
                'Zadel Merk', 'Zadel Model', 'Created At', 'Updated At'
            ], $tableData);

            // Analyseer TestzadelsController update method
            $content = File::get($controllerPath);
            
            $this->info('');
            $this->info('🔍 Analyseren TestzadelsController update method...');
            
            // Zoek naar update method
            if (preg_match('/public function update\(.*?\)\s*\{(.*?)\n\s*public function/s', $content, $matches)) {
                $updateMethod = $matches[0];
                $this->info('📝 Update method gevonden');
                
                // Check validation rules
                if (preg_match('/validate\(\[(.*?)\]/s', $updateMethod, $validateMatch)) {
                    $this->info('📋 Validation rules gevonden');
                    $this->line('Validation sectie (eerste 500 chars):');
                    $this->line(substr($validateMatch[0], 0, 500) . '...');
                }
                
                // Check for onderdeel_type handling
                if (stripos($updateMethod, 'onderdeel_type') !== false) {
                    $this->info('✅ onderdeel_type wordt verwerkt in update method');
                } else {
                    $this->warn('⚠️ onderdeel_type wordt NIET verwerkt in update method');
                }
                
            } else {
                $this->warn('⚠️ Update method niet gevonden in TestzadelsController');
            }

            // Check voor specifieke testzadel die probleem heeft
            $this->info('');
            $this->info('🔍 Specifieke testzadel details:');
            
            $problematicTestzadel = Testzadel::whereNull('onderdeel_type')
                                           ->orWhere('onderdeel_type', '')
                                           ->first();
            
            if ($problematicTestzadel) {
                $this->warn('⚠️ Testzadel met lege onderdeel_type gevonden:');
                $this->line("ID: {$problematicTestzadel->id}");
                $this->line("Status: {$problematicTestzadel->status}");
                $this->line("Onderdeel Type: '{$problematicTestzadel->onderdeel_type}'");
                $this->line("Created: {$problematicTestzadel->created_at}");
                $this->line("Updated: {$problematicTestzadel->updated_at}");
            } else {
                $this->info('✅ Geen testzadels met lege onderdeel_type gevonden');
            }

            // Check voor unieke onderdeel_type waarden
            $this->info('');
            $this->info('📊 Unieke onderdeel_type waarden in database:');
            
            $uniqueTypes = Testzadel::select('onderdeel_type')
                                   ->distinct()
                                   ->pluck('onderdeel_type')
                                   ->filter()
                                   ->toArray();
                                   
            foreach ($uniqueTypes as $type) {
                $count = Testzadel::where('onderdeel_type', $type)->count();
                $this->line("'{$type}' => {$count} testzadels");
            }
            
            // Check voor NULL/empty waarden
            $nullCount = Testzadel::whereNull('onderdeel_type')->count();
            $emptyCount = Testzadel::where('onderdeel_type', '')->count();
            
            if ($nullCount > 0) {
                $this->warn("NULL onderdeel_type => {$nullCount} testzadels");
            }
            if ($emptyCount > 0) {
                $this->warn("Empty onderdeel_type => {$emptyCount} testzadels");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error debugging testzadel update: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}