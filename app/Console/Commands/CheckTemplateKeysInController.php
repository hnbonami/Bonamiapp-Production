<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TemplateKey;

class CheckTemplateKeysInController extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-template-keys-controller';

    /**
     * The console command description.
     */
    protected $description = 'Check hoe template keys worden opgehaald voor de edit pagina';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Controleren template keys voor edit pagina...');

        try {
            // Check alle template keys in database
            $allKeys = TemplateKey::all();
            $this->info("📊 Totaal template keys in database: {$allKeys->count()}");
            
            // Groepeer per categorie zoals controller doet
            $groupedKeys = $allKeys->groupBy('category');
            
            $this->info('📋 Template keys gegroepeerd per categorie:');
            foreach ($groupedKeys as $category => $keys) {
                $this->line("  {$category}: {$keys->count()} keys");
                
                // Toon eerste paar keys per categorie
                foreach ($keys->take(3) as $key) {
                    $this->line("    - {$key->description}: {$key->key}");
                }
                if ($keys->count() > 3) {
                    $this->line("    ... en " . ($keys->count() - 3) . " meer");
                }
            }

            // Check specifiek bikefit keys
            $this->info('');
            $this->info('🚴‍♂️ Bikefit keys details:');
            $bikefitKeys = TemplateKey::where('category', 'bikefit')->get();
            
            foreach ($bikefitKeys as $key) {
                $this->line("  ✅ {$key->description}: {$key->key}");
            }

            // Check of er keys zijn met aanpassingen
            $this->info('');
            $this->info('🔍 Zoeken naar "aanpassingen" keys:');
            $aanpassingenKeys = TemplateKey::where('key', 'LIKE', '%aanpassingen%')
                                         ->orWhere('description', 'LIKE', '%aanpassingen%')
                                         ->get();
                                         
            if ($aanpassingenKeys->count() > 0) {
                foreach ($aanpassingenKeys as $key) {
                    $this->info("  ✅ GEVONDEN: {$key->description}: {$key->key}");
                }
            } else {
                $this->warn('  ❌ Geen "aanpassingen" keys gevonden');
            }

            // Check stuurpen keys  
            $this->info('');
            $this->info('🔍 Zoeken naar "stuurpen" keys:');
            $stuurpenKeys = TemplateKey::where('key', 'LIKE', '%stuurpen%')
                                      ->orWhere('description', 'LIKE', '%stuurpen%')
                                      ->get();
                                      
            if ($stuurpenKeys->count() > 0) {
                foreach ($stuurpenKeys as $key) {
                    $this->info("  ✅ GEVONDEN: {$key->description}: {$key->key}");
                }
            } else {
                $this->warn('  ❌ Geen "stuurpen" keys gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error checking template keys: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}