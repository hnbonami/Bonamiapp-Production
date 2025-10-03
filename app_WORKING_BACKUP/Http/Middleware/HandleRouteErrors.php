<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Log;

class HandleRouteErrors
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (UrlGenerationException $e) {
            Log::error('Route generation error: ' . $e->getMessage());
            
            // Redirect naar veilige fallback
            if (auth()->check()) {
                if (auth()->user()->user_type === 'admin') {
                    return redirect()->route('dashboard')->with('error', 'Er was een probleem met de pagina. U bent doorverwezen naar het dashboard.');
                } else {
                    return redirect()->route('profile.edit')->with('error', 'Uw profiel is niet volledig ingesteld. Voltooi eerst uw profiel.');
                }
            }
            
            return redirect()->route('login');
        }
    }
}