<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sjabloon;
use App\Models\SjabloonPage;

class PerformancePulseStandaardSjabloonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Dit is de standaard Performance Pulse sjabloon die gebruikt wordt
     * voor organisaties die GEEN "rapporten_opmaken" feature hebben.
     * 
     * Deze sjabloon gebruikt de {{rapport.*}} variabelen die automatisch
     * worden vervangen met default Performance Pulse waarden.
     */
    public function run(): void
    {
        $this->command->info('🎨 Aanmaken Performance Pulse Standaard Sjabloon...');

        // Maak standaard Bikefit sjabloon
        $bikefitSjabloon = Sjabloon::updateOrCreate(
            [
                'naam' => 'Performance Pulse - Standaard Bikefit',
                'organisatie_id' => null, // NULL = globaal voor iedereen
            ],
            [
                'categorie' => 'bikefit',
                'testtype' => null, // Wildcard voor alle bikefit types
                'beschrijving' => 'Standaard Performance Pulse bikefit rapport met aangepaste branding variabelen',
                'is_actief' => true,
            ]
        );

        // Verwijder oude pagina's
        $bikefitSjabloon->pages()->delete();

        // Pagina 1: Voorblad
        SjabloonPage::create([
            'sjabloon_id' => $bikefitSjabloon->id,
            'page_number' => 1,
            'content' => $this->getVoorbladHtml(),
            'is_url_page' => false,
        ]);

        // Pagina 2: Klantgegevens & Lichaamsmaten
        SjabloonPage::create([
            'sjabloon_id' => $bikefitSjabloon->id,
            'page_number' => 2,
            'content' => $this->getKlantgegevensHtml(),
            'is_url_page' => false,
        ]);

        // Pagina 3: Bikefit Resultaten VOOR
        SjabloonPage::create([
            'sjabloon_id' => $bikefitSjabloon->id,
            'page_number' => 3,
            'content' => $this->getBikefitVoorHtml(),
            'is_url_page' => false,
        ]);

        // Pagina 4: Bikefit Resultaten NA
        SjabloonPage::create([
            'sjabloon_id' => $bikefitSjabloon->id,
            'page_number' => 4,
            'content' => $this->getBikefitNaHtml(),
            'is_url_page' => false,
        ]);

        // Pagina 5: Mobiliteit
        SjabloonPage::create([
            'sjabloon_id' => $bikefitSjabloon->id,
            'page_number' => 5,
            'content' => $this->getMobiliteitHtml(),
            'is_url_page' => false,
        ]);

        // Pagina 6: Laatste blad
        SjabloonPage::create([
            'sjabloon_id' => $bikefitSjabloon->id,
            'page_number' => 6,
            'content' => $this->getLaatsteBladHtml(),
            'is_url_page' => false,
        ]);

        $this->command->info('✅ Performance Pulse Standaard Bikefit Sjabloon aangemaakt!');
    }

    private function getVoorbladHtml()
    {
        return '<div style="font-family: {{rapport.lettertype}}; padding: 40px; text-align: center; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; background: linear-gradient(135deg, {{rapport.primaire_kleur}} 0%, #ffffff 100%);">
    {{rapport.voorblad_foto}}
    <div style="margin: 40px 0;">
        {{rapport.logo}}
    </div>
    <h1 style="font-size: 48px; color: {{rapport.secundaire_kleur}}; margin: 20px 0;">Bikefit Rapport</h1>
    <h2 style="font-size: 32px; color: #666; margin: 10px 0;">{{klant.voornaam}} {{klant.naam}}</h2>
    <p style="font-size: 18px; color: #888; margin: 10px 0;">Datum: {{bikefit.datum}}</p>
    <div style="margin-top: 60px; font-size: 14px; color: #999;">
        {{rapport.inleidende_tekst}}
    </div>
</div>';
    }

    private function getKlantgegevensHtml()
    {
        return '<div style="font-family: {{rapport.lettertype}}; padding: 40px; color: {{rapport.secundaire_kleur}};">
    <h2 style="color: {{rapport.primaire_kleur}}; border-bottom: 3px solid {{rapport.primaire_kleur}}; padding-bottom: 10px;">👤 Klantgegevens</h2>
    
    <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px; font-weight: bold; width: 30%;">Naam:</td>
            <td style="padding: 10px;">{{klant.voornaam}} {{klant.naam}}</td>
        </tr>
        <tr style="background: #f8f9fa;">
            <td style="padding: 10px; font-weight: bold;">Email:</td>
            <td style="padding: 10px;">{{klant.email}}</td>
        </tr>
        <tr>
            <td style="padding: 10px; font-weight: bold;">Telefoon:</td>
            <td style="padding: 10px;">{{klant.telefoonnummer}}</td>
        </tr>
        <tr style="background: #f8f9fa;">
            <td style="padding: 10px; font-weight: bold;">Geboortedatum:</td>
            <td style="padding: 10px;">{{klant.geboortedatum}}</td>
        </tr>
    </table>

    <h2 style="color: {{rapport.primaire_kleur}}; border-bottom: 3px solid {{rapport.primaire_kleur}}; padding-bottom: 10px; margin-top: 40px;">📏 Lichaamsmaten</h2>
    
    $Bikefit.body_measurements_block_html$

    <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-left: 4px solid {{rapport.primaire_kleur}};">
        <h3>Doelstellingen</h3>
        <p>{{bikefit.doelstellingen}}</p>
    </div>
</div>';
    }

    private function getBikefitVoorHtml()
    {
        return '<div style="font-family: {{rapport.lettertype}}; padding: 40px; color: {{rapport.secundaire_kleur}};">
    <h2 style="color: {{rapport.primaire_kleur}}; border-bottom: 3px solid {{rapport.primaire_kleur}}; padding-bottom: 10px;">🔧 Bikefit Metingen VOOR Aanpassing</h2>
    
    $ResultatenVoor$

    <div style="margin-top: 40px;">
        <h3 style="color: {{rapport.primaire_kleur}};">Opmerkingen Huidige Positie</h3>
        <p style="background: #f8f9fa; padding: 15px; border-radius: 8px;">{{bikefit.huidige_positie_opmerkingen}}</p>
    </div>
</div>';
    }

    private function getBikefitNaHtml()
    {
        return '<div style="font-family: {{rapport.lettertype}}; padding: 40px; color: {{rapport.secundaire_kleur}};">
    <h2 style="color: {{rapport.primaire_kleur}}; border-bottom: 3px solid {{rapport.primaire_kleur}}; padding-bottom: 10px;">✅ Bikefit Metingen NA Aanpassing</h2>
    
    $ResultatenNa$

    <h3 style="color: {{rapport.primaire_kleur}}; margin-top: 40px;">📐 Prognose Zitpositie</h3>
    $Bikefit.prognose_zitpositie_html$

    <div style="margin-top: 40px;">
        <h3 style="color: {{rapport.primaire_kleur}};">💡 Aanbevelingen</h3>
        <p style="background: #f8f9fa; padding: 15px; border-radius: 8px;">{{bikefit.aanbevelingen}}</p>
    </div>
</div>';
    }

    private function getMobiliteitHtml()
    {
        return '<div style="font-family: {{rapport.lettertype}}; padding: 40px; color: {{rapport.secundaire_kleur}};">
    <h2 style="color: {{rapport.primaire_kleur}}; border-bottom: 3px solid {{rapport.primaire_kleur}}; padding-bottom: 10px;">🤸 Mobiliteit Resultaten</h2>
    
    $mobiliteitklant$

    <div style="margin-top: 40px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
        <h3>ℹ️ Toelichting Mobiliteit</h3>
        <p>De mobiliteit tests geven inzicht in je flexibiliteit en bewegingsbereik. Scores worden weergegeven van slecht (rood) tot goed (groen).</p>
    </div>
</div>';
    }

    private function getLaatsteBladHtml()
    {
        return '<div style="font-family: {{rapport.lettertype}}; padding: 40px; text-align: center; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between; color: {{rapport.secundaire_kleur}};">
    <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
        <h2 style="color: {{rapport.primaire_kleur}}; font-size: 36px; margin-bottom: 30px;">Bedankt voor je vertrouwen!</h2>
        
        <div style="margin: 40px 0; font-size: 16px; line-height: 1.8;">
            {{rapport.laatste_blad_tekst}}
        </div>

        <div style="margin: 60px auto; max-width: 600px;">
            <h3 style="color: {{rapport.primaire_kleur}}; margin-bottom: 20px;">📞 Contact</h3>
            {{rapport.contactgegevens}}
        </div>

        {{rapport.qr_code}}
    </div>

    <div style="margin-top: 60px; padding-top: 20px; border-top: 2px solid #e5e5e5; font-size: 12px; color: #999;">
        {{rapport.disclaimer}}
        <div style="margin-top: 20px;">
            {{rapport.footer}}
        </div>
    </div>
</div>';
    }
}
