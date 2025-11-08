<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organisatie;
use App\Models\OrganisatieBranding;

class PerformancePulseDefaultBrandingSeeder extends Seeder
{
    /**
     * Seed Performance Pulse default branding (organisatie ID 1)
     * Deze wordt gebruikt als template voor alle nieuwe organisaties
     */
    public function run(): void
    {
        // Zorg dat organisatie ID 1 bestaat (Performance Pulse master)
        $masterOrganisatie = Organisatie::firstOrCreate(
            ['id' => 1],
            [
                'naam' => 'Performance Pulse',
                'email' => 'info@performancepulse.be',
                'status' => 'actief',
                'subscription_tier' => 'enterprise',
                'branding_enabled' => true,
                'primary_color' => '#7fb432',
                'secondary_color' => '#6a9929',
                'sidebar_color' => '#FFFFFF',
                'text_color' => '#374151',
            ]
        );
        
        // Seed default Performance Pulse branding
        OrganisatieBranding::updateOrCreate(
            [
                'organisatie_id' => 1,
            ],
            [
                'is_actief' => true,
                
                // Navbar kleuren
                'navbar_achtergrond' => '#c8e1eb',
                'navbar_tekst_kleur' => '#000000',
                
                // Sidebar kleuren
                'sidebar_achtergrond' => '#FFFFFF',
                'sidebar_tekst_kleur' => '#374151',
                'sidebar_actief_achtergrond' => '#f6fbfe',
                'sidebar_actief_lijn' => '#c1dfeb',
                
                // Dark mode kleuren
                'dark_achtergrond' => '#1F2937',
                'dark_tekst' => '#F9FAFB',
                'dark_navbar_achtergrond' => '#111827',
                'dark_sidebar_achtergrond' => '#111827',
                
                // Login pagina kleuren
                'login_text_color' => '#374151',
                'login_button_color' => '#7fb432',
                'login_button_hover_color' => '#6a9929',
                'login_link_color' => '#374151',
                
                // Logo paths (voeg toe indien je default logo's hebt)
                'logo_pad' => null, // Of: 'branding/performancepulse-logo.png'
                'login_logo' => null, // Of: 'branding/performancepulse-login-logo.png'
                'login_background_image' => null,
                'login_background_video' => null,
            ]
        );
        
        $this->command->info('✅ Performance Pulse default branding geseeded voor organisatie ID 1');
    }
}