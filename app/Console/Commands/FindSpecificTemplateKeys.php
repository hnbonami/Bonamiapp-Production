<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindSpecificTemplateKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:find-specific-keys';

    /**
     * The console command description.
     */
    protected $description = 'Zoek naar de specifieke template keys uit de screenshot';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Zoeken naar specifieke template keys uit de screenshot...');

        try {
            // De keys die we zoeken uit de screenshot
            $targetKeys = [
                'bikefit.stuurpen_voor',
                'bikefit.stuurpen_na', 
                'bikefit.inclinatie_zadel',
                'bikefit.rotatie_schoenplaatjes',
                'inclinatie.rotatie_schoenplaatjes'
            ];

            $this->info('🎯 Zoeken naar deze keys:');
            foreach ($targetKeys as $key) {
                $this->line("  {{$key}}");
            }
            $this->info('');

            // Zoek in alle mogelijke directories
            $searchDirs = [
                resource_path('views'),
                storage_path('app'),
                base_path('resources'),
                app_path(),
            ];

            $foundFiles = [];

            foreach ($searchDirs as $dir) {
                if (!File::exists($dir)) continue;

                $this->info("📁 Zoeken in: {$dir}");

                // Zoek recursief naar alle bestanden die template keys kunnen bevatten
                $files = File::allFiles($dir);
                
                foreach ($files as $file) {
                    $extension = $file->getExtension();
                    
                    // Skip bestanden die waarschijnlijk geen templates zijn
                    if (!in_array($extension, ['php', 'blade', 'html', 'htm', ''])) {
                        continue;
                    }
                    
                    $content = File::get($file->getPathname());
                    
                    // Check of een van onze target keys in dit bestand staat
                    $foundInFile = [];
                    foreach ($targetKeys as $key) {
                        if (strpos($content, '{{'.$key.'}}') !== false) {
                            $foundInFile[] = $key;
                        }
                    }
                    
                    if (!empty($foundInFile)) {
                        $relativePath = str_replace(base_path(), '', $file->getPathname());
                        $foundFiles[$relativePath] = $foundInFile;
                        
                        $this->info("  ✅ GEVONDEN in: {$relativePath}");
                        foreach ($foundInFile as $foundKey) {
                            $this->line("    - {{{{{$foundKey}}}}}");
                        }
                    }
                }
            }

            if (empty($foundFiles)) {
                $this->error('❌ GEEN van de target keys gevonden in bestanden!');
                $this->info('');
                $this->info('💡 Mogelijke oorzaken:');
                $this->info('1. De keys worden dynamisch gegenereerd');
                $this->info('2. Ze staan in een database veld');
                $this->info('3. Ze worden via JavaScript toegevoegd');
                $this->info('4. Het template wordt via een andere route geladen');
            } else {
                $this->info('');
                $this->info('🎉 GEVONDEN! De template keys staan in deze bestanden:');
                
                foreach ($foundFiles as $file => $keys) {
                    $this->info("📄 {$file}");
                    foreach ($keys as $key) {
                        $this->line("  - {{{{{$key}}}}}");
                    }
                }
                
                $this->info('');
                $this->info('🔧 NU KUNNEN WE DE JUISTE REPLACEMENTS TOEVOEGEN:');
                $this->info('php artisan bonami:add-exact-replacements');
            }

            // Als we specifieke bestanden hebben gevonden, laten we ook de volledige inhoud zien
            if (count($foundFiles) === 1) {
                $file = array_keys($foundFiles)[0];
                $fullPath = base_path() . $file;
                
                $this->info('');
                $this->info("📄 Inhoud van {$file}:");
                $this->info('=' . str_repeat('=', 60));
                
                $content = File::get($fullPath);
                $lines = explode("\n", $content);
                
                // Toon alleen de eerste 20 lijnen om het overzichtelijk te houden
                for ($i = 0; $i < min(20, count($lines)); $i++) {
                    $this->line(sprintf("%3d: %s", $i + 1, $lines[$i]));
                }
                
                if (count($lines) > 20) {
                    $this->line("... (en " . (count($lines) - 20) . " lijnen meer)");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}