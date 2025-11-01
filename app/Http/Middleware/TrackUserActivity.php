<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Als gebruiker is ingelogd en nog geen actieve sessie heeft
        if (Auth::check()) {
            $userId = Auth::id();
            
            // Check of er al een actieve sessie is
            $activeSession = ActivityLog::where('user_id', $userId)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();
            
            // Als er geen actieve sessie is, maak er een
            if (!$activeSession) {
                ActivityLog::create([
                    'user_id' => $userId,
                    'login_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                \Log::info('Activity: New login session created', [
                    'user_id' => $userId,
                    'ip' => $request->ip()
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, $response)
    {
        // Als gebruiker uitlogt, update de laatste sessie
        if ($request->is('logout') && $request->isMethod('post')) {
            $userId = Auth::id();
            
            if ($userId) {
                $lastActivity = ActivityLog::where('user_id', $userId)
                    ->whereNull('logout_at')
                    ->latest('login_at')
                    ->first();

                if ($lastActivity) {
                    $logoutTime = now();
                    $sessionDuration = $lastActivity->login_at->diffInSeconds($logoutTime);
                    
                    $lastActivity->update([
                        'logout_at' => $logoutTime,
                        'session_duration' => $sessionDuration,
                    ]);

                    \Log::info('Activity: Logout recorded', [
                        'user_id' => $userId,
                        'session_duration' => gmdate('H:i:s', $sessionDuration)
                    ]);
                }
            }
        }
    }
}