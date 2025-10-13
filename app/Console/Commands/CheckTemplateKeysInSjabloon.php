<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sjabloon;

class CheckTemplateKeysInSjabloon extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-template-keys';

    /**
     * The console command description.
     */
    protected $description = 'Check welke template keys daadwerkelijk in het sjabloon staan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking template keys in actual sjabloon...');

        try {
            // Zoek het bikefit sjabloon
            $sjabloon = Sjabloon::where('is_actief', 1)
                ->where('testtype', 'standaard bikefit')
                ->where('categorie', 'bikefit')
                ->first();

            if (!$sjabloon) {
                $this->error('Geen actief bikefit sjabloon gevonden');
                return Command::FAILURE;
            }

            $this->info("✅ Gevonden sjabloon: {$sjabloon->naam}");

            // Zoek naar alle bikefit template keys in de content
            $content = $sjabloon->inhoud ?? '';
            
            if (empty($content)) {
                $this->warn('Sjabloon inhoud is leeg');
                return Command::FAILURE;
            }

            // Zoek alle {{bikefit.*}} patterns
            preg_match_all('/\{\{bikefit\.[^}]+\}\}/', $content, $matches);

            if (empty($matches[0])) {
                $this->warn('Geen {{bikefit.*}} template keys gevonden in sjabloon');
                return Command::SUCCESS;
            }

            $this->info('🎯 Template keys gevonden in sjabloon:');
            $templateKeys = array_unique($matches[0]);
            sort($templateKeys);

            foreach ($templateKeys as $key) {
                $this->line("  {$key}");
            }

            $this->info('');
            $this->info('🔍 PROBLEEM ANALYSE:');

            // Check of de nieuwe keys in het sjabloon staan
            $newKeys = [
                '{{bikefit.rotatie_aanpassingen}}',
                '{{bikefit.inclinatie_aanpassingen}}',
                '{{bikefit.type_fitting}}',
                '{{bikefit.type_fiets}}',
                '{{bikefit.frametype}}',
                '{{bikefit.type_zadel}}',
                '{{bikefit.voetpositie}}',
            ];

            $this->info('✅ Nieuwe keys die WEL een replacement hebben:');
            foreach ($newKeys as $key) {
                if (in_array($key, $templateKeys)) {
                    $this->info("  ✅ {$key} - GEVONDEN in sjabloon");
                } else {
                    $this->line("  ❌ {$key} - NIET in sjabloon");
                }
            }

            $this->info('');
            $this->info('❌ Keys in sjabloon die GEEN replacement hebben:');
            foreach ($templateKeys as $key) {
                if (!in_array($key, $newKeys)) {
                    // Check if it's in existing replacements (basic check)
                    $fieldName = str_replace(['{{bikefit.', '}}'], '', $key);
                    $commonFields = ['datum', 'testtype', 'lengte_cm', 'binnenbeenlengte_cm', 'opmerkingen', 'zadel_trapas_hoek'];
                    
                    if (!in_array($fieldName, $commonFields)) {
                        $this->error("  🚨 {$key} - ONTBREEKT replacement!");
                    }
                }
            }

            $this->info('');
            $this->info('💡 OPLOSSING:');
            $this->info('Voeg replacements toe voor alle ontbrekende keys met:');
            $this->info('php artisan bonami:add-missing-replacements');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}