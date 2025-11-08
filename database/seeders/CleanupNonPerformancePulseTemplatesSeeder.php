<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class CleanupNonPerformancePulseTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // STAP 1: Verwijder ALLE templates die NIET "Performance Pulse" in de naam hebben
        // BEHALVE templates van organisatie ID 1 (superadmin)
        $deleted = EmailTemplate::where('name', 'NOT LIKE', '%Performance Pulse%')
            ->where(function($query) {
                $query->where('organisatie_id', '!=', 1)
                      ->orWhereNull('organisatie_id');
            })
            ->delete();
        
        $this->command->info("✅ {$deleted} niet-Performance Pulse templates verwijderd");
        
        // STAP 2: Zet alle overgebleven Performance Pulse templates op organisatie_id = null (standaard)
        $updated = EmailTemplate::where('name', 'LIKE', '%Performance Pulse%')
            ->update(['organisatie_id' => null]);
        
        $this->command->info("✅ {$updated} Performance Pulse templates ingesteld als standaard (organisatie_id = null)");
        
        // STATISTIEKEN
        $performancePulse = EmailTemplate::where('name', 'LIKE', '%Performance Pulse%')->count();
        $this->command->info("📊 Performance Pulse templates: {$performancePulse}");
        
        $org1Templates = EmailTemplate::where('organisatie_id', 1)->count();
        $this->command->info("📊 Organisatie 1 (superadmin) templates: {$org1Templates}");
        
        $total = EmailTemplate::count();
        $this->command->info("📊 Totaal templates: {$total} (zou 6-7 moeten zijn)");
        
        if ($total > 7) {
            $this->command->warn("⚠️  Er zijn nog steeds teveel templates ({$total})! Verwacht werd 6-7 templates.");
        } else {
            $this->command->info("✅ Cleanup compleet! Nieuwe organisaties zien nu alleen Performance Pulse templates.");
        }
    }
}