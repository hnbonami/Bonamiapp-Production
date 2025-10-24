<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAccess
{
    /**
     * Handle an incoming request.
     * 
     * Controleert of de gebruiker toegang heeft tot een specifieke feature
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $featureKey  De key van de feature (bijv. 'klantenbeheer', 'bikefits')
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = auth()->user();

        // Superadmin heeft altijd toegang
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Check of gebruiker toegang heeft tot deze feature
        if (!$user || !$user->hasFeatureAccess($featureKey)) {
            \Log::warning('Toegang geweigerd tot feature', [
                'user_id' => $user ? $user->id : null,
                'organisatie_id' => $user && $user->organisatie ? $user->organisatie->id : null,
                'feature_key' => $featureKey,
                'route' => $request->route()->getName(),
                'url' => $request->url()
            ]);

            abort(403, 'Je organisatie heeft geen toegang tot deze functionaliteit. Neem contact op met de beheerder om deze feature te activeren.');
        }

        return $next($request);
    }
}