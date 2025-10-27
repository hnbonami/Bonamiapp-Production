<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganisatie
{
    /**
     * Controleer of resource tot juiste organisatie behoort
     * Voorkomt cross-organisatie data toegang
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        
        $user = auth()->user();
        
        // SuperAdmin mag alles zien
        if ($user->isSuperAdmin()) {
            return $next($request);
        }
        
        // Controleer of er een model in de route zit met organisatie_id
        $route = $request->route();
        $parameters = $route ? $route->parameters() : [];
        
        foreach ($parameters as $parameter) {
            // Check of parameter een model is met organisatie_id
            if (is_object($parameter) && method_exists($parameter, 'getAttribute')) {
                $organisatieId = $parameter->getAttribute('organisatie_id');
                
                if ($organisatieId && $organisatieId !== $user->organisatie_id) {
                    \Log::warning('Cross-organisation access attempt blocked', [
                        'user_id' => $user->id,
                        'user_organisatie_id' => $user->organisatie_id,
                        'resource_organisatie_id' => $organisatieId,
                        'url' => $request->url()
                    ]);
                    
                    abort(403, 'Je hebt geen toegang tot deze resource.');
                }
            }
        }
        
        return $next($request);
    }
}
