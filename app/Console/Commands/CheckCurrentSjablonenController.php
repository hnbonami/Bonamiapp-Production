<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckCurrentSjablonenController extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-current-controller';

    /**
     * The console command description.
     */
    protected $description = 'Check de huidige status van SjablonenController edit method';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Controleren huidige SjablonenController...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            
            if (!File::exists($controllerPath)) {
                $this->error('SjablonenController niet gevonden');
                return Command::FAILURE;
            }
            
            $content = File::get($controllerPath);
            
            // Zoek naar edit method
            if (preg_match('/public function edit\(.*?\)\s*\{(.*?)(?=public function|\}$)/s', $content, $matches)) {
                $editMethod = $matches[0];
                
                $this->info('📝 Edit method gevonden');
                
                // Check wat er nu staat voor templateKeys
                if (preg_match('/templateKeys.*?=.*?(?=;|\n|return)/s', $editMethod, $templateMatch)) {
                    $this->info('📋 Huidige templateKeys implementatie:');
                    $templateKeysLine = trim($templateMatch[0]);
                    $this->line($templateKeysLine);
                    
                    // Check of het de database versie is
                    if (strpos($templateKeysLine, '\App\Models\TemplateKey::all()->groupBy') !== false) {
                        $this->info('✅ Database versie wordt gebruikt!');
                    } elseif (strpos($templateKeysLine, 'collect([') !== false) {
                        $this->warn('⚠️ Hardcoded versie wordt nog gebruikt');
                        
                        // Toon waar de hardcoded array begint en eindigt
                        if (preg_match('/templateKeys = collect\(\[(.*?)\]\);/s', $editMethod, $fullMatch)) {
                            $this->line('');
                            $this->line('📄 Volledige hardcoded array gevonden:');
                            $this->line('Lengte: ' . strlen($fullMatch[0]) . ' karakters');
                            $this->line('Begin: ' . substr($fullMatch[0], 0, 100) . '...');
                            $this->line('Eind: ...' . substr($fullMatch[0], -100));
                        }
                    } else {
                        $this->info('🤔 Onbekende templateKeys implementatie');
                    }
                } else {
                    $this->warn('⚠️ Geen templateKeys implementatie gevonden');
                }
                
                // Toon de volledige edit method voor debugging
                $this->info('');
                $this->info('📄 Volledige edit method (eerste 2000 chars):');
                $this->line(substr($editMethod, 0, 2000) . '...');
                
            } else {
                $this->warn('Edit method niet gevonden');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}