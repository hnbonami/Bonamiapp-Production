<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     * 
     * Controleert of de ingelogde gebruiker superadmin rechten heeft
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Controleer of gebruiker is ingelogd
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Controleer of gebruiker superadmin is (probeer verschillende mogelijke veldnamen)
        $isSuperAdmin = $user->is_superadmin ?? 
                        $user->role === 'superadmin' ?? 
                        $user->user_type === 'superadmin' ?? 
                        false;

        if (!$isSuperAdmin) {
            abort(403, 'Geen toegang. Alleen superadmins hebben toegang tot deze pagina.');
        }

        return $next($request);
    }
}
