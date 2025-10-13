<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Testzadel;

class FixNullOnderdeelType extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-null-onderdeel-type';

    /**
     * The console command description.
     */
    protected $description = 'Fix testzadels met NULL onderdeel_type';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing testzadels met NULL onderdeel_type...');

        try {
            // Find testzadels with NULL or empty onderdeel_type
            $nullTypeTestzadels = Testzadel::where(function($query) {
                $query->whereNull('onderdeel_type')
                      ->orWhere('onderdeel_type', '');
            })->get();

            if ($nullTypeTestzadels->count() === 0) {
                $this->info('✅ Geen testzadels met NULL onderdeel_type gevonden');
                return Command::SUCCESS;
            }

            $this->warn("Gevonden {$nullTypeTestzadels->count()} testzadels met lege onderdeel_type:");

            foreach ($nullTypeTestzadels as $testzadel) {
                // Intelligente bepaling van onderdeel_type op basis van beschikbare data
                $defaultType = 'testzadel'; // Standaard waarde
                
                // Als er zadel informatie is, is het waarschijnlijk een testzadel
                if (!empty($testzadel->zadel_merk) || !empty($testzadel->zadel_model)) {
                    $defaultType = 'testzadel';
                } 
                // Als er geen zadel info is maar wel uitleendata, probeer te gissen
                else {
                    // Kijk naar bikefit context of andere hints
                    if ($testzadel->bikefit_id) {
                        $defaultType = 'testzadel'; // Bij bikefit is het meestal zadel
                    } else {
                        $defaultType = 'zooltjes'; // Anders waarschijnlijk zooltjes
                    }
                }
                
                $this->line("ID {$testzadel->id}: '{$testzadel->zadel_merk}' '{$testzadel->zadel_model}' → {$defaultType}");
                
                $testzadel->update([
                    'onderdeel_type' => $defaultType
                ]);
                
                $this->info("✅ Testzadel ID {$testzadel->id} updated naar onderdeel_type: '{$defaultType}'");
            }

            $this->info('🎉 Alle NULL onderdeel_type testzadels zijn gerepareerd!');

            // Verifieer het resultaat
            $this->info('');
            $this->info('🔍 Verificatie na reparatie:');
            
            $verificationCount = Testzadel::whereNull('onderdeel_type')->count();
            $emptyCount = Testzadel::where('onderdeel_type', '')->count();
            
            if ($verificationCount === 0 && $emptyCount === 0) {
                $this->info('✅ Alle testzadels hebben nu een geldig onderdeel_type');
            } else {
                $this->warn("⚠️ Er zijn nog {$verificationCount} NULL en {$emptyCount} empty onderdeel_type records");
            }

            // Toon overzicht van alle onderdeel_type waarden
            $this->info('');
            $this->info('📊 Huidige onderdeel_type distributie:');
            
            $types = Testzadel::select('onderdeel_type')
                             ->selectRaw('COUNT(*) as count')
                             ->groupBy('onderdeel_type')
                             ->get();
                             
            foreach ($types as $type) {
                $this->line("- {$type->onderdeel_type}: {$type->count} testzadels");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing NULL onderdeel_type: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}