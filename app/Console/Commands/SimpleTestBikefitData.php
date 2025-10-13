<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bikefit;

class SimpleTestBikefitData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:simple-test-data {bikefit_id?}';

    /**
     * The console command description.
     */
    protected $description = 'Voeg eenvoudige test data toe (alleen tekst velden) voor template testing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Adding simple test data (text fields only)...');

        try {
            $bikefitId = $this->argument('bikefit_id') ?? 13;
            $bikefit = Bikefit::find($bikefitId);
            
            if (!$bikefit) {
                $this->error("Bikefit ID {$bikefitId} niet gevonden");
                return Command::FAILURE;
            }
            
            $this->info("Using bikefit ID: {$bikefit->id}");
            
            // Alleen tekst velden om database issues te vermijden
            $testData = [
                'type_fitting' => 'Professionele Bikefit Test',
                'type_fiets' => 'Racefiets Carbon',
                'frametype' => 'Carbon Monocoque',
                'type_zadel' => 'Fizik Arione R3',
                'nieuw_testzadel' => 'Selle Italia SLR Boost',
                'lenigheid_hamstrings' => 'Goed - geen beperkingen',
                'voetpositie' => 'Neutraal gepositioneerd',
                'rotatie_aanpassingen' => '2° naar binnen links, 1° naar binnen rechts',
                'inclinatie_aanpassingen' => '1° naar beneden aangepast',
                'aanpassingen_stuurpen_aan' => 'Ja - aangepast',
                'aanpassingen_stuurpen_pre' => '100mm origineel',
                'aanpassingen_stuurpen_post' => '110mm na aanpassing',
                'aanpassingen_zadel' => '+2.5cm omhoog',
                'aanpassingen_setback' => '+1.0cm naar achteren',
                'aanpassingen_reach' => '-0.5cm korter',
                'aanpassingen_drop' => '+1.5cm meer drop',
                'zadeltil' => '0.5° naar beneden',
                'zadelbreedte' => '143mm breed',
                'one_leg_squat_links' => 'Stabiel - geen compensatie',
                'one_leg_squat_rechts' => 'Lichte knie compensatie',
                'straight_leg_raise_links' => '75° flexibiliteit',
                'straight_leg_raise_rechts' => '78° flexibiliteit',
                'knieflexie_links' => '120° maximale flexie',
                'knieflexie_rechts' => '118° maximale flexie',
                'heup_endorotatie_links' => '45° interne rotatie',
                'heup_endorotatie_rechts' => '42° interne rotatie',
                'heup_exorotatie_links' => '50° externe rotatie',
                'heup_exorotatie_rechts' => '52° externe rotatie',
                'enkeldorsiflexie_links' => '15° dorsiflexie',
                'enkeldorsiflexie_rechts' => '18° dorsiflexie',
                'interne_opmerkingen' => 'TESTDATA: Goede vooruitgang sinds vorige bikefit. Klant zeer tevreden met alle aanpassingen. Nieuwe positie voelt veel natuurlijker aan.'
            ];
            
            // Backup huidige data
            $originalData = [];
            foreach ($testData as $field => $value) {
                $originalData[$field] = $bikefit->$field;
            }
            
            $this->info('📄 Backup van originele data gemaakt');
            
            // Update alleen de tekst velden
            foreach ($testData as $field => $value) {
                $bikefit->$field = $value;
                $this->line("  {$field}: '{$value}'");
            }
            
            $bikefit->save();
            
            $this->info('✅ Eenvoudige test data succesvol toegevoegd!');
            $this->info('');
            $this->info('🎯 Test nu je template replacement:');
            $this->info('1. Ga naar bikefit ID 13 en genereer een rapport');
            $this->info('2. Kijk of deze velden nu echte data tonen:');
            $this->info('   - {{bikefit.aanpassingen_zadel}} → "+2.5cm omhoog"');
            $this->info('   - {{bikefit.aanpassingen_stuurpen_pre}} → "100mm origineel"');
            $this->info('   - {{bikefit.rotatie_aanpassingen}} → "2° naar binnen..."');
            $this->info('   - {{bikefit.interne_opmerkingen}} → "TESTDATA: Goede vooruitgang..."');
            
            // Sla backup op
            cache()->put("simple_bikefit_backup_{$bikefit->id}", $originalData, now()->addHours(24));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}