<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganisatieAdmin
{
    /**
     * Handle an incoming request.
     * 
     * Controleert of de ingelogde gebruiker organisatie admin rechten heeft
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Controleer of gebruiker is ingelogd
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Controleer of gebruiker organisatie admin is (probeer verschillende mogelijke veldnamen)
        $isOrganisatieAdmin = $user->is_organisatie_admin ?? 
                              $user->role === 'organisatie_admin' ?? 
                              $user->user_type === 'organisatie_admin' ?? 
                              false;

        if (!$isOrganisatieAdmin) {
            abort(403, 'Geen toegang. Alleen organisatie admins hebben toegang tot deze pagina.');
        }

        return $next($request);
    }
}
