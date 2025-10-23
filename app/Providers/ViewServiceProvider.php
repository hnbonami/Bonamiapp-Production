<?php

namespace App\Providers;

use App\Models\Klant;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // In de view composer voor sidebar stats
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $organisatieId = auth()->user()->organisatie_id;
                
                $view->with([
                    'klantenCount' => Klant::where('organisatie_id', $organisatieId)->count(),
                    'medewerkersCount' => User::where('organisatie_id', $organisatieId)
                                              ->whereIn('role', ['admin', 'medewerker'])
                                              ->count(),
                    // ...existing code...
                ]);
            }
        });
    }
}