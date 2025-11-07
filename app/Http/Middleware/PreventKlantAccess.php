<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventKlantAccess
{
    /**
     * Handle an incoming request.
     * 
     * Voorkom dat klanten toegang krijgen tot admin/medewerker pagina's
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check of gebruiker is ingelogd
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Je moet ingelogd zijn om deze pagina te bekijken.');
        }
        
        // Check of gebruiker een klant is
        if (auth()->user()->role === 'klant') {
            abort(403, 'Geen toegang. Klanten hebben geen toegang tot deze pagina.');
        }
        
        return $next($request);
    }
}