<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feature;
use Illuminate\Support\Facades\DB;

class AddGebruikersbeheerFeature extends Command
{
    protected $signature = 'features:add-gebruikersbeheer';
    protected $description = 'Voeg Gebruikersbeheer (Rechten & Rollen) feature toe als aparte feature';

    public function handle(): int
    {
        $this->info('👥 Gebruikersbeheer feature toevoegen...');
        
        DB::beginTransaction();
        
        try {
            // Check of feature al bestaat
            $exists = Feature::where('key', 'gebruikersbeheer')->exists();
            
            if ($exists) {
                $this->warn('⚠️  Feature "gebruikersbeheer" bestaat al!');
                return Command::SUCCESS;
            }
            
            // Maak nieuwe feature aan
            $feature = Feature::create([
                'key' => 'gebruikersbeheer',
                'naam' => 'Gebruikersbeheer',
                'beschrijving' => 'Beheer gebruikersrollen, tabblad toegang, permissions en login activiteit',
                'categorie' => 'beheer',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 60,
                'is_actief' => true,
            ]);
            
            $this->info("✅ Feature aangemaakt: {$feature->key} (ID: {$feature->id})");
            
            // Geef organisatie 1 automatisch toegang tot gebruikersbeheer
            $organisatie1HasMedewerkerbeheer = DB::table('organisatie_features')
                ->where('organisatie_id', 1)
                ->where('feature_id', 15) // medewerkerbeheer feature ID
                ->where('is_actief', 1)
                ->exists();
            
            if ($organisatie1HasMedewerkerbeheer) {
                DB::table('organisatie_features')->insert([
                    'organisatie_id' => 1,
                    'feature_id' => $feature->id,
                    'is_actief' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info('✅ Organisatie 1 krijgt automatisch toegang tot gebruikersbeheer');
            }
            
            DB::commit();
            
            $this->info('');
            $this->info('🎉 Gebruikersbeheer feature succesvol toegevoegd!');
            $this->info('');
            $this->info('📋 Feature details:');
            $this->line("  ID: {$feature->id}");
            $this->line("  Key: {$feature->key}");
            $this->line("  Naam: {$feature->naam}");
            $this->info('');
            $this->info('🔧 Volgende stap: Update admin/index.blade.php om @hasFeature(\'gebruikersbeheer\') te gebruiken');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}