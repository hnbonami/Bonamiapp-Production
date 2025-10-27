<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Check of gebruiker de juiste rol heeft
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Toegestane rollen (bijv: 'admin', 'medewerker')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check of gebruiker is ingelogd
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Je moet ingelogd zijn om deze pagina te bekijken.');
        }
        
        $user = auth()->user();
        
        // SuperAdmin heeft altijd toegang
        if ($user->isSuperAdmin()) {
            return $next($request);
        }
        
        // Check of gebruiker één van de toegestane rollen heeft
        if (!in_array($user->role, $roles)) {
            \Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'url' => $request->url()
            ]);
            
            abort(403, 'Je hebt geen toegang tot deze pagina.');
        }
        
        return $next($request);
    }
}
