<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TemplateKey;

class AddExistingTemplateKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:add-existing-template-keys';

    /**
     * The console command description.
     */
    protected $description = 'Voeg de bestaande werkende template keys toe aan database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Adding existing working template keys...');

        try {
            // Bestaande keys die al werken (gebaseerd op de sjabloon editor interface)
            $existingWorkingKeys = [
                // Klant keys
                ['key' => '{{klant.voornaam}}', 'description' => 'Voornaam', 'category' => 'klant'],
                ['key' => '{{klant.naam}}', 'description' => 'Achternaam', 'category' => 'klant'],
                ['key' => '{{klant.email}}', 'description' => 'Email', 'category' => 'klant'],
                ['key' => '{{klant.telefoon}}', 'description' => 'Telefoon', 'category' => 'klant'],
                ['key' => '{{klant.adres}}', 'description' => 'Adres', 'category' => 'klant'],
                ['key' => '{{klant.postcode}}', 'description' => 'Postcode', 'category' => 'klant'],
                ['key' => '{{klant.plaats}}', 'description' => 'Plaats', 'category' => 'klant'],
                ['key' => '{{klant.geboortedatum}}', 'description' => 'Geboortedatum', 'category' => 'klant'],

                // Bikefit basis keys (die al werken)
                ['key' => '{{bikefit.datum}}', 'description' => 'Datum', 'category' => 'bikefit'],
                ['key' => '{{bikefit.testtype}}', 'description' => 'Test Type', 'category' => 'bikefit'],
                ['key' => '{{bikefit.lengte_cm}}', 'description' => 'Lengte (cm)', 'category' => 'bikefit'],
                ['key' => '{{bikefit.binnenbeenlengte_cm}}', 'description' => 'Binnenbeenlengte (cm)', 'category' => 'bikefit'],
                ['key' => '{{bikefit.zadel_trapas_hoek}}', 'description' => 'Zadel-trapas Hoek', 'category' => 'bikefit'],
                ['key' => '{{bikefit.zadel_trapas_afstand}}', 'description' => 'Zadel-trapas Afstand', 'category' => 'bikefit'],
                ['key' => '{{bikefit.stuur_trapas_hoek}}', 'description' => 'Stuur-trapas Hoek', 'category' => 'bikefit'],
                ['key' => '{{bikefit.stuur_trapas_afstand}}', 'description' => 'Stuur-trapas Afstand', 'category' => 'bikefit'],
                ['key' => '{{bikefit.opmerkingen}}', 'description' => 'Opmerkingen', 'category' => 'bikefit'],

                // Algemene keys
                ['key' => '{{datum}}', 'description' => 'Huidige Datum', 'category' => 'algemeen'],
                ['key' => '{{tijd}}', 'description' => 'Huidige Tijd', 'category' => 'algemeen'],
            ];

            $addedCount = 0;
            $skippedCount = 0;

            foreach ($existingWorkingKeys as $keyData) {
                // Check if key already exists
                $exists = TemplateKey::where('key', $keyData['key'])->exists();

                if (!$exists) {
                    TemplateKey::create($keyData);
                    $this->info("✅ Added: {$keyData['description']} ({$keyData['key']})");
                    $addedCount++;
                } else {
                    $this->line("⏭️ Skipped: {$keyData['description']} (already exists)");
                    $skippedCount++;
                }
            }

            $this->info('');
            $this->info("🎉 Summary:");
            $this->info("   ✅ Added: {$addedCount} existing template keys");
            $this->info("   ⏭️ Skipped: {$skippedCount} already existing keys");

            $totalKeys = TemplateKey::count();
            $this->info("   📊 Total template keys now: {$totalKeys}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error adding existing template keys: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}