<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrganisatieBranding extends Model
{
    protected $table = 'organisatie_brandings';
    
    protected $fillable = [
        'organisatie_id',
        'logo_pad',
        'logo_klein_pad',
        'rapport_logo_pad',
        'primaire_kleur',
        'primaire_kleur_hover',
        'primaire_kleur_licht',
        'secundaire_kleur',
        'accent_kleur',
        'achtergrond_kleur',
        'kaart_achtergrond',
        'tekst_kleur_primair',
        'tekst_kleur_secundair',
        'font_familie',
        'font_grootte_basis',
        'rapport_achtergrond',
        'rapport_footer_tekst',
        'toon_logo_in_rapporten',
        'navbar_achtergrond',
        'navbar_tekst_kleur',
        'is_actief',
        'custom_css',
        // Nieuwe sidebar kleuren
        'sidebar_achtergrond',
        'sidebar_tekst_kleur',
        'sidebar_actief_achtergrond',
        'sidebar_actief_lijn',
        // Nieuwe dark mode kleuren
        'dark_achtergrond',
        'dark_tekst',
        'dark_navbar_achtergrond',
        'dark_sidebar_achtergrond',
        // Login branding velden
        'login_background_image',
        'login_text_color',
        'login_button_color',
        'login_button_hover_color',
        'login_link_color',
    ];
    
    protected $casts = [
        'toon_logo_in_rapporten' => 'boolean',
        'is_actief' => 'boolean',
        'custom_css' => 'array',
    ];
    
    /**
     * Relatie naar organisatie
     */
    public function organisatie()
    {
        return $this->belongsTo(Organisatie::class);
    }
    
    /**
     * Haal volledige URL van logo op
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo_pad) {
            return asset('logo_bonami.png'); // Default logo
        }
        
        return Storage::url($this->logo_pad);
    }
    
    /**
     * Haal volledige URL van klein logo op
     */
    public function getLogoSmallUrlAttribute()
    {
        if (!$this->logo_klein_pad) {
            return $this->logo_url; // Fallback naar normaal logo
        }
        
        return Storage::url($this->logo_klein_pad);
    }
    
    /**
     * Haal volledige URL van rapport logo op
     */
    public function getRapportLogoUrlAttribute()
    {
        if (!$this->rapport_logo_pad) {
            return $this->logo_url; // Fallback naar normaal logo
        }
        
        return Storage::url($this->rapport_logo_pad);
    }
    
    /**
     * Genereer CSS variabelen voor custom styling
     */
    public function getCssVariables()
    {
        return [
            '--primary-color' => $this->primaire_kleur ?? '#3B82F6',
            '--primary-color-hover' => $this->primaire_kleur_hover ?? '#2563EB',
            '--primary-color-light' => $this->primaire_kleur_licht ?? '#DBEAFE',
            '--secondary-color' => $this->secundaire_kleur ?? '#1E40AF',
            '--accent-color' => $this->accent_kleur ?? '#10B981',
            '--text-color' => $this->tekst_kleur_primair ?? '#1F2937',
            '--text-color-secondary' => $this->tekst_kleur_secundair ?? '#6B7280',
            '--background-color' => $this->achtergrond_kleur ?? '#FFFFFF',
            '--card-background' => $this->kaart_achtergrond ?? '#F9FAFB',
            '--heading-font' => 'Inter', // Hardcoded fallback
            '--body-font' => 'Inter', // Hardcoded fallback
        ];
    }
    
    /**
     * Check of organisatie custom branding heeft
     */
    public static function hasCustomBranding($organisatieId)
    {
        return self::where('organisatie_id', $organisatieId)->exists();
    }
    
    /**
     * Haal branding op of maak default aan
     */
    public static function getOrCreateForOrganisatie($organisatieId)
    {
        return self::firstOrCreate(
            ['organisatie_id' => $organisatieId],
            [
                // Basis kleuren
                'primaire_kleur' => '#3B82F6',
                'primaire_kleur_hover' => '#2563EB',
                'primaire_kleur_licht' => '#DBEAFE',
                'secundaire_kleur' => '#6B7280',
                'accent_kleur' => '#10B981',
                'achtergrond_kleur' => '#F9FAFB',
                'kaart_achtergrond' => '#FFFFFF',
                'tekst_kleur_primair' => '#111827',
                'tekst_kleur_secundair' => '#6B7280',
                
                // Navbar kleuren
                'navbar_achtergrond' => '#1f2937',
                'navbar_tekst_kleur' => '#ffffff',
                
                // Sidebar kleuren (COMPLETE defaults)
                'sidebar_achtergrond' => '#ffffff',
                'sidebar_tekst_kleur' => '#374151',
                'sidebar_actief_achtergrond' => '#f6fbfe',
                'sidebar_actief_lijn' => '#c1dfeb',
                
                // Dark mode kleuren (COMPLETE defaults)
                'dark_achtergrond' => '#1f2937',
                'dark_tekst' => '#f9fafb',
                'dark_navbar_achtergrond' => '#111827',
                'dark_sidebar_achtergrond' => '#111827',
                
                // Typografie
                'font_familie' => 'Inter',
                'font_grootte_basis' => 16,
                
                // Rapport settings
                'rapport_achtergrond' => '#FFFFFF',
                'toon_logo_in_rapporten' => true,
                
                // Status
                'is_actief' => false,
            ]
        );
    }
    
    // Backwards compatibility: aliassen voor Engelse property namen
    public function getPrimaryColorAttribute()
    {
        return $this->primaire_kleur;
    }
    
    public function getSecondaryColorAttribute()
    {
        return $this->secundaire_kleur;
    }
    
    public function getAccentColorAttribute()
    {
        return $this->accent_kleur;
    }
    
    public function getTextColorAttribute()
    {
        return $this->tekst_kleur_primair;
    }
    
    public function getBackgroundColorAttribute()
    {
        return $this->achtergrond_kleur;
    }
    
    public function getLogoPathAttribute()
    {
        return $this->logo_pad;
    }
    
    // Accessors voor niet-bestaande kolommen (backwards compatibility)
    public function getCompanyNameAttribute()
    {
        return null; // Kolom 'bedrijfsnaam' bestaat niet in DB
    }
    
    public function getTaglineAttribute()
    {
        return null; // Kolom bestaat niet
    }
    
    public function getRapportHeaderAttribute()
    {
        return $this->rapport_footer_tekst; // Gebruik footer tekst als header fallback
    }
    
    public function getRapportFooterAttribute()
    {
        return $this->rapport_footer_tekst;
    }
    
    // Font accessors (gebruik echte database kolommen)
    public function getHeadingFontAttribute()
    {
        return $this->font_familie ?? 'Inter';
    }
    
    public function getBodyFontAttribute()
    {
        return $this->font_familie ?? 'Inter';
    }
}
