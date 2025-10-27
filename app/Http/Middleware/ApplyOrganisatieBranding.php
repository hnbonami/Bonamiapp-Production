<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ApplyOrganisatieBranding
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        if ($user && $user->organisatie_id) {
            $branding = $user->getBranding();
            
            if ($branding) {
                // Maak branding beschikbaar voor alle views
                View::share('organisatieBranding', $branding);
                
                // Voeg CSS variabelen toe aan response
                $cssVariables = $branding->getCssVariables();
                View::share('brandingCssVariables', $cssVariables);
            }
        }
        
        return $next($request);
    }
}
