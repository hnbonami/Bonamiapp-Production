<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DebugTemplateReplacementExecution extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:debug-execution';

    /**
     * The console command description.
     */
    protected $description = 'Debug welke template replacements daadwerkelijk worden uitgevoerd';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🐛 Adding execution debugging to template replacement...');

        try {
            $controllerPath = app_path('Http/Controllers/SjablonenController.php');
            $content = File::get($controllerPath);
            
            // Voeg debug output toe aan een bestaande replacement die WEL werkt
            $workingReplacement = "str_replace('{{bikefit.opmerkingen}}', \$bikefit->opmerkingen ?? '', \$content);";
            
            if (strpos($content, $workingReplacement) !== false) {
                $debuggedReplacement = "str_replace('{{bikefit.opmerkingen}}', \$bikefit->opmerkingen ?? '', \$content);
                \\Log::info('🔥 WORKING REPLACEMENT EXECUTED', ['opmerkingen' => \$bikefit->opmerkingen ?? 'EMPTY']);";
                
                $content = str_replace($workingReplacement, $debuggedReplacement, $content);
            }
            
            // Voeg debug output toe aan een nieuwe replacement
            $newReplacement = "str_replace('{{bikefit.rotatie_aanpassingen}}', \$bikefit->rotatie_aanpassingen ?? '', \$content);";
            
            if (strpos($content, $newReplacement) !== false) {
                $debuggedNewReplacement = "str_replace('{{bikefit.rotatie_aanpassingen}}', \$bikefit->rotatie_aanpassingen ?? '', \$content);
                \\Log::info('🔥 NEW REPLACEMENT EXECUTED', ['rotatie_aanpassingen' => \$bikefit->rotatie_aanpassingen ?? 'EMPTY']);";
                
                $content = str_replace($newReplacement, $debuggedNewReplacement, $content);
            }
            
            // Voeg debug aan het begin van generatePagesForBikefit
            if (strpos($content, 'function generatePagesForBikefit(') !== false) {
                $methodStart = 'function generatePagesForBikefit($sjabloon, $bikefit)
    {';
                
                $debuggedMethodStart = 'function generatePagesForBikefit($sjabloon, $bikefit)
    {
        \\Log::info("🔥 generatePagesForBikefit CALLED", [
            "bikefit_id" => $bikefit->id ?? "unknown",
            "rotatie_aanpassingen_value" => $bikefit->rotatie_aanpassingen ?? "EMPTY_OR_NULL"
        ]);';
                
                $content = str_replace($methodStart, $debuggedMethodStart, $content);
            }
            
            File::put($controllerPath, $content);
            
            $this->info('✅ Debug logging toegevoegd');
            $this->info('');
            $this->info('🎯 NU TESTEN:');
            $this->info('1. Genereer rapport: http://127.0.0.1:8000/bikefit/29/16/sjabloon-rapport');
            $this->info('2. Check logs: tail -f storage/logs/laravel.log');
            $this->info('');
            $this->info('💡 KIJK NAAR:');
            $this->info('- Zie je "🔥 generatePagesForBikefit CALLED"?');
            $this->info('- Zie je "🔥 WORKING REPLACEMENT EXECUTED"?');
            $this->info('- Zie je "🔥 NEW REPLACEMENT EXECUTED"?');
            $this->info('- Wat is de waarde van rotatie_aanpassingen_value?');
            $this->info('');
            $this->info('🚨 ALS JE GEEN LOGS ZIET:');
            $this->info('Dan wordt generatePagesForBikefit NIET aangeroepen voor jouw URL');
            $this->info('Dan moeten we een andere method vinden!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}