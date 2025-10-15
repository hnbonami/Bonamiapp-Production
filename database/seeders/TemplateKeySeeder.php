<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TemplateKey;

class TemplateKeySeeder extends Seeder
{
    public function run()
    {
        $keys = [
            // Klant gegevens
            ['key' => 'klant.naam', 'description' => 'Naam van de klant', 'category' => 'klant'],
            ['key' => 'klant.email', 'description' => 'Email van de klant', 'category' => 'klant'],
            ['key' => 'klant.telefoon', 'description' => 'Telefoonnummer van de klant', 'category' => 'klant'],
            ['key' => 'klant.adres', 'description' => 'Adres van de klant', 'category' => 'klant'],
            ['key' => 'klant.postcode', 'description' => 'Postcode van de klant', 'category' => 'klant'],
            ['key' => 'klant.stad', 'description' => 'Stad van de klant', 'category' => 'klant'],
            ['key' => 'klant.geboortedatum', 'description' => 'Geboortedatum van de klant', 'category' => 'klant'],
            ['key' => 'klant.geslacht', 'description' => 'Geslacht van de klant', 'category' => 'klant'],
            ['key' => 'klant.beroep', 'description' => 'Beroep van de klant', 'category' => 'klant'],
            ['key' => 'klant.notities', 'description' => 'Notities over de klant', 'category' => 'klant'],
            
            // Bikefit gegevens - basis
            ['key' => 'bikefit.datum', 'description' => 'Datum van de bikefit', 'category' => 'bikefit'],
            ['key' => 'bikefit.notities', 'description' => 'Notities van de bikefit', 'category' => 'bikefit'],
            ['key' => 'bikefit.type_fiets', 'description' => 'Type fiets', 'category' => 'bikefit'],
            ['key' => 'bikefit.merk_fiets', 'description' => 'Merk van de fiets', 'category' => 'bikefit'],
            ['key' => 'bikefit.model_fiets', 'description' => 'Model van de fiets', 'category' => 'bikefit'],
            
            // Bikefit - lichaamsmaten
            ['key' => 'bikefit.lengte', 'description' => 'Lengte in cm', 'category' => 'bikefit'],
            ['key' => 'bikefit.gewicht', 'description' => 'Gewicht in kg', 'category' => 'bikefit'],
            ['key' => 'bikefit.binnenbeenlengte', 'description' => 'Binnenbeenlengte in cm', 'category' => 'bikefit'],
            ['key' => 'bikefit.armlengte', 'description' => 'Armlengte in cm', 'category' => 'bikefit'],
            ['key' => 'bikefit.romp_lengte', 'description' => 'Romplengte in cm', 'category' => 'bikefit'],
            ['key' => 'bikefit.schouder_breedte', 'description' => 'Schouderbreedte in cm', 'category' => 'bikefit'],
            ['key' => 'bikefit.heup_breedte', 'description' => 'Heupbreedte in cm', 'category' => 'bikefit'],
            
            // Bikefit - flexibiliteit
            ['key' => 'bikefit.flexibiliteit_heup', 'description' => 'Flexibiliteit heup', 'category' => 'bikefit'],
            ['key' => 'bikefit.flexibiliteit_knie', 'description' => 'Flexibiliteit knie', 'category' => 'bikefit'],
            ['key' => 'bikefit.flexibiliteit_enkel', 'description' => 'Flexibiliteit enkel', 'category' => 'bikefit'],
            ['key' => 'bikefit.flexibiliteit_rug', 'description' => 'Flexibiliteit rug', 'category' => 'bikefit'],
            ['key' => 'bikefit.flexibiliteit_nek', 'description' => 'Flexibiliteit nek', 'category' => 'bikefit'],
            
            // Bikefit - fiets afstellingen
            ['key' => 'bikefit.zadel_hoogte', 'description' => 'Zadelhoogte in mm', 'category' => 'bikefit'],
            ['key' => 'bikefit.zadel_setback', 'description' => 'Zadel setback in mm', 'category' => 'bikefit'],
            ['key' => 'bikefit.zadel_tip', 'description' => 'Zadel tip hoek in graden', 'category' => 'bikefit'],
            ['key' => 'bikefit.stuur_hoogte', 'description' => 'Stuurhoogte in mm', 'category' => 'bikefit'],
            ['key' => 'bikefit.stuur_bereik', 'description' => 'Stuurbereik in mm', 'category' => 'bikefit'],
            ['key' => 'bikefit.stem_lengte', 'description' => 'Stemlengte in mm', 'category' => 'bikefit'],
            ['key' => 'bikefit.stem_hoek', 'description' => 'Stemhoek in graden', 'category' => 'bikefit'],
            ['key' => 'bikefit.stuur_breedte', 'description' => 'Stuurbreedte in mm', 'category' => 'bikefit'],
            
            // Bikefit - pedalen en schoenen
            ['key' => 'bikefit.cleats_voor_achter', 'description' => 'Cleats voor/achter positie', 'category' => 'bikefit'],
            ['key' => 'bikefit.cleats_links_rechts', 'description' => 'Cleats links/rechts positie', 'category' => 'bikefit'],
            ['key' => 'bikefit.cleats_hoek', 'description' => 'Cleats hoek', 'category' => 'bikefit'],
            ['key' => 'bikefit.schoen_maat', 'description' => 'Schoenmaat', 'category' => 'bikefit'],
            
            // Inspanningstest gegevens - basis
            ['key' => 'inspanningstest.datum', 'description' => 'Datum van de inspanningstest', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.notities', 'description' => 'Notities van de inspanningstest', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.type_test', 'description' => 'Type inspanningstest', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.duur', 'description' => 'Duur van de test in minuten', 'category' => 'inspanningstest'],
            
            // Inspanningstest - NIEUWE COMPONENT KEYS
            ['key' => '{{INSPANNINGSTEST_ALGEMEEN}}', 'description' => 'Algemene Informatie (klantgegevens, testdatum, lichaamssamenstelling)', 'category' => 'inspanningstest'],
            ['key' => '{{INSPANNINGSTEST_TRAININGSTATUS}}', 'description' => 'Trainingstatus (slaap, eetlust, gevoel, stress)', 'category' => 'inspanningstest'],
            ['key' => '{{INSPANNINGSTEST_TESTRESULTATEN}}', 'description' => 'Testresultaten Tabel (tijd, vermogen, lactaat, hartslag)', 'category' => 'inspanningstest'],
            ['key' => '{{INSPANNINGSTEST_GRAFIEK}}', 'description' => 'Grafiek Analyse (hartslag & lactaat progressie)', 'category' => 'inspanningstest'],
            ['key' => '{{INSPANNINGSTEST_DREMPELS}}', 'description' => 'Drempelwaarden Overzicht (LT1/LT2 tabel)', 'category' => 'inspanningstest'],
            ['key' => '{{INSPANNINGSTEST_ZONES}}', 'description' => 'Trainingszones (Bonami/Karvonen methode)', 'category' => 'inspanningstest'],
            ['key' => '{{INSPANNINGSTEST_AI_ANALYSE}}', 'description' => 'AI Performance Analyse (complete trainingsadvies)', 'category' => 'inspanningstest'],
            
            // Inspanningstest - startwaarden
            ['key' => 'inspanningstest.rust_hartslag', 'description' => 'Rusthartslag', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.rust_bloeddruk_systolisch', 'description' => 'Rust bloeddruk systolisch', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.rust_bloeddruk_diastolisch', 'description' => 'Rust bloeddruk diastolisch', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.lichaamsgewicht', 'description' => 'Lichaamsgewicht in kg', 'category' => 'inspanningstest'],
            
            // Inspanningstest - maximale waarden
            ['key' => 'inspanningstest.max_hartslag', 'description' => 'Maximale hartslag', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.max_vermogen', 'description' => 'Maximaal vermogen in watt', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.max_zuurstofopname', 'description' => 'Maximale zuurstofopname (VO2 max)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.lactaat_drempel', 'description' => 'Lactaatdrempel', 'category' => 'inspanningstest'],
            
            // Inspanningstest - hartslagzones
            ['key' => 'inspanningstest.zone1_ondergrens', 'description' => 'Hartslag zone 1 ondergrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone1_bovengrens', 'description' => 'Hartslag zone 1 bovengrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone2_ondergrens', 'description' => 'Hartslag zone 2 ondergrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone2_bovengrens', 'description' => 'Hartslag zone 2 bovengrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone3_ondergrens', 'description' => 'Hartslag zone 3 ondergrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone3_bovengrens', 'description' => 'Hartslag zone 3 bovengrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone4_ondergrens', 'description' => 'Hartslag zone 4 ondergrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone4_bovengrens', 'description' => 'Hartslag zone 4 bovengrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone5_ondergrens', 'description' => 'Hartslag zone 5 ondergrens', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.zone5_bovengrens', 'description' => 'Hartslag zone 5 bovengrens', 'category' => 'inspanningstest'],
            
            // Inspanningstest - vermogenszones
            ['key' => 'inspanningstest.power_zone1', 'description' => 'Vermogen zone 1 (herstel)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.power_zone2', 'description' => 'Vermogen zone 2 (aërobe basis)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.power_zone3', 'description' => 'Vermogen zone 3 (tempo)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.power_zone4', 'description' => 'Vermogen zone 4 (drempel)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.power_zone5', 'description' => 'Vermogen zone 5 (VO2 max)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.power_zone6', 'description' => 'Vermogen zone 6 (anaërobe capaciteit)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.power_zone7', 'description' => 'Vermogen zone 7 (neuromusculair)', 'category' => 'inspanningstest'],
            
            // Test specifieke metingen
            ['key' => 'inspanningstest.ftp', 'description' => 'Functional Threshold Power (FTP)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.lthr', 'description' => 'Lactate Threshold Heart Rate (LTHR)', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.recovery_hartslag', 'description' => 'Herstel hartslag na 1 minuut', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.eindoordeel', 'description' => 'Eindoordeel van de test', 'category' => 'inspanningstest'],
            ['key' => 'inspanningstest.aanbevelingen', 'description' => 'Trainingsaanbevelingen', 'category' => 'inspanningstest'],
        ];

        foreach ($keys as $key) {
            TemplateKey::updateOrCreate(
                ['key' => $key['key']],
                $key
            );
        }
    }
}