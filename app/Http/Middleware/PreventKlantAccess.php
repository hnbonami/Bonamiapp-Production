<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PreventKlantAccess
{
    /**
     * Handle an incoming request.
     * 
     * Voorkom dat klanten toegang krijgen tot admin/medewerker pagina's
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Als de gebruiker een klant is
        if ($user && $user->role === 'klant') {
            
            // 🔧 AUTO-FIX: Als klant_id NULL is, zoek klant record op basis van email
            if ($user->klant_id === null && $user->email) {
                $klantRecord = \App\Models\Klant::where('email', $user->email)
                    ->where('organisatie_id', $user->organisatie_id)
                    ->first();
                
                if ($klantRecord) {
                    \Log::warning('🔧 AUTO-FIX: klant_id was NULL, gekoppeld aan klant record', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'klant_id' => $klantRecord->id
                    ]);
                    
                    // Update user met klant_id
                    $user->klant_id = $klantRecord->id;
                    $user->save();
                    
                    // Refresh user voor huidige request
                    $user = $user->fresh();
                }
            }
            
            $routeName = $request->route()->getName();
            $routeKlantParam = $request->route('klant');
            
            // Sta toe: klanten.show voor hun eigen profiel
            if ($routeName === 'klanten.show') {
                $klantId = $routeKlantParam;
                
                // Als het een Klant model is, haal het ID op
                if ($klantId instanceof \App\Models\Klant) {
                    $klantId = $klantId->id;
                }
                
                \Log::info('📋 Klanten.show - Vergelijk IDs', [
                    'user_klant_id' => $user->klant_id,
                    'route_klant_id' => $klantId,
                    'match' => $user->klant_id == $klantId
                ]);
                
                // Check of de klant zijn EIGEN profiel bekijkt
                if ($user->klant_id == $klantId) {
                    \Log::info('✅ Klant bekijkt eigen profiel - TOEGANG VERLEEND');
                    return $next($request);
                }
                
                \Log::warning('❌ Klant probeert ANDER profiel te bekijken', [
                    'user_klant_id' => $user->klant_id,
                    'requested_klant_id' => $klantId
                ]);
            }
            
            // Voor alle andere routes: blokkeer toegang
            \Log::warning('🚫 Klant heeft geen toegang tot deze pagina', [
                'user_id' => $user->id,
                'route' => $routeName
            ]);
            
            abort(403, 'Je hebt geen toegang tot deze pagina.');
        }

        return $next($request);
    }
}