<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Sjabloon;

class FindTemplateContentSource extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:find-template-source';

    /**
     * The console command description.
     */
    protected $description = 'Zoek waar de template content daadwerkelijk vandaan komt';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Zoeken naar de echte template content source...');

        try {
            // Check het sjabloon record nog een keer
            $sjabloon = Sjabloon::where('is_actief', 1)
                ->where('testtype', 'standaard bikefit')
                ->where('categorie', 'bikefit')
                ->first();

            if ($sjabloon) {
                $this->info("📋 Sjabloon record gevonden:");
                $this->info("  ID: {$sjabloon->id}");
                $this->info("  Naam: {$sjabloon->naam}");
                $this->info("  Bestand: " . ($sjabloon->bestand ?? 'GEEN'));
                $this->info("  Inhoud lengte: " . strlen($sjabloon->inhoud ?? ''));
                
                // Check of er een bestand is
                if ($sjabloon->bestand) {
                    $bestandPath = storage_path('app/sjablonen/' . $sjabloon->bestand);
                    
                    if (File::exists($bestandPath)) {
                        $this->info("✅ Sjabloon bestand gevonden: {$bestandPath}");
                        
                        $content = File::get($bestandPath);
                        $this->info("📄 Bestand grootte: " . strlen($content) . " characters");
                        
                        // Zoek naar template keys in het bestand
                        preg_match_all('/\{\{[^}]+\}\}/', $content, $matches);
                        
                        if (!empty($matches[0])) {
                            $templateKeys = array_unique($matches[0]);
                            $this->info("🎯 Template keys in bestand (" . count($templateKeys) . "):");
                            
                            foreach ($templateKeys as $key) {
                                if (strpos($key, 'bikefit.') !== false) {
                                    $this->line("  {$key}");
                                }
                            }
                            
                            // Zoek specifiek naar de keys die je in de screenshot zag
                            $problematicKeys = [
                                '{{bikefit.stuurpen_voor}}',
                                '{{bikefit.stuurpen_na}}', 
                                '{{bikefit.inclinatie_zadel}}',
                                '{{bikefit.rotatie_schoenplaatjes}}',
                                '{{inclinatie.rotatie_schoenplaatjes}}'
                            ];
                            
                            $this->info('');
                            $this->info('🚨 Problematische keys uit screenshot:');
                            foreach ($problematicKeys as $key) {
                                if (in_array($key, $templateKeys)) {
                                    $this->error("  ❌ {$key} - GEVONDEN maar geen replacement!");
                                } else {
                                    $this->line("  ❓ {$key} - Niet in bestand");
                                }
                            }
                            
                        } else {
                            $this->warn('Geen template keys gevonden in bestand');
                        }
                        
                    } else {
                        $this->error("❌ Sjabloon bestand niet gevonden: {$bestandPath}");
                    }
                } else {
                    $this->warn('Geen bestand gespecificeerd in sjabloon record');
                }
            }
            
            // Zoek ook naar andere mogelijke template locaties
            $this->info('');
            $this->info('🔍 Zoeken naar andere template bestanden...');
            
            $templateDirs = [
                storage_path('app/sjablonen'),
                resource_path('views/sjablonen'),
                resource_path('views/templates'),
                resource_path('templates'),
            ];
            
            foreach ($templateDirs as $dir) {
                if (File::exists($dir)) {
                    $this->info("📁 Zoeken in: {$dir}");
                    
                    $files = File::glob($dir . '/*.{html,htm,blade.php}', GLOB_BRACE);
                    foreach ($files as $file) {
                        $content = File::get($file);
                        if (strpos($content, '{{bikefit.') !== false) {
                            $this->info("  ✅ Template keys gevonden in: " . basename($file));
                        }
                    }
                } else {
                    $this->line("  ❌ Directory bestaat niet: {$dir}");
                }
            }
            
            $this->info('');
            $this->info('💡 VOLGENDE STAPPEN:');
            $this->info('1. Als er een bestand is gevonden met template keys:');
            $this->info('   php artisan bonami:add-missing-from-file [BESTAND]');
            $this->info('2. Of voeg de ontbrekende keys handmatig toe met:');
            $this->info('   php artisan bonami:add-specific-keys');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}