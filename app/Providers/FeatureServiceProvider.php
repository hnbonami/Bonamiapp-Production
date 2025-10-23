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
        // Deze wordt LAZY geëvalueerd, dus geen circular dependency
        Blade::if('feature', function (string $feature) {
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
    }
}
