<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LoginActivity;
use Illuminate\Support\Facades\Auth;

class TrackLogout
{
    public function handle(Request $request, Closure $next)
    {
        // Check if this is a logout request
        if ($request->is('logout') || $request->route()?->getName() === 'logout') {
            $user = Auth::user();
            
            if ($user) {
                \Log::info('🔥 LOGOUT MIDDLEWARE TRIGGERED', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'route' => $request->route()?->getName(),
                    'url' => $request->url(),
                ]);
                
                // Update the most recent login activity with logout time
                $latestActivity = LoginActivity::where('user_id', $user->id)
                    ->whereNull('logged_out_at')
                    ->latest('logged_in_at')
                    ->first();
                    
                if ($latestActivity) {
                    $latestActivity->update(['logged_out_at' => now()]);
                    \Log::info('🔥 LOGOUT TIME UPDATED VIA MIDDLEWARE', [
                        'activity_id' => $latestActivity->id,
                        'logged_out_at' => now(),
                    ]);
                } else {
                    \Log::warning('🔥 NO ACTIVE SESSION FOUND FOR LOGOUT', [
                        'user_id' => $user->id,
                    ]);
                }
            }
        }
        
        return $next($request);
    }
}