<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisatieRapportInstelling extends Model
{
    use HasFactory;

    protected $table = 'organisatie_rapport_instellingen';

    protected $fillable = [
        'organisatie_id',
        'header_tekst',
        'footer_tekst',
        'logo_path',
        'voorblad_foto_path',
        'inleidende_tekst',
        'laatste_blad_tekst',
        'disclaimer_tekst',
        'primaire_kleur',
        'secundaire_kleur',
        'lettertype',
        'paginanummering_tonen',
        'paginanummering_positie',
        'contact_adres',
        'contact_telefoon',
        'contact_email',
        'contact_website',
        'contactgegevens_in_footer',
        'qr_code_tonen',
        'qr_code_url',
        'qr_code_positie',
    ];

    protected $casts = [
        'paginanummering_tonen' => 'boolean',
        'contactgegevens_in_footer' => 'boolean',
        'qr_code_tonen' => 'boolean',
    ];

    /**
     * Organisatie relatie
     */
    public function organisatie()
    {
        return $this->belongsTo(Organisatie::class);
    }

    /**
     * Get logo URL voor gebruik in rapporten
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    /**
     * Get voorblad foto URL
     */
    public function getVoorbladFotoUrlAttribute()
    {
        return $this->voorblad_foto_path ? asset('storage/' . $this->voorblad_foto_path) : null;
    }

    /**
     * Get contactgegevens als geformatteerde HTML
     */
    public function getContactgegevensHtmlAttribute()
    {
        $html = '';
        
        if ($this->contact_adres) {
            $html .= '<div>' . e($this->contact_adres) . '</div>';
        }
        if ($this->contact_telefoon) {
            $html .= '<div>Tel: ' . e($this->contact_telefoon) . '</div>';
        }
        if ($this->contact_email) {
            $html .= '<div>Email: ' . e($this->contact_email) . '</div>';
        }
        if ($this->contact_website) {
            $html .= '<div>Web: ' . e($this->contact_website) . '</div>';
        }
        
        return $html;
    }

    /**
     * Get default instellingen voor nieuwe organisaties
     */
    public static function getDefaults()
    {
        return [
            'primaire_kleur' => '#c8e1eb',
            'secundaire_kleur' => '#111111',
            'lettertype' => 'Arial',
            'paginanummering_tonen' => true,
            'paginanummering_positie' => 'rechtsonder',
            'contactgegevens_in_footer' => true,
            'qr_code_tonen' => false,
            'qr_code_positie' => 'rechtsonder',
            'header_tekst' => 'Performance Pulse Rapport',
            'footer_tekst' => '© ' . date('Y') . ' Performance Pulse - Sportcoaching',
        ];
    }
}
