<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Klant;

class CleanupSoftDeletedKlanten extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'klanten:cleanup-deleted 
                            {--force : Voer cleanup uit zonder bevestiging}
                            {--days= : Verwijder alleen klanten die langer dan X dagen soft deleted zijn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verwijder permanent alle soft deleted klanten en hun gerelateerde data (GDPR cleanup)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $force = $this->option('force');
        
        // Haal soft deleted klanten op
        $query = Klant::onlyTrashed();
        
        if ($days) {
            $query->where('deleted_at', '<=', now()->subDays($days));
            $this->info("🔍 Zoeken naar klanten soft deleted > {$days} dagen geleden...");
        } else {
            $this->info("🔍 Zoeken naar ALLE soft deleted klanten...");
        }
        
        $softDeletedKlanten = $query->get();
        $count = $softDeletedKlanten->count();
        
        if ($count === 0) {
            $this->info("✅ Geen soft deleted klanten gevonden.");
            return 0;
        }
        
        // Toon overzicht
        $this->warn("⚠️  Gevonden: {$count} soft deleted klanten");
        
        $this->table(
            ['ID', 'Naam', 'Email', 'Verwijderd op'],
            $softDeletedKlanten->map(function($klant) {
                return [
                    $klant->id,
                    $klant->naam,
                    $klant->email,
                    $klant->deleted_at->format('d-m-Y H:i')
                ];
            })
        );
        
        // Bevestiging vragen (tenzij --force)
        if (!$force) {
            if (!$this->confirm("⚠️  Dit verwijdert PERMANENT {$count} klanten + gerelateerde data. Doorgaan?", false)) {
                $this->info("❌ Cleanup geannuleerd.");
                return 1;
            }
        }
        
        $this->info("🗑️  Verwijderen van {$count} klanten...");
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();
        
        $deletedCount = 0;
        $errors = [];
        
        foreach ($softDeletedKlanten as $klant) {
            try {
                // Force delete triggert onze cascade delete in Klant model
                $klant->forceDelete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "Klant {$klant->id} ({$klant->naam}): {$e->getMessage()}";
                \Log::error("Cleanup fout voor klant {$klant->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Resultaten
        $this->info("✅ Cleanup voltooid!");
        $this->info("📊 Verwijderd: {$deletedCount} van {$count} klanten");
        
        if (count($errors) > 0) {
            $this->error("❌ Fouten opgetreden bij " . count($errors) . " klanten:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }
        
        // Controleer hoeveel er nog over zijn
        $remaining = Klant::onlyTrashed()->count();
        $this->info("📊 Nog {$remaining} soft deleted klanten over");
        
        return 0;
    }
}
