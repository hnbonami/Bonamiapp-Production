<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Testzadel;

class FixTestzadelStatussen extends Command
{
    protected $signature = 'testzadel:fix-statussen';
    protected $description = 'Fix testzadels met ongeldige "nieuw" status naar correcte statussen';

    public function handle()
    {
        $this->info('🔧 Bezig met fixen van testzadel statussen...');
        
        // Tel testzadels met "nieuw" status
        $countNieuw = Testzadel::where('status', 'nieuw')->count();
        
        if ($countNieuw === 0) {
            $this->info('✅ Geen testzadels met "nieuw" status gevonden.');
            return;
        }
        
        $this->warn("⚠️ Gevonden {$countNieuw} testzadels met ongeldige 'nieuw' status");
        
        // Fix de statussen
        $fixed = Testzadel::fixNieuweStatussen();
        
        $this->info("✅ {$fixed} testzadels succesvol gefixed!");
        
        // Toon overzicht van huidige statussen
        $this->info("\n📊 Overzicht testzadel statussen:");
        foreach (Testzadel::getStatussen() as $status => $label) {
            $count = Testzadel::where('status', $status)->count();
            $this->line("   {$label}: {$count}");
        }
        
        // Check of er nog steeds ongeldige statussen zijn
        $remaining = Testzadel::whereNotIn('status', array_keys(Testzadel::getStatussen()))->count();
        if ($remaining > 0) {
            $this->error("⚠️ Er zijn nog {$remaining} testzadels met ongeldige statussen!");
        }
    }
}