<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Testzadel;

class FixTestzadelUpdate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-testzadel-update';

    /**
     * The console command description.
     */
    protected $description = 'Fix testzadel update method en onderdeel_type problemen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing testzadel update problemen...');

        try {
            // 1. Fix leeg onderdeel_type in database voor bestaande records
            $this->info('🔍 Controleren bestaande testzadels met lege onderdeel_type...');
            
            $emptyTypeTestzadels = Testzadel::where(function($query) {
                $query->whereNull('onderdeel_type')
                      ->orWhere('onderdeel_type', '');
            })->get();
            
            if ($emptyTypeTestzadels->count() > 0) {
                $this->warn("Gevonden {$emptyTypeTestzadels->count()} testzadels met lege onderdeel_type");
                
                foreach ($emptyTypeTestzadels as $testzadel) {
                    // Set default naar 'testzadel' als er zadel info is, anders 'zooltjes'
                    $defaultType = 'testzadel';
                    if (empty($testzadel->zadel_merk) && empty($testzadel->zadel_model)) {
                        $defaultType = 'zooltjes';
                    }
                    
                    $testzadel->update(['onderdeel_type' => $defaultType]);
                    
                    $this->line("✅ Testzadel ID {$testzadel->id} → onderdeel_type: '{$defaultType}'");
                }
            } else {
                $this->info('✅ Geen testzadels met lege onderdeel_type gevonden');
            }

            // 2. Check en fix TestzadelsController
            $controllerPath = app_path('Http/Controllers/TestzadelsController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('❌ TestzadelsController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            $originalContent = $content;
            
            // Check of update method correct is
            $this->info('🔍 Controleren TestzadelsController update method...');
            
            // Zoek naar update method en check validation
            if (preg_match('/public function update\(Request \$request.*?\{.*?validate\(\[(.*?)\]/s', $content, $matches)) {
                $validationRules = $matches[1];
                
                if (stripos($validationRules, 'onderdeel_type') === false) {
                    $this->warn('⚠️ onderdeel_type mist in validation rules');
                    
                    // Fix validation rules
                    $newValidationRules = $validationRules;
                    if (!empty(trim($validationRules))) {
                        $newValidationRules = trim($validationRules) . ",\n            'onderdeel_type' => 'required|string|in:testzadel,zooltjes,cleats,stuurpen',";
                    } else {
                        $newValidationRules = "'onderdeel_type' => 'required|string|in:testzadel,zooltjes,cleats,stuurpen',";
                    }
                    
                    $content = str_replace($validationRules, $newValidationRules, $content);
                    $this->info('✅ onderdeel_type toegevoegd aan validation rules');
                } else {
                    $this->info('✅ onderdeel_type bestaat al in validation rules');
                }
            }
            
            // Check of er een werkelijke_retour_datum automatisch gezet wordt bij status wijziging
            if (stripos($content, 'werkelijke_retour_datum') === false) {
                $this->warn('⚠️ Geen automatische werkelijke_retour_datum logica gevonden');
                
                // Zoek naar status update logica
                if (preg_match('/(\$testzadel->update\(\$validated\);|\$testzadel->update\(\$request->all\(\)\);)/s', $content, $updateMatch)) {
                    $updateLine = $updateMatch[0];
                    
                    $newUpdateLogic = '
            // Zet werkelijke_retour_datum automatisch bij status wijziging naar teruggegeven
            if ($request->status === \'teruggegeven\' && $testzadel->status !== \'teruggegeven\') {
                $validated[\'werkelijke_retour_datum\'] = now();
            }
            
            ' . $updateLine;
                    
                    $content = str_replace($updateLine, $newUpdateLogic, $content);
                    $this->info('✅ Automatische werkelijke_retour_datum logica toegevoegd');
                }
            }
            
            // Backup en save als er wijzigingen zijn
            if ($content !== $originalContent) {
                $backupPath = $controllerPath . '.backup.' . date('Y-m-d-H-i-s');
                File::put($backupPath, $originalContent);
                $this->info("📄 Backup gemaakt: {$backupPath}");
                
                File::put($controllerPath, $content);
                $this->info('✅ TestzadelsController geüpdated');
            } else {
                $this->info('ℹ️ Geen wijzigingen nodig aan controller');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing testzadel update: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}