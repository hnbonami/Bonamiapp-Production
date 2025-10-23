<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganisatieFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $feature  De feature key die vereist is
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Check of gebruiker is ingelogd
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $organisatie = $user->organisatie;

        // Check of gebruiker een organisatie heeft
        if (!$organisatie) {
            abort(403, 'Je account is niet gekoppeld aan een organisatie.');
        }

        // Check of de organisatie deze feature heeft
        if (!$organisatie->hasFeature($feature)) {
            // Log poging om toegang te krijgen tot feature
            \Log::warning("Feature toegang geweigerd", [
                'user_id' => $user->id,
                'organisatie_id' => $organisatie->id,
                'feature' => $feature,
                'url' => $request->fullUrl()
            ]);

            // Redirect naar dashboard met error bericht
            return redirect()
                ->route('dashboard')
                ->with('error', 'Je organisatie heeft geen toegang tot deze functionaliteit. Neem contact op met je beheerder.');
        }

        return $next($request);
    }
}
