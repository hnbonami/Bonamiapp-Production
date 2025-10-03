<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Klant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SafeKlantComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $safeKlant = null;
            
            try {
                if ($user->user_type === 'klant') {
                    // Probeer klant via klant_id
                    if ($user->klant_id) {
                        $safeKlant = Klant::find($user->klant_id);
                    }
                    
                    // Als niet gevonden, probeer via email
                    if (!$safeKlant) {
                        $safeKlant = Klant::where('email', $user->email)->first();
                        if ($safeKlant) {
                            $user->update(['klant_id' => $safeKlant->id]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('SafeKlantComposer error: ' . $e->getMessage());
            }
            
            $view->with('safeKlant', $safeKlant);
            $view->with('canAccessKlant', $safeKlant !== null || $user->user_type === 'admin');
            
            // Voeg veilige route helper toe
            $view->with('safeKlantRoute', function($routeName, $fallback = 'profile.edit') use ($safeKlant) {
                if ($safeKlant && $safeKlant->id) {
                    return route($routeName, $safeKlant->id);
                }
                return route($fallback);
            });
        }
    }
}