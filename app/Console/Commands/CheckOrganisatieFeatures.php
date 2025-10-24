<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organisatie;
use App\Models\Feature;

class CheckOrganisatieFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organisatie:check-features {organisatie_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Controleer welke features een organisatie heeft en of ze actief zijn';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $organisatieId = $this->argument('organisatie_id');
        $organisatie = Organisatie::find($organisatieId);

        if (!$organisatie) {
            $this->error("Organisatie met ID {$organisatieId} niet gevonden!");
            return 1;
        }

        $this->info("=== Feature Check voor Organisatie ===");
        $this->info("ID: {$organisatie->id}");
        $this->info("Naam: {$organisatie->naam}");
        $this->info("Email: {$organisatie->email}");
        $this->info("Status: {$organisatie->status}");
        $this->newLine();

        // Haal alle features op
        $alleFeatures = Feature::orderBy('categorie')->orderBy('sorteer_volgorde')->get();
        
        $this->info("=== Feature Overzicht ===");
        $this->table(
            ['Feature', 'Key', 'Heeft Toegang', 'Is Actief', 'Premium', 'Vervalt Op'],
            $alleFeatures->map(function($feature) use ($organisatie) {
                $pivot = $organisatie->features()->where('feature_id', $feature->id)->first();
                $heeftToegang = $organisatie->hasFeature($feature->key);
                
                return [
                    $feature->naam,
                    $feature->key,
                    $heeftToegang ? '✅ JA' : '❌ NEE',
                    $pivot ? ($pivot->pivot->is_actief ? '✅' : '❌') : '-',
                    $feature->is_premium ? '💎' : '-',
                    $pivot && $pivot->pivot->expires_at ? $pivot->pivot->expires_at : '-'
                ];
            })
        );

        $this->newLine();
        $this->info("Totaal aantal features: " . $alleFeatures->count());
        $this->info("Actieve features: " . $organisatie->features()->wherePivot('is_actief', true)->count());
        
        return 0;
    }
}