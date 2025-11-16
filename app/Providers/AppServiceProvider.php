<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Override public_path voor productieserver (One.com hosting)
        if (env('APP_ENV') === 'production') {
            // Bind path.public voor Laravel's interne gebruik
            $this->app->bind('path.public', function() {
                return base_path('public');
            });
            
            // 🔥 FIX: Forceer asset URL prefix voor uploads in productie
            // Alle uploads gaan naar /public/uploads/, maar Laravel moet weten dat
            // ze bereikbaar zijn via /uploads/ (niet /storage/uploads/)
            \URL::forceScheme('https');
        }

        // Registreer custom Blade directive voor feature checks
        \Blade::if('hasFeature', function (string $featureKey) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            return $user->hasFeatureAccess($featureKey);
        });
    }
}
