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
