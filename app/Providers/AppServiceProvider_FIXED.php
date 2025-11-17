<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // ✅ GEFIXTE VERSIE - Public path override voor One.com hosting
        if (app()->environment('production')) {
            if (!function_exists('public_path_override')) {
                function public_path_override($path = '') {
                    // ✅ NIEUW PAD voor performancepulse.be
                    return '/customers/1/e/9/c9kxrmhxa/webroots/b524bfb2/public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
                }
            }
        }

        // Registreer custom Blade directive voor feature checks
        \Blade::if('hasFeature', function (string $featureKey) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            
            if (!$user->organisatie) {
                return false;
            }
            
            return $user->organisatie->hasFeature($featureKey);
        });

        // View composer voor organisatie branding (indien beschikbaar)
        View::composer('*', function ($view) {
            if (auth()->check() && auth()->user()->organisatie) {
                $branding = auth()->user()->organisatie->branding;
                $view->with('branding', $branding);
            }
        });
    }
}
