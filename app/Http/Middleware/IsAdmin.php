<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Controleer of de gebruiker een administrator is
     * Alleen gebruikers met is_admin = true krijgen toegang
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check of gebruiker ingelogd is en admin rechten heeft
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Geen toegang. Alleen administrators hebben toegang tot deze pagina.');
        }

        return $next($request);
    }
}
