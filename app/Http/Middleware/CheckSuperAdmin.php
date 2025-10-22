<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Controleer of de gebruiker een superadmin is
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Toegang geweigerd. Alleen superadmins hebben toegang tot deze pagina.');
        }

        return $next($request);
    }
}
