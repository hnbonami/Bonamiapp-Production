<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DebugTemplateReplacement extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:debug-template';

    /**
     * The console command description.
     */
    protected $description = 'Debug template replacement door logging toe te voegen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🐛 Adding debug logging to template replacement...');

        try {
            // Voeg debug logging toe aan BikefitController
            $bikefitPath = app_path('Http/Controllers/BikefitController.php');
            $content = File::get($bikefitPath);
            
            // Zoek naar de generateSjabloonReport method
            if (strpos($content, 'function generateSjabloonReport') !== false) {
                $this->info('✅ Gevonden generateSjabloonReport in BikefitController');
                
                // Voeg debug log toe aan het begin van de method
                $debugCode = '
        \\Log::info("🔥 DEBUG: generateSjabloonReport called in BikefitController", [
            "bikefit_id" => $bikefit->id ?? "unknown",
            "method" => "BikefitController@generateSjabloonReport"
        ]);';
                
                $newContent = str_replace(
                    'function generateSjabloonReport(',
                    'function generateSjabloonReport(' . $debugCode . '
        // Original method continues here
        ',
                    $content
                );
                
                File::put($bikefitPath, $newContent);
                $this->info('📝 Debug logging toegevoegd aan BikefitController');
            }
            
            // Voeg ook debug logging toe aan SjablonenController
            $sjablonenPath = app_path('Http/Controllers/SjablonenController.php');
            $sjablonenContent = File::get($sjablonenPath);
            
            if (strpos($sjablonenContent, 'function generatePagesForBikefit') !== false) {
                $this->info('✅ Gevonden generatePagesForBikefit in SjablonenController');
                
                $debugCode2 = '
        \\Log::info("🔥 DEBUG: generatePagesForBikefit called in SjablonenController", [
            "bikefit_id" => $bikefit->id ?? "unknown",
            "method" => "SjablonenController@generatePagesForBikefit"
        ]);';
                
                $newSjablonenContent = str_replace(
                    'function generatePagesForBikefit(',
                    'function generatePagesForBikefit(' . $debugCode2 . '
        // Original method continues here
        ',
                    $sjablonenContent
                );
                
                File::put($sjablonenPath, $newSjablonenContent);
                $this->info('📝 Debug logging toegevoegd aan SjablonenController');
            }
            
            // Voeg specifieke debug voor nieuwe template keys toe
            $this->info('');
            $this->info('🎯 VOLGENDE STAPPEN:');
            $this->info('1. Genereer een rapport: http://127.0.0.1:8000/bikefit/29/16/sjabloon-rapport');
            $this->info('2. Check de logs: tail -f storage/logs/laravel.log');
            $this->info('3. Zoek naar "🔥 DEBUG" berichten om te zien welke method wordt aangeroepen');
            $this->info('');
            $this->info('💡 Als je alleen SjablonenController logs ziet, dan moeten we daar de nieuwe keys toevoegen');
            $this->info('💡 Als je alleen BikefitController logs ziet, dan moeten we daar kijken waarom het niet werkt');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}