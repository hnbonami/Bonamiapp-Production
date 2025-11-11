<?php

namespace App\Services;

use App\Models\OrganisatieRapportInstelling;

class RapportVariabelenService
{
    /**
     * Vervang alle rapport variabelen in content
     */
    /**
     * Haal rapport instellingen op voor een organisatie
     */
    private function getRapportInstellingen(?int $organisatieId = null): array
    {
        \Log::info('🔍 getRapportInstellingen called', [
            'organisatie_id' => $organisatieId
        ]);
        
        // Check of organisatie de feature heeft
        $heeftFeature = false;
        if ($organisatieId) {
            $heeftFeature = \DB::table('organisatie_features')
                ->join('features', 'organisatie_features.feature_id', '=', 'features.id')
                ->where('organisatie_features.organisatie_id', $organisatieId)
                ->where('features.key', 'rapporten_opmaken')
                ->where('organisatie_features.is_actief', true)
                ->exists();
                
            \Log::info('📋 Feature check', [
                'heeft_feature' => $heeftFeature
            ]);
        }
        
        if ($heeftFeature && $organisatieId) {
            // Haal custom instellingen uit database
            $dbInstellingen = \DB::table('organisatie_rapport_instellingen')
                ->where('organisatie_id', $organisatieId)
                ->first();
            
            \Log::info('💾 Database instellingen', [
                'found' => !is_null($dbInstellingen),
                'header' => $dbInstellingen->header_tekst ?? 'NULL',
                'logo_path' => $dbInstellingen->logo_path ?? 'NULL'
            ]);
            
            if ($dbInstellingen) {
                return [
                    'header' => $dbInstellingen->header_tekst ?? config('rapport.header'),
                    'footer' => $dbInstellingen->footer_tekst ?? config('rapport.footer'),
                    'logo_html' => $dbInstellingen->logo_path ? '<div style="margin: 0 -20mm 10px -20mm; padding: 0 20mm; text-align: right;"><img src="' . asset('storage/' . $dbInstellingen->logo_path) . '" alt="Logo" style="max-width: 105px; height: auto; display: inline-block;"></div>' : '',
                    'voorblad_foto_html' => $dbInstellingen->voorblad_foto_path ? '<div style="margin: 20px -20mm; padding: 0; text-align: center;"><img src="' . asset('storage/' . $dbInstellingen->voorblad_foto_path) . '" alt="Voorblad" style="width: 210mm; max-width: 210mm; height: auto; display: block;"></div>' : '',
                    'inleidende_tekst' => $dbInstellingen->inleidende_tekst ?? config('rapport.inleidende_tekst'),
                    'laatste_blad_tekst' => $dbInstellingen->laatste_blad_tekst ?? config('rapport.laatste_blad_tekst'),
                    'disclaimer' => $dbInstellingen->disclaimer_tekst ?? config('rapport.disclaimer'),
                    'primaire_kleur' => $dbInstellingen->primaire_kleur ?? config('rapport.primaire_kleur'),
                    'secundaire_kleur' => $dbInstellingen->secundaire_kleur ?? config('rapport.secundaire_kleur'),
                    'lettertype' => $dbInstellingen->lettertype ?? config('rapport.lettertype'),
                    'contactgegevens_html' => $this->formatContactgegevens($dbInstellingen),
                    'contact_adres' => $dbInstellingen->contact_adres ?? config('rapport.contact_adres'),
                    'contact_telefoon' => $dbInstellingen->contact_telefoon ?? config('rapport.contact_telefoon'),
                    'contact_email' => $dbInstellingen->contact_email ?? config('rapport.contact_email'),
                    'contact_website' => $dbInstellingen->contact_website ?? config('rapport.contact_website'),
                    'qr_code_html' => $dbInstellingen->qr_code_url ? $this->generateQRCode($dbInstellingen->qr_code_url) : '',
                ];
            }
        }
        
        \Log::info('⚠️ Using config defaults', [
            'config_header' => config('rapport.header')
        ]);
        
        // Fallback naar defaults uit config
        return [
            'header' => config('rapport.header'),
            'footer' => config('rapport.footer'),
            'logo_html' => '',
            'voorblad_foto_html' => '',
            'inleidende_tekst' => config('rapport.inleidende_tekst'),
            'laatste_blad_tekst' => config('rapport.laatste_blad_tekst'),
            'disclaimer' => config('rapport.disclaimer'),
            'primaire_kleur' => config('rapport.primaire_kleur'),
            'secundaire_kleur' => config('rapport.secundaire_kleur'),
            'lettertype' => config('rapport.lettertype'),
            'contactgegevens_html' => '',
            'contact_adres' => config('rapport.contact_adres'),
            'contact_telefoon' => config('rapport.contact_telefoon'),
            'contact_email' => config('rapport.contact_email'),
            'contact_website' => config('rapport.contact_website'),
            'qr_code_html' => '',
        ];
    }
    
