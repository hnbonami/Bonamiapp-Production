<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class FeatureHelper
{
    /**
     * Check of de huidige gebruiker toegang heeft tot een feature
     * Gebaseerd op:
     * 1. Organisatie heeft de feature geactiveerd
     * 2. Rol van gebruiker heeft de feature geactiveerd
     * 
     * @param string $featureSlug De slug van de feature (bijv. 'klantenbeheer')
     * @return bool
     */
    public static function hasAccess(string $featureSlug): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Superadmin heeft altijd toegang
        if ($user->role === 'superadmin') {
            return true;
        }
        
        // Haal de feature op
        $feature = \App\Models\Feature::where('slug', $featureSlug)->first();
        
        if (!$feature) {
            return false;
        }
        
        // Check 1: Heeft de organisatie deze feature?
        $organisatie = $user->organisatie;
        
        if (!$organisatie) {
            return false;
        }
        
        $organisatieHasFeature = $organisatie->features()
            ->where('feature_id', $feature->id)
            ->wherePivot('is_actief', true)
            ->exists();
        
        if (!$organisatieHasFeature) {
            return false;
        }
        
        // Check 2: Heeft de rol van de gebruiker deze feature geactiveerd?
        $roleHasFeature = DB::table('feature_role')
            ->where('role_key', $user->role)
            ->where('feature_id', $feature->id)
            ->where('is_actief', true)
            ->exists();
        
        // FALLBACK: Als er HELEMAAL GEEN role-feature koppelingen zijn voor deze rol,
        // geef admin en medewerker standaard toegang tot alle organisatie features
        if (!$roleHasFeature) {
            $hasAnyRoleFeatures = DB::table('feature_role')
                ->where('role_key', $user->role)
                ->where('is_actief', true)
                ->exists();
            
            // Als er helemaal geen actieve features zijn ingesteld voor deze rol,
            // geef standaard toegang aan admin en medewerker
            if (!$hasAnyRoleFeatures && in_array($user->role, ['admin', 'medewerker'])) {
                \Log::info("Fallback toegang verleend voor {$user->role} - geen role features ingesteld");
                return true;
            }
        }
        
        return $roleHasFeature;
    }
    
    /**
     * Check of gebruiker toegang heeft tot meerdere features (OR logica)
     * @param array $featureSlugs
     * @return bool
     */
    public static function hasAnyAccess(array $featureSlugs): bool
    {
        foreach ($featureSlugs as $slug) {
            if (self::hasAccess($slug)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check of gebruiker toegang heeft tot alle features (AND logica)
     * @param array $featureSlugs
     * @return bool
     */
    public static function hasAllAccess(array $featureSlugs): bool
    {
        foreach ($featureSlugs as $slug) {
            if (!self::hasAccess($slug)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Haal alle features op waar de gebruiker toegang tot heeft
     * @return \Illuminate\Support\Collection
     */
    public static function getAccessibleFeatures()
    {
        $user = auth()->user();
        
        if (!$user) {
            return collect([]);
        }
        
        // Superadmin heeft toegang tot alles
        if ($user->role === 'superadmin') {
            return \App\Models\Feature::all();
        }
        
        // Haal organisatie features op
        $organisatie = $user->organisatie;
        
        if (!$organisatie) {
            return collect([]);
        }
        
        $organisatieFeatureIds = $organisatie->features()
            ->wherePivot('is_actief', true)
            ->pluck('features.id');
        
        // Haal role features op
        $roleFeatureIds = DB::table('feature_role')
            ->where('role_key', $user->role)
            ->where('is_actief', true)
            ->whereIn('feature_id', $organisatieFeatureIds)
            ->pluck('feature_id');
        
        return \App\Models\Feature::whereIn('id', $roleFeatureIds)->get();
    }
}
