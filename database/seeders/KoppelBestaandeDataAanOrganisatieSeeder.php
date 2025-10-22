<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organisatie;
use App\Models\Bikefit;
use App\Models\Inspanningstest;
use App\Models\Sjabloon;
use App\Models\Testzadel;
use App\Models\Medewerker;

class KoppelBestaandeDataAanOrganisatieSeeder extends Seeder
{
    public function run(): void
    {
        // Haal Bonami organisatie op
        $bonami = Organisatie::where('naam', 'Bonami Sportcoaching')->first();
        
        if (!$bonami) {
            $this->command->error('❌ Bonami Sportcoaching organisatie niet gevonden!');
            $this->command->info('💡 Draai eerst: php artisan db:seed --class=OrganisatieSeeder');
            return;
        }

        // Koppel bikefits
        $bikefitsCount = Bikefit::whereNull('organisatie_id')->update(['organisatie_id' => $bonami->id]);
        $this->command->info("✓ {$bikefitsCount} bikefits gekoppeld aan Bonami");

        // Koppel inspanningstests
        $testsCount = Inspanningstest::whereNull('organisatie_id')->update(['organisatie_id' => $bonami->id]);
        $this->command->info("✓ {$testsCount} inspanningstests gekoppeld aan Bonami");

        // Koppel sjablonen
        $sjablonenCount = Sjabloon::whereNull('organisatie_id')->update(['organisatie_id' => $bonami->id]);
        $this->command->info("✓ {$sjablonenCount} sjablonen gekoppeld aan Bonami");

        // Koppel testzadels
        $testzadelsCount = Testzadel::whereNull('organisatie_id')->update(['organisatie_id' => $bonami->id]);
        $this->command->info("✓ {$testzadelsCount} testzadels gekoppeld aan Bonami");

        // Koppel medewerkers (als tabel bestaat)
        try {
            $medewerkersCount = Medewerker::whereNull('organisatie_id')->update(['organisatie_id' => $bonami->id]);
            $this->command->info("✓ {$medewerkersCount} medewerkers gekoppeld aan Bonami");
        } catch (\Exception $e) {
            $this->command->warn("⚠ Medewerkers tabel niet gevonden of al gekoppeld");
        }

        $this->command->info('');
        $this->command->info('🎉 Alle bestaande data succesvol gekoppeld aan Bonami Sportcoaching!');
    }
}