    private function formatContactgegevens($instellingen): string
    {
        if (!$instellingen) return '';
        
        $html = '<div class="contactgegevens">';
        if ($instellingen->contact_adres) $html .= '<p>' . $instellingen->contact_adres . '</p>';
        if ($instellingen->contact_telefoon) $html .= '<p>Tel: ' . $instellingen->contact_telefoon . '</p>';
        if ($instellingen->contact_email) $html .= '<p>Email: ' . $instellingen->contact_email . '</p>';
        if ($instellingen->contact_website) $html .= '<p>Web: ' . $instellingen->contact_website . '</p>';
        $html .= '</div>';
        
        return $html;
    }
    
    private function generateQRCode(string $url): string
    {
        // Simple QR code implementation - kan later uitgebreid worden
        return '<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($url) . '" alt="QR Code">';
    }
    
    public function vervangRapportVariabelen(string $content, ?int $organisatieId = null, int $pageNumber = 1): string
    {
        // Haal rapport instellingen op
        $instellingen = $this->getRapportInstellingen($organisatieId);
        
        \Log::info('🔍 RapportVariabelenService::vervangRapportVariabelen', [
            'organisatie_id' => $organisatieId,
            'page_number' => $pageNumber,
            'heeft_instellingen' => !is_null($instellingen),
            'content_preview' => substr($content, 0, 200),
            'contains_single_brace' => strpos($content, '{rapport.') !== false,
            'contains_double_brace' => strpos($content, '{{rapport.') !== false
        ]);
        
        // ONDERSTEUN BEIDE FORMATEN: {{rapport.xxx}} EN {rapport.xxx}
        $replacements = [
            // Dubbele curly braces (Blade-safe format)
            '{{rapport.header}}' => $instellingen['header'] ?? '',
            '{{rapport.footer}}' => $instellingen['footer'] ?? '',
            '{{rapport.logo}}' => $instellingen['logo_html'] ?? '',
            '{{rapport.voorblad_foto}}' => $instellingen['voorblad_foto_html'] ?? '',
            '{{rapport.inleidende_tekst}}' => $instellingen['inleidende_tekst'] ?? '',
            '{{rapport.laatste_blad_tekst}}' => $instellingen['laatste_blad_tekst'] ?? '',
            '{{rapport.disclaimer}}' => $instellingen['disclaimer'] ?? '',
            '{{rapport.primaire_kleur}}' => $instellingen['primaire_kleur'] ?? '',
            '{{rapport.secundaire_kleur}}' => $instellingen['secundaire_kleur'] ?? '',
            '{{rapport.lettertype}}' => $instellingen['lettertype'] ?? '',
            '{{rapport.contactgegevens}}' => $instellingen['contactgegevens_html'] ?? '',
            '{{rapport.contact_adres}}' => $instellingen['contact_adres'] ?? '',
            '{{rapport.contact_telefoon}}' => $instellingen['contact_telefoon'] ?? '',
            '{{rapport.contact_email}}' => $instellingen['contact_email'] ?? '',
            '{{rapport.contact_website}}' => $instellingen['contact_website'] ?? '',
            '{{rapport.qr_code}}' => $instellingen['qr_code_html'] ?? '',
            '{{rapport.paginanummer}}' => (string)$pageNumber,
            
            // Enkele curly braces (legacy format)
            '{rapport.header}' => $instellingen['header'] ?? '',
            '{rapport.footer}' => $instellingen['footer'] ?? '',
            '{rapport.logo}' => $instellingen['logo_html'] ?? '',
            '{rapport.voorblad_foto}' => $instellingen['voorblad_foto_html'] ?? '',
            '{rapport.inleidende_tekst}' => $instellingen['inleidende_tekst'] ?? '',
            '{rapport.laatste_blad_tekst}' => $instellingen['laatste_blad_tekst'] ?? '',
            '{rapport.disclaimer}' => $instellingen['disclaimer'] ?? '',
            '{rapport.primaire_kleur}' => $instellingen['primaire_kleur'] ?? '',
            '{rapport.secundaire_kleur}' => $instellingen['secundaire_kleur'] ?? '',
            '{rapport.lettertype}' => $instellingen['lettertype'] ?? '',
            '{rapport.contactgegevens}' => $instellingen['contactgegevens_html'] ?? '',
            '{rapport.contact_adres}' => $instellingen['contact_adres'] ?? '',
            '{rapport.contact_telefoon}' => $instellingen['contact_telefoon'] ?? '',
            '{rapport.contact_email}' => $instellingen['contact_email'] ?? '',
            '{rapport.contact_website}' => $instellingen['contact_website'] ?? '',
            '{rapport.qr_code}' => $instellingen['qr_code_html'] ?? '',
            '{rapport.paginanummer}' => (string)$pageNumber,
        ];
        
        \Log::info('📝 Vervangingen worden toegepast', [
            'aantal_replacements' => count($replacements),
            'header_waarde' => $instellingen['header'] ?? 'LEEG',
            'logo_waarde_lengte' => strlen($instellingen['logo_html'] ?? ''),
        ]);
        
        // Vervang alle variabelen
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        \Log::info('✅ Vervangingen voltooid', [
            'contains_rapport_after' => strpos($content, 'rapport.') !== false,
            'content_length' => strlen($content)
        ]);
        
        return $content;
    }

    /**
     * Vervang met default Performance Pulse waarden
     */
    private function vervangMetDefaults($content, $paginaNummer = null)
    {
        $defaults = OrganisatieRapportInstelling::getDefaults();
        
        $content = str_replace('{{rapport.header}}', $defaults['header_tekst'], $content);
        $content = str_replace('{{rapport.footer}}', $defaults['footer_tekst'], $content);
        $content = str_replace('{{rapport.inleidende_tekst}}', '', $content);
        $content = str_replace('{{rapport.laatste_blad_tekst}}', '', $content);
        $content = str_replace('{{rapport.disclaimer}}', '', $content);
        $content = str_replace('{{rapport.primaire_kleur}}', $defaults['primaire_kleur'], $content);
        $content = str_replace('{{rapport.secundaire_kleur}}', $defaults['secundaire_kleur'], $content);
        $content = str_replace('{{rapport.lettertype}}', $defaults['lettertype'], $content);
        $content = str_replace('{{rapport.contactgegevens}}', '', $content);
        $content = str_replace('{{rapport.contact_adres}}', '', $content);
        $content = str_replace('{{rapport.contact_telefoon}}', '', $content);
        $content = str_replace('{{rapport.contact_email}}', '', $content);
        $content = str_replace('{{rapport.contact_website}}', '', $content);
        $content = str_replace('{{rapport.logo}}', '', $content);
        $content = str_replace('{{rapport.voorblad_foto}}', '', $content);
        $content = str_replace('{{rapport.qr_code}}', '', $content);
        
        if ($paginaNummer !== null) {
            $content = str_replace('{{rapport.paginanummer}}', (string)$paginaNummer, $content);
        } else {
            $content = str_replace('{{rapport.paginanummer}}', '', $content);
        }

        return $content;
    }

    /**
     * Check of organisatie custom rapporten heeft
     */
    public function hasCustomRapporten($organisatieId)
    {
        if (!$organisatieId) {
            return false;
        }

        $instellingen = OrganisatieRapportInstelling::where('organisatie_id', $organisatieId)->first();
        
        return $instellingen !== null;
    }
}
