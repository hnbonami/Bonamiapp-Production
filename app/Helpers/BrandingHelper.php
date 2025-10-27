<?php

namespace App\Helpers;

use App\Models\OrganisatieBranding;
use App\Models\User;

class BrandingHelper
{
    /**
     * Haal branding configuratie op voor een gebruiker
     */
    public static function getBrandingForUser(User $user)
    {
        if (!$user->organisatie_id) {
            return null;
        }
        
        return $user->getBranding();
    }
    
    /**
     * Haal branding configuratie op voor een organisatie ID
     */
    public static function getBrandingForOrganisatie($organisatieId)
    {
        $organisatie = \App\Models\Organisatie::find($organisatieId);
        
        if (!$organisatie || !$organisatie->hasCustomBrandingFeature()) {
            return null;
        }
        
        return $organisatie->getBrandingConfig();
    }
    
    /**
     * Genereer inline CSS voor rapporten met branding
     */
    public static function getRapportCss(OrganisatieBranding $branding = null)
    {
        if (!$branding) {
            return self::getDefaultRapportCss();
        }
        
        return "
        <style>
            @import url('https://fonts.googleapis.com/css2?family={$branding->heading_font}:wght@400;600;700&family={$branding->body_font}:wght@400;500&display=swap');
            
            body {
                font-family: '{$branding->body_font}', sans-serif;
                color: {$branding->text_color};
                background-color: {$branding->background_color};
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-family: '{$branding->heading_font}', sans-serif;
                color: {$branding->primary_color};
            }
            
            .header-bar {
                background-color: {$branding->primary_color};
                color: white;
            }
            
            .accent {
                color: {$branding->accent_color};
            }
            
            .secondary {
                color: {$branding->secondary_color};
            }
            
            table thead {
                background-color: {$branding->primary_color};
                color: white;
            }
            
            .btn-primary {
                background-color: {$branding->primary_color};
                color: white;
            }
            
            a {
                color: {$branding->primary_color};
            }
        </style>
        ";
    }
    
    /**
     * Standaard rapport CSS zonder branding
     */
    private static function getDefaultRapportCss()
    {
        return "
        <style>
            body {
                font-family: 'Inter', sans-serif;
                color: #1F2937;
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-family: 'Inter', sans-serif;
                color: #3B82F6;
            }
            
            .header-bar {
                background-color: #3B82F6;
                color: white;
            }
        </style>
        ";
    }
    
    /**
     * Genereer HTML header voor rapport met logo en bedrijfsinfo
     */
    public static function getRapportHeader(OrganisatieBranding $branding = null, $organisatie = null)
    {
        $logoUrl = $branding && $branding->rapport_logo_path 
            ? storage_path('app/public/' . $branding->rapport_logo_path)
            : public_path('logo_bonami.png');
            
        $companyName = $branding && $branding->company_name 
            ? $branding->company_name 
            : ($organisatie ? $organisatie->naam : 'Bonami Sportcoaching');
            
        $tagline = $branding && $branding->tagline 
            ? $branding->tagline 
            : '';
            
        $headerText = $branding && $branding->rapport_header 
            ? '<p style="margin: 5px 0; font-size: 12px;">' . nl2br(e($branding->rapport_header)) . '</p>'
            : '';
        
        return "
        <div class='rapport-header' style='text-align: center; padding: 20px; border-bottom: 3px solid " . ($branding ? $branding->primary_color : '#3B82F6') . ";'>
            <img src='{$logoUrl}' alt='Logo' style='max-height: 80px; margin-bottom: 10px;'>
            <h1 style='margin: 10px 0; font-size: 24px;'>{$companyName}</h1>
            " . ($tagline ? "<p style='margin: 5px 0; font-size: 14px; color: #666;'>{$tagline}</p>" : "") . "
            {$headerText}
        </div>
        ";
    }
    
    /**
     * Genereer HTML footer voor rapport
     */
    public static function getRapportFooter(OrganisatieBranding $branding = null, $organisatie = null)
    {
        $footerText = $branding && $branding->rapport_footer 
            ? nl2br(e($branding->rapport_footer))
            : 'Copyright © ' . date('Y') . ' - Alle rechten voorbehouden';
            
        $primaryColor = $branding ? $branding->primary_color : '#3B82F6';
        
        return "
        <div class='rapport-footer' style='text-align: center; padding: 15px; border-top: 2px solid {$primaryColor}; margin-top: 30px; font-size: 11px; color: #666;'>
            <p>{$footerText}</p>
            <p style='margin-top: 5px;'>Gegenereerd op " . now()->format('d/m/Y H:i') . "</p>
        </div>
        ";
    }
    
    /**
     * Genereer watermark HTML voor rapport
     */
    public static function getRapportWatermark(OrganisatieBranding $branding = null)
    {
        if (!$branding || !$branding->show_watermark || !$branding->rapport_watermark_path) {
            return '';
        }
        
        $watermarkUrl = storage_path('app/public/' . $branding->rapport_watermark_path);
        
        return "
        <div class='watermark' style='position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.1; z-index: -1;'>
            <img src='{$watermarkUrl}' alt='Watermark' style='max-width: 500px;'>
        </div>
        ";
    }
    
    /**
     * Check of organisatie custom branding heeft
     */
    public static function hasCustomBranding($organisatieId)
    {
        return OrganisatieBranding::hasCustomBranding($organisatieId);
    }
}
