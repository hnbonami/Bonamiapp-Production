<?php

namespace App\Services;

use App\Models\OrganisatieRapportInstelling;

class RapportVariabelenService
{
    /**
     * Vervang alle rapport variabelen in content
     */
    public function vervangRapportVariabelen($content, $organisatieId = null, $paginaNummer = null)
    {
        // Haal organisatie ID op van ingelogde user als niet meegegeven
        if (!$organisatieId) {
            $user = auth()->user();
            $organisatieId = $user ? $user->organisatie_id : null;
        }

        if (!$organisatieId) {
            // Geen organisatie, gebruik default Performance Pulse waarden
            return $this->vervangMetDefaults($content, $paginaNummer);
        }

        // Haal rapport instellingen op
        $instellingen = OrganisatieRapportInstelling::where('organisatie_id', $organisatieId)->first();

        if (!$instellingen) {
            // Geen instellingen, gebruik defaults
            return $this->vervangMetDefaults($content, $paginaNummer);
        }

        // Vervang alle variabelen
        $content = str_replace('{{rapport.header}}', $instellingen->header_tekst ?? '', $content);
        $content = str_replace('{{rapport.footer}}', $instellingen->footer_tekst ?? '', $content);
        $content = str_replace('{{rapport.inleidende_tekst}}', $instellingen->inleidende_tekst ?? '', $content);
        $content = str_replace('{{rapport.laatste_blad_tekst}}', $instellingen->laatste_blad_tekst ?? '', $content);
        $content = str_replace('{{rapport.disclaimer}}', $instellingen->disclaimer_tekst ?? '', $content);
        $content = str_replace('{{rapport.primaire_kleur}}', $instellingen->primaire_kleur ?? '#c8e1eb', $content);
        $content = str_replace('{{rapport.secundaire_kleur}}', $instellingen->secundaire_kleur ?? '#111111', $content);
        $content = str_replace('{{rapport.lettertype}}', $instellingen->lettertype ?? 'Arial', $content);
        
        // Contactgegevens
        $content = str_replace('{{rapport.contact_adres}}', $instellingen->contact_adres ?? '', $content);
        $content = str_replace('{{rapport.contact_telefoon}}', $instellingen->contact_telefoon ?? '', $content);
        $content = str_replace('{{rapport.contact_email}}', $instellingen->contact_email ?? '', $content);
        $content = str_replace('{{rapport.contact_website}}', $instellingen->contact_website ?? '', $content);
        $content = str_replace('{{rapport.contactgegevens}}', $instellingen->contactgegevens_html ?? '', $content);
        
        // Logo (als IMG tag)
        $logoHtml = '';
        if ($instellingen->logo_path) {
            $logoUrl = asset('storage/' . $instellingen->logo_path);
            $logoHtml = '<img src="' . $logoUrl . '" alt="Logo" style="max-height: 80px; height: auto;">';
        }
        $content = str_replace('{{rapport.logo}}', $logoHtml, $content);
        
        // Voorblad foto (als IMG tag)
        $voorbladHtml = '';
        if ($instellingen->voorblad_foto_path) {
            $voorbladUrl = asset('storage/' . $instellingen->voorblad_foto_path);
            $voorbladHtml = '<img src="' . $voorbladUrl . '" alt="Voorblad" style="max-width: 100%; height: auto;">';
        }
        $content = str_replace('{{rapport.voorblad_foto}}', $voorbladHtml, $content);
        
        // QR Code (als IMG tag)
        $qrHtml = '';
        if ($instellingen->qr_code_tonen && $instellingen->qr_code_url) {
            if (\App\Helpers\QrCodeHelper::isAvailable()) {
                $qrHtml = \App\Helpers\QrCodeHelper::generate(
                    $instellingen->qr_code_url, 
                    config('rapport.qr_code.size', 150)
                );
            } else {
                \Log::warning('QR Code package niet geïnstalleerd. Installeer: composer require simplesoftwareio/simple-qrcode');
            }
        }
        $content = str_replace('{{rapport.qr_code}}', $qrHtml, $content);
        
        // Paginanummer
        if ($paginaNummer !== null && $instellingen->paginanummering_tonen) {
            $content = str_replace('{{rapport.paginanummer}}', (string)$paginaNummer, $content);
        } else {
            $content = str_replace('{{rapport.paginanummer}}', '', $content);
        }

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
