<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FeatureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registreer custom Blade directive voor feature checks
        // Zowel @hasFeature als @feature voor backwards compatibility
        Blade::if('hasFeature', function (string $feature) {
            // Check alleen als er een user is
            if (!app()->bound('auth')) {
                return false;
            }
            
            $user = auth()->user();
            
            if (!$user) {
                return false;
            }
            
            $organisatie = $user->organisatie;
            
            if (!$organisatie) {
                return false;
            }
            
            return $organisatie->hasFeature($feature);
        });
        
        // Alias voor backwards compatibility
        Blade::if('feature', function (string $feature) {
            if (!app()->bound('auth')) {
                return false;
            }
            
            $user = auth()->user();
            
            if (!$user) {
                return false;
            }
            
            $organisatie = $user->organisatie;
            
            if (!$organisatie) {
                return false;
            }
            
            return $organisatie->hasFeature($feature);
        });
    }
}
