<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\Klant;
use App\Models\Bikefit;
use App\Models\Medewerker;
use App\Models\Inspanningstest;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Custom route model binding voor klanten - bypass scope na create
        Route::bind('klant', function ($value) {
            // Als de value al een Klant instance is, return direct
            if ($value instanceof Klant) {
                return $value;
            }
            // Anders normale binding MET organisatie scope
            return Klant::findOrFail($value);
        });

        // Custom route model binding voor bikefits
        Route::bind('bikefit', function ($value) {
            if ($value instanceof Bikefit) {
                return $value;
            }
            return Bikefit::findOrFail($value);
        });

        // Custom route model binding voor medewerkers
        Route::bind('medewerker', function ($value) {
            if ($value instanceof Medewerker) {
                return $value;
            }
            return Medewerker::findOrFail($value);
        });

        // Custom route model binding voor inspanningstesten
        Route::bind('test', function ($value) {
            if ($value instanceof Inspanningstest) {
                return $value;
            }
            return Inspanningstest::findOrFail($value);
        });

        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}