<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feature;
use Illuminate\Support\Facades\DB;

class AddEmailBeheerFeature extends Command
{
    protected $signature = 'features:add-email-beheer';
    protected $description = 'Voeg Email Beheer feature toe als aparte feature';

    public function handle(): int
    {
        $this->info('📧 Email Beheer feature toevoegen...');
        
        DB::beginTransaction();
        
        try {
            // Check of feature al bestaat
            $exists = Feature::where('key', 'email_beheer')->exists();
            
            if ($exists) {
                $this->warn('⚠️  Feature "email_beheer" bestaat al!');
                return Command::SUCCESS;
            }
            
            // Maak nieuwe feature aan
            $feature = Feature::create([
                'key' => 'email_beheer',
                'naam' => 'Email Beheer',
                'beschrijving' => 'Beheer email templates, instellingen, logs en automatische herinneringen',
                'categorie' => 'beheer',
                'is_premium' => false,
                'prijs_per_maand' => null,
                'sorteer_volgorde' => 50,
                'is_actief' => true,
            ]);
            
            $this->info("✅ Feature aangemaakt: {$feature->key} (ID: {$feature->id})");
            
            // Geef organisatie 1 automatisch toegang tot email_beheer
            $organisatie1HasSjablonen = DB::table('organisatie_features')
                ->where('organisatie_id', 1)
                ->where('feature_id', 7) // sjablonen feature ID
                ->where('is_actief', 1)
                ->exists();
            
            if ($organisatie1HasSjablonen) {
                DB::table('organisatie_features')->insert([
                    'organisatie_id' => 1,
                    'feature_id' => $feature->id,
                    'is_actief' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info('✅ Organisatie 1 krijgt automatisch toegang tot email_beheer');
            }
            
            DB::commit();
            
            $this->info('');
            $this->info('🎉 Email Beheer feature succesvol toegevoegd!');
            $this->info('');
            $this->info('📋 Feature details:');
            $this->line("  ID: {$feature->id}");
            $this->line("  Key: {$feature->key}");
            $this->line("  Naam: {$feature->naam}");
            $this->info('');
            $this->info('🔧 Volgende stap: Update admin/index.blade.php om @hasFeature(\'email_beheer\') te gebruiken');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}