<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganisatieAdmin
{
    /**
     * Controleer of de gebruiker een organisatie admin of superadmin is
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isBeheerder()) {
            abort(403, 'Toegang geweigerd. Alleen beheerders hebben toegang tot deze pagina.');
        }

        return $next($request);
    }
}
