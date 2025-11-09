<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTemplate;
use App\Models\Organisatie;

class RenameOrganisatieEmailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:rename-organisatie-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verwijder en hermaak alle organisatie email templates met correcte organisatienamen';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Start met hernoemen van organisatie email templates...');
        
        // Haal alle organisaties op die templates hebben
        $organisaties = Organisatie::whereHas('emailTemplates')->get();
        
        if ($organisaties->isEmpty()) {
            $this->warn('⚠️  Geen organisaties met email templates gevonden.');
            return 0;
        }
        
        $this->info("📊 Gevonden: {$organisaties->count()} organisaties met templates");
        
        $totalDeleted = 0;
        $totalCreated = 0;
        
        foreach ($organisaties as $organisatie) {
            $this->line('');
            $this->info("🏢 Organisatie: {$organisatie->naam} (ID: {$organisatie->id})");
            
            // Tel huidige templates
            $currentTemplates = EmailTemplate::where('organisatie_id', $organisatie->id)->get();
            $this->line("   Huidige templates: {$currentTemplates->count()}");
            
            // Verwijder alle bestaande templates van deze organisatie
            $deletedCount = EmailTemplate::where('organisatie_id', $organisatie->id)->delete();
            $totalDeleted += $deletedCount;
            $this->line("   ❌ Verwijderd: {$deletedCount} templates");
            
            // Haal Performance Pulse standaard templates op
            $defaultTemplates = EmailTemplate::whereNull('organisatie_id')
                                            ->where('is_default', true)
                                            ->where('is_active', true)
                                            ->get();
            
            if ($defaultTemplates->isEmpty()) {
                $this->error("   ⚠️  Geen Performance Pulse templates gevonden om te klonen!");
                continue;
            }
            
            $createdCount = 0;
            
            foreach ($defaultTemplates as $defaultTemplate) {
                // Vervang "Performance Pulse" in de naam met de organisatie naam
                $templateNaam = str_replace('Performance Pulse', $organisatie->naam, $defaultTemplate->name);
                
                // Als "Performance Pulse" niet in de naam staat, voeg organisatie naam toe als prefix
                if ($templateNaam === $defaultTemplate->name) {
                    $templateNaam = $organisatie->naam . ' - ' . $defaultTemplate->name;
                }
                
                // Maak nieuwe template aan
                EmailTemplate::create([
                    'name' => $templateNaam,
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
                
                $createdCount++;
            }
            
            $totalCreated += $createdCount;
            $this->line("   ✅ Aangemaakt: {$createdCount} nieuwe templates met organisatienaam");
        }
        
        $this->line('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("✅ Klaar!");
        $this->info("📊 Totaal verwijderd: {$totalDeleted} templates");
        $this->info("📊 Totaal aangemaakt: {$totalCreated} templates");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        return 0;
    }
}
