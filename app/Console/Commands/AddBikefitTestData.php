<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bikefit;

class AddBikefitTestData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:add-bikefit-test-data {bikefit_id?}';

    /**
     * The console command description.
     */
    protected $description = 'Voeg test data toe aan een bikefit record om template replacement te testen';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Adding test data to bikefit...');

        try {
            $bikefitId = $this->argument('bikefit_id');
            
            if (!$bikefitId) {
                $bikefit = Bikefit::first();
                if (!$bikefit) {
                    $this->error('Geen bikefit records gevonden');
                    return Command::FAILURE;
                }
                $bikefitId = $bikefit->id;
            } else {
                $bikefit = Bikefit::find($bikefitId);
                if (!$bikefit) {
                    $this->error("Bikefit ID {$bikefitId} niet gevonden");
                    return Command::FAILURE;
                }
            }
            
            $this->info("Using bikefit ID: {$bikefit->id}");
            
            // Vul nieuwe velden met test data (database-vriendelijke waarden)
            $testData = [
                'aanpassingen_zadel' => '+2.5cm',
                'aanpassingen_setback' => '+1.0cm', 
                'aanpassingen_reach' => '-0.5cm',
                'aanpassingen_drop' => '+1.5cm',
                'aanpassingen_stuurpen_aan' => 'Ja',
                'aanpassingen_stuurpen_pre' => '100mm',
                'aanpassingen_stuurpen_post' => '110mm',
                'rotatie_aanpassingen' => '2° naar binnen',
                'inclinatie_aanpassingen' => '1° naar beneden',
                'ophoging_li' => '2',
                'ophoging_re' => '0',
                'type_fitting' => 'Professionele Bikefit',
                'bouwjaar' => 2023,
                'type_fiets' => 'Racefiets',
                'frametype' => 'Carbon',
                'armlengte_cm' => 68,
                'romplengte_cm' => 52,
                'schouderbreedte_cm' => 42,
                'zadel_lengte_center_top' => 28.5,
                'type_zadel' => 'Fizik Arione',
                'zadeltil' => '0.5°',
                'zadelbreedte' => '143mm',
                'nieuw_testzadel' => 'Selle Italia SLR',
                'lenigheid_hamstrings' => 'Goed',
                'voetpositie' => 'Neutraal',
                'straight_leg_raise_links' => '75°',
                'straight_leg_raise_rechts' => '78°',
                'knieflexie_links' => '120°',
                'knieflexie_rechts' => '118°',
                'heup_endorotatie_links' => '45°',
                'heup_endorotatie_rechts' => '42°',
                'heup_exorotatie_links' => '50°',
                'heup_exorotatie_rechts' => '52°',
                'enkeldorsiflexie_links' => '15°',
                'enkeldorsiflexie_rechts' => '18°',
                'one_leg_squat_links' => 'Stabiel',
                'one_leg_squat_rechts' => 'Lichte compensatie',
                'interne_opmerkingen' => 'Goede vooruitgang sinds vorige bikefit. Klant tevreden met aanpassingen.'
            ];
            
            // Backup huidige data
            $originalData = [];
            foreach ($testData as $field => $value) {
                $originalData[$field] = $bikefit->$field;
            }
            
            $this->info('📄 Backup van originele data gemaakt');
            
            // Update bikefit met test data
            foreach ($testData as $field => $value) {
                $bikefit->$field = $value;
                $this->line("  {$field}: '{$value}'");
            }
            
            $bikefit->save();
            
            $this->info('✅ Test data toegevoegd aan bikefit ID: ' . $bikefit->id);
            $this->info('');
            $this->info('🎯 Test nu je template replacement door:');
            $this->info('1. Een rapport te genereren voor deze bikefit');
            $this->info('2. Te kijken of de nieuwe velden nu echte data tonen');
            $this->info('');
            $this->info('🔄 Om de originele data te herstellen, run:');
            $this->info("php artisan bonami:restore-bikefit-data {$bikefit->id}");
            
            // Sla backup op in cache voor later herstel
            cache()->put("bikefit_backup_{$bikefit->id}", $originalData, now()->addHours(24));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}