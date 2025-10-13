<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Testzadel;

class SimulateTestzadelUpdate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:simulate-testzadel-update {testzadel_id} {onderdeel_type}';

    /**
     * The console command description.
     */
    protected $description = 'Simuleer testzadel controller update om probleem te debuggen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $testzadelId = $this->argument('testzadel_id');
        $newOnderdeelType = $this->argument('onderdeel_type');
        
        try {
            $testzadel = Testzadel::find($testzadelId);
            
            if (!$testzadel) {
                $this->error("Testzadel ID {$testzadelId} niet gevonden");
                return Command::FAILURE;
            }
            
            $this->info("🧪 Simuleren controller update voor testzadel ID {$testzadelId}");
            $this->line("Huidige onderdeel_type: '{$testzadel->onderdeel_type}'");
            $this->line("Nieuwe onderdeel_type: '{$newOnderdeelType}'");
            
            // Simuleer de validation data zoals controller doet
            $validated = [
                'klant_id' => $testzadel->klant_id,
                'bikefit_id' => $testzadel->bikefit_id,
                'onderdeel_type' => $newOnderdeelType,
                'status' => $testzadel->status,
                'zadel_merk' => $testzadel->zadel_merk,
                'zadel_model' => $testzadel->zadel_model,
                'zadel_type' => $testzadel->zadel_type,
                'zadel_breedte' => $testzadel->zadel_breedte,
                'automatisch_mailtje' => $testzadel->automatisch_mailtje,
                'uitleen_datum' => $testzadel->uitleen_datum,
                'verwachte_retour_datum' => $testzadel->verwachte_retour_datum,
                'opmerkingen' => $testzadel->opmerkingen,
            ];
            
            $this->info("\n🔄 Validated data die gebruikt zou worden:");
            foreach ($validated as $key => $value) {
                $this->line("  {$key}: '{$value}'");
            }
            
            // Probeer de update zoals controller doet
            $this->info("\n🔧 Uitvoeren van testzadel->update()...");
            
            $updateResult = $testzadel->update($validated);
            $this->line("Update result: " . ($updateResult ? 'true' : 'false'));
            
            // Refresh en controleer
            $testzadel->refresh();
            
            $this->info("\n✅ Na update - nieuwe waarden:");
            $this->line("onderdeel_type: '{$testzadel->onderdeel_type}'");
            $this->line("status: '{$testzadel->status}'");
            $this->line("updated_at: {$testzadel->updated_at}");
            
            if ($testzadel->onderdeel_type === $newOnderdeelType) {
                $this->info("🎉 SUCCESS: onderdeel_type correct bijgewerkt!");
            } else {
                $this->error("❌ FAILED: onderdeel_type niet bijgewerkt. Nog steeds: '{$testzadel->onderdeel_type}'");
                
                // Probeer alternatieve methoden
                $this->info("\n🔄 Proberen alternatieve update methoden...");
                
                // Methode 1: Direct assignment + save
                $testzadel->onderdeel_type = $newOnderdeelType;
                $saveResult = $testzadel->save();
                $testzadel->refresh();
                
                $this->line("Methode 1 (direct assignment + save): " . ($testzadel->onderdeel_type === $newOnderdeelType ? 'SUCCESS' : 'FAILED'));
                
                // Methode 2: Fill + save
                $testzadel->fill(['onderdeel_type' => $newOnderdeelType]);
                $fillResult = $testzadel->save();
                $testzadel->refresh();
                
                $this->line("Methode 2 (fill + save): " . ($testzadel->onderdeel_type === $newOnderdeelType ? 'SUCCESS' : 'FAILED'));
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}