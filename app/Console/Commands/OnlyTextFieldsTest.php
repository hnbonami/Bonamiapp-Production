<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bikefit;

class OnlyTextFieldsTest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:only-text-fields';

    /**
     * The console command description.
     */
    protected $description = 'Vul alleen TEXT velden voor veilige template testing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Vullen van alleen TEXT velden voor template test...');

        try {
            $bikefit = Bikefit::find(13);
            
            if (!$bikefit) {
                $this->error("Bikefit ID 13 niet gevonden");
                return Command::FAILURE;
            }
            
            $this->info("Using bikefit ID: {$bikefit->id}");
            
            // ALLEEN TEXT velden - geen decimals of integers
            $testData = [
                'type_fitting' => 'TEST: Professionele Bikefit',
                'type_fiets' => 'TEST: Racefiets Carbon',
                'frametype' => 'TEST: Carbon Monocoque',
                'type_zadel' => 'TEST: Fizik Arione R3',
                'nieuw_testzadel' => 'TEST: Selle Italia SLR Boost',
                'lenigheid_hamstrings' => 'TEST: Goed - geen beperkingen',
                'voetpositie' => 'TEST: Neutraal gepositioneerd',
                'rotatie_aanpassingen' => 'TEST: 2° naar binnen links',
                'inclinatie_aanpassingen' => 'TEST: 1° naar beneden aangepast',
                'aanpassingen_stuurpen_aan' => 'TEST: Ja - aangepast',
                'aanpassingen_stuurpen_pre' => 'TEST: 100mm origineel',
                'aanpassingen_stuurpen_post' => 'TEST: 110mm na aanpassing',
                'zadeltil' => 'TEST: 0.5° naar beneden',
                'zadelbreedte' => 'TEST: 143mm breed',
                'one_leg_squat_links' => 'TEST: Stabiel - geen compensatie',
                'one_leg_squat_rechts' => 'TEST: Lichte knie compensatie',
                'straight_leg_raise_links' => 'TEST: 75° flexibiliteit',
                'straight_leg_raise_rechts' => 'TEST: 78° flexibiliteit',
                'knieflexie_links' => 'TEST: 120° maximale flexie',
                'knieflexie_rechts' => 'TEST: 118° maximale flexie',
                'heup_endorotatie_links' => 'TEST: 45° interne rotatie',
                'heup_endorotatie_rechts' => 'TEST: 42° interne rotatie',
                'heup_exorotatie_links' => 'TEST: 50° externe rotatie',
                'heup_exorotatie_rechts' => 'TEST: 52° externe rotatie',
                'enkeldorsiflexie_links' => 'TEST: 15° dorsiflexie',
                'enkeldorsiflexie_rechts' => 'TEST: 18° dorsiflexie',
                'interne_opmerkingen' => 'TEST DATA VOOR TEMPLATE: Alle nieuwe velden zijn gevuld met test data om template replacement te verifiëren.'
            ];
            
            // Update alleen veilige tekst velden
            foreach ($testData as $field => $value) {
                $bikefit->$field = $value;
                $this->line("  ✅ {$field}: '{$value}'");
            }
            
            $bikefit->save();
            
            $this->info('');
            $this->info('🎉 SUCCESS: Alleen TEXT velden gevuld zonder database errors!');
            $this->info('');
            $this->info('🎯 NU TESTEN:');
            $this->info('1. Ga naar bikefit ID 13 en genereer rapport');
            $this->info('2. Zoek naar "TEST:" in het rapport');
            $this->info('3. Als je "TEST: Professionele Bikefit" ziet i.p.v. {{bikefit.type_fitting}} → WERKT!');
            $this->info('4. Als je nog {{bikefit.type_fitting}} ziet → WERKT NIET!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}