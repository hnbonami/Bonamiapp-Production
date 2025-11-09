<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Models\Organisatie;
use Illuminate\Console\Command;

class CloneDefaultEmailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:clone-defaults {organisatie_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kloon Performance Pulse standaard email templates voor een organisatie';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $organisatieId = $this->argument('organisatie_id');
        
        if ($organisatieId) {
            // Kloon voor specifieke organisatie
            $organisatie = Organisatie::find($organisatieId);
            
            if (!$organisatie) {
                $this->error("❌ Organisatie met ID {$organisatieId} niet gevonden.");
                return 1;
            }
            
            $this->cloneTemplatesForOrganisatie($organisatie);
        } else {
            // Kloon voor ALLE organisaties
            // Later kun je dit aanpassen met een features check als die kolom bestaat
            $organisaties = Organisatie::all();
            
            if ($organisaties->isEmpty()) {
                $this->warn("⚠️  Geen organisaties gevonden in de database.");
                return 0;
            }
            
            $this->info("🔄 Klonen van templates voor {$organisaties->count()} organisaties...");
            
            foreach ($organisaties as $organisatie) {
                $this->cloneTemplatesForOrganisatie($organisatie);
            }
        }
        
        $this->info("✅ Klaar!");
        return 0;
    }
    
    /**
     * Kloon alle Performance Pulse templates voor een organisatie
     */
    private function cloneTemplatesForOrganisatie(Organisatie $organisatie)
    {
        $this->info("📋 Klonen templates voor: {$organisatie->naam}");
        
        // Haal alle Performance Pulse standaard templates op
        $defaultTemplates = EmailTemplate::whereNull('organisatie_id')
                                        ->where('is_default', true)
                                        ->where('is_active', true)
                                        ->get();
        
        $clonedCount = 0;
        $skippedCount = 0;
        
        foreach ($defaultTemplates as $defaultTemplate) {
            // Check of er al een custom template bestaat voor dit type
            $existingCustom = EmailTemplate::where('type', $defaultTemplate->type)
                                          ->where('organisatie_id', $organisatie->id)
                                          ->first();
            
            if ($existingCustom) {
                $this->warn("  ⚠️  {$defaultTemplate->name} bestaat al, overslaan...");
                $skippedCount++;
                continue;
            }
            
            // Maak een kopie van de template voor deze organisatie
            $clonedTemplate = EmailTemplate::create([
                'name' => $defaultTemplate->name,
                'slug' => $defaultTemplate->slug . '-org-' . $organisatie->id,
                'type' => $defaultTemplate->type,
                'subject' => $defaultTemplate->subject,
                'body_html' => $defaultTemplate->body_html,
                'description' => $defaultTemplate->description,
                'is_active' => true,
                'is_default' => false,
                'organisatie_id' => $organisatie->id,
                'parent_template_id' => $defaultTemplate->id,
            ]);
            
            $this->info("  ✅ {$defaultTemplate->name} gekloond (ID: {$clonedTemplate->id})");
            $clonedCount++;
        }
        
        $this->info("  📊 {$clonedCount} templates gekloond, {$skippedCount} overgeslagen voor {$organisatie->naam}");
    }
}