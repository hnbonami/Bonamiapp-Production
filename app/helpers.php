<?php

if (!function_exists('safe_klant_route')) {
    /**
     * Generate a safe route to klanten.show with proper parameter checking
     */
    function safe_klant_route($routeName, $klantId = null)
    {
        if (!$klantId) {
            // Probeer klant_id uit de huidige gebruiker te halen
            if (auth()->check() && auth()->user()->klant_id) {
                $klantId = auth()->user()->klant_id;
            } elseif (auth()->check() && auth()->user()->user_type === 'klant') {
                // Probeer klant te vinden via email
                $klant = \App\Models\Klant::where('email', auth()->user()->email)->first();
                if ($klant) {
                    auth()->user()->update(['klant_id' => $klant->id]);
                    $klantId = $klant->id;
                }
            }
        }
        
        if ($klantId) {
            return route($routeName, $klantId);
        }
        
        // Fallback route
        return route('profile.edit');
    }
}

if (!function_exists('user_can_access_klant')) {
    /**
     * Check if current user can access klant profile
     */
    function user_can_access_klant($klantId = null)
    {
        if (!auth()->check()) {
            return false;
        }
        
        if (auth()->user()->user_type === 'admin') {
            return true;
        }
        
        if (auth()->user()->user_type === 'klant') {
            if (!$klantId) {
                return auth()->user()->klant_id !== null;
            }
            return auth()->user()->klant_id == $klantId;
        }
        
        return false;
    }
}