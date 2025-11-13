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
        // Override public_path voor productieserver (One.com hosting) - HARDERE FIX
        if (env('APP_ENV') === 'production') {
            // Bind ZOWEL path.public ALS publieke helper functie
            $this->app->bind('path.public', function() {
                return '/customers/5/a/2/hannesbonami.be/httpd.www/public';
            });
            
            // HARD OVERRIDE van public_path() helper functie
            if (!function_exists('public_path_override')) {
                function public_path_override($path = '') {
                    return '/customers/5/a/2/hannesbonami.be/httpd.www/public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
                }
            }
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
