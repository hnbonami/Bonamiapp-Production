<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bikefit;

class TestOpmerkingField extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:test-opmerking';

    /**
     * The console command description.
     */
    protected $description = 'Test alleen het opmerkingen veld voor template replacement';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Test alleen opmerkingen veld...');

        try {
            $bikefit = Bikefit::find(13);
            
            if (!$bikefit) {
                $this->error("Bikefit ID 13 niet gevonden");
                return Command::FAILURE;
            }
            
            // Update alleen het opmerkingen veld (dit weten we dat TEXT is)
            $bikefit->opmerkingen = '🔥 TEMPLATE TEST: Als je dit ziet in het rapport, dan werkt {{bikefit.opmerkingen}} replacement!';
            $bikefit->save();
            
            $this->info('✅ Bikefit opmerkingen aangepast naar test waarde');
            $this->info('');
            $this->info('🎯 TEST NU:');
            $this->info('1. Genereer rapport voor bikefit ID 13');
            $this->info('2. Zoek naar "🔥 TEMPLATE TEST"');
            $this->info('3. Als je de vuurtje emoji ziet → replacement werkt voor bestaande velden');
            $this->info('4. Als je nog {{bikefit.opmerkingen}} ziet → replacement werkt helemaal niet');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}