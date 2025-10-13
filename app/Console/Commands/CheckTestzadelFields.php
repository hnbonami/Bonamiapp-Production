<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Testzadel;

class CheckTestzadelFields extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-testzadel-fields {testzadel_id}';

    /**
     * The console command description.
     */
    protected $description = 'Check testzadel velden in database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $testzadelId = $this->argument('testzadel_id');
        
        try {
            $testzadel = Testzadel::find($testzadelId);
            
            if (!$testzadel) {
                $this->error("Testzadel ID {$testzadelId} niet gevonden");
                return Command::FAILURE;
            }
            
            $this->info("🔍 Testzadel ID {$testzadelId} - Huidige velden:");
            $this->line("onderdeel_type: '{$testzadel->onderdeel_type}'");
            $this->line("status: '{$testzadel->status}'");
            $this->line("zadel_merk: '{$testzadel->zadel_merk}'");
            $this->line("zadel_model: '{$testzadel->zadel_model}'");
            $this->line("zadel_type: '{$testzadel->zadel_type}'");
            $this->line("zadel_breedte: '{$testzadel->zadel_breedte}'");
            $this->line("automatisch_mailtje: " . ($testzadel->automatisch_mailtje ? 'true' : 'false'));
            $this->line("updated_at: {$testzadel->updated_at}");
            
            // Test een directe update om te zien of het werkt
            $this->info("\n🧪 Testing direct onderdeel_type update:");
            $oldType = $testzadel->onderdeel_type;
            $testType = 'zooltjes';
            
            $testzadel->onderdeel_type = $testType;
            $testzadel->save();
            
            $testzadel->refresh();
            
            if ($testzadel->onderdeel_type === $testType) {
                $this->info("✅ Direct update WERKT: '{$oldType}' → '{$testzadel->onderdeel_type}'");
                
                // Zet terug naar originele waarde
                $testzadel->onderdeel_type = $oldType;
                $testzadel->save();
                $this->line("🔄 Teruggezet naar originele waarde: '{$oldType}'");
            } else {
                $this->error("❌ Direct update WERKT NIET: nog steeds '{$testzadel->onderdeel_type}'");
            }
            
            // Toon alle attributes voor debugging
            $this->info("\n📋 Alle database attributes:");
            foreach ($testzadel->getAttributes() as $key => $value) {
                $this->line("{$key}: '{$value}'");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}