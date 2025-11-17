<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingsZonesTemplate;
use App\Models\Organisatie;

class BonaminZonesSeeder extends Seeder
{
    /**
     * Seed de standaard Bonami trainingszones templates
     */
    public function run(): void
    {
        // Haal de eerste organisatie op (of pas aan naar jouw setup)
        $organisatie = Organisatie::first();
        
        if (!$organisatie) {
            $this->command->warn('Geen organisatie gevonden. Maak eerst een organisatie aan.');
            return;
        }

        // ===== BONAMI STANDAARD ZONES (LT2 BASIS) =====
        $bonamiTemplate = TrainingsZonesTemplate::create([
            'organisatie_id' => $organisatie->id,
            'naam' => 'Bonami Standaard Zones',
            'sport_type' => 'beide',
            'berekening_basis' => 'lt2',
            'beschrijving' => 'Standaard Bonami trainingszones gebaseerd op OBLA/LT2 drempel',
            'is_actief' => true,
            'is_systeem' => true,
        ]);

        // Bonami zones configuratie
        $bonamiZones = [
            ['zone_naam' => 'Recov', 'kleur' => '#E3F2FD', 'min' => 0, 'max' => 60, 'ref' => null],
            ['zone_naam' => 'LSD', 'kleur' => '#90CAF9', 'min' => 60, 'max' => 75, 'ref' => null],
            ['zone_naam' => 'Tempo', 'kleur' => '#64B5F6', 'min' => 75, 'max' => 85, 'ref' => null],
            ['zone_naam' => 'SST', 'kleur' => '#42A5F5', 'min' => 85, 'max' => 95, 'ref' => null],
            ['zone_naam' => 'Threshold', 'kleur' => '#2196F3', 'min' => 95, 'max' => 105, 'ref' => 'LT2'],
            ['zone_naam' => 'VO2max', 'kleur' => '#1976D2', 'min' => 105, 'max' => 120, 'ref' => null],
            ['zone_naam' => 'Anaerobic', 'kleur' => '#0D47A1', 'min' => 120, 'max' => 150, 'ref' => null],
        ];

        foreach ($bonamiZones as $index => $zone) {
            $bonamiTemplate->zones()->create([
                'zone_naam' => $zone['zone_naam'],
                'kleur' => $zone['kleur'],
                'min_percentage' => $zone['min'],
                'max_percentage' => $zone['max'],
                'referentie_waarde' => $zone['ref'],
                'volgorde' => $index,
            ]);
        }

        $this->command->info('✅ Bonami Standaard Zones toegevoegd');

        // ===== KARVONEN HARTSLAG ZONES (MAX BASIS) =====
        $karvonenTemplate = TrainingsZonesTemplate::create([
            'organisatie_id' => $organisatie->id,
            'naam' => 'Karvonen Hartslag Zones',
            'sport_type' => 'beide',
            'berekening_basis' => 'max',
            'beschrijving' => 'Klassieke Karvonen hartslagzones gebaseerd op maximale hartslag',
            'is_actief' => true,
            'is_systeem' => true,
        ]);

        // Karvonen zones configuratie
        $karvonenZones = [
            ['zone_naam' => 'Zone 1 - Herstel', 'kleur' => '#E8F5E9', 'min' => 50, 'max' => 60, 'ref' => null],
            ['zone_naam' => 'Zone 2 - Endurance', 'kleur' => '#A5D6A7', 'min' => 60, 'max' => 70, 'ref' => null],
            ['zone_naam' => 'Zone 3 - Tempo', 'kleur' => '#66BB6A', 'min' => 70, 'max' => 80, 'ref' => null],
            ['zone_naam' => 'Zone 4 - Lactaat', 'kleur' => '#43A047', 'min' => 80, 'max' => 90, 'ref' => null],
            ['zone_naam' => 'Zone 5 - VO2max', 'kleur' => '#2E7D32', 'min' => 90, 'max' => 100, 'ref' => 'MAX'],
        ];

        foreach ($karvonenZones as $index => $zone) {
            $karvonenTemplate->zones()->create([
                'zone_naam' => $zone['zone_naam'],
                'kleur' => $zone['kleur'],
                'min_percentage' => $zone['min'],
                'max_percentage' => $zone['max'],
                'referentie_waarde' => $zone['ref'],
                'volgorde' => $index,
            ]);
        }

        $this->command->info('✅ Karvonen Hartslag Zones toegevoegd');

        // ===== CYCLING POWER ZONES (FTP BASIS) =====
        $ftpTemplate = TrainingsZonesTemplate::create([
            'organisatie_id' => $organisatie->id,
            'naam' => 'Cycling Power Zones (FTP)',
            'sport_type' => 'fietsen',
            'berekening_basis' => 'ftp',
            'beschrijving' => 'Wielrennen vermogenszones gebaseerd op FTP (Functional Threshold Power)',
            'is_actief' => true,
            'is_systeem' => true,
        ]);

        // FTP zones configuratie (zoals Coggan zones)
        $ftpZones = [
            ['zone_naam' => 'Active Recovery', 'kleur' => '#F1F8E9', 'min' => 0, 'max' => 55, 'ref' => null],
            ['zone_naam' => 'Endurance', 'kleur' => '#C5E1A5', 'min' => 55, 'max' => 75, 'ref' => null],
            ['zone_naam' => 'Tempo', 'kleur' => '#9CCC65', 'min' => 75, 'max' => 90, 'ref' => null],
            ['zone_naam' => 'Lactate Threshold', 'kleur' => '#7CB342', 'min' => 90, 'max' => 105, 'ref' => 'FTP'],
            ['zone_naam' => 'VO2max', 'kleur' => '#558B2F', 'min' => 105, 'max' => 120, 'ref' => null],
            ['zone_naam' => 'Anaerobic', 'kleur' => '#33691E', 'min' => 120, 'max' => 150, 'ref' => null],
        ];

        foreach ($ftpZones as $index => $zone) {
            $ftpTemplate->zones()->create([
                'zone_naam' => $zone['zone_naam'],
                'kleur' => $zone['kleur'],
                'min_percentage' => $zone['min'],
                'max_percentage' => $zone['max'],
                'referentie_waarde' => $zone['ref'],
                'volgorde' => $index,
            ]);
        }

        $this->command->info('✅ Cycling Power Zones (FTP) toegevoegd');
        $this->command->info('🎉 Alle standaard zone templates zijn succesvol toegevoegd!');
    }
}
