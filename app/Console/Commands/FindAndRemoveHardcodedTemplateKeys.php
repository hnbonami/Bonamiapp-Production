<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindAndRemoveHardcodedTemplateKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:find-hardcoded-keys';

    /**
     * The console command description.
     */
    protected $description = 'Vind en toon de hardcoded template keys array die vervangen moet worden';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Zoeken naar hardcoded template keys array...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            
            // Zoek naar de templateKeys = collect([ tot de ]);
            if (preg_match('/templateKeys = collect\(\[(.*?)\]\);/s', $content, $matches)) {
                $this->info('📋 Hardcoded templateKeys array gevonden:');
                $this->line('Lengte: ' . strlen($matches[0]) . ' karakters');
                $this->line('');
                $this->line('Begin:');
                $this->line(substr($matches[0], 0, 200) . '...');
                $this->line('');
                $this->line('Eind:');
                $this->line('...' . substr($matches[0], -200));
                
                // Toon hoe het vervangen moet worden
                $this->info('');
                $this->info('✅ Dit moet vervangen worden door:');
                $this->line('$templateKeys = \App\Models\TemplateKey::all()->groupBy(\'category\');');
                
            } else {
                $this->warn('Hardcoded templateKeys array niet gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}