<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserLoginLog;
use Illuminate\Support\Facades\Auth;

class TrackUserLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only track for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if we need to log this login
            $this->trackLoginIfNeeded($user, $request);
        }

        return $response;
    }

    /**
     * Track user login if this is a new session
     */
    private function trackLoginIfNeeded($user, $request)
    {
        $sessionKey = 'login_logged_for_' . $user->id;
        
        // Only log once per session
        if (!session()->has($sessionKey)) {
            // Log the login
            UserLoginLog::logLogin(
                $user->id,
                $request->ip(),
                $request->userAgent()
            );
            
            // Update user stats
            $user->updateLoginStats();
            
            // Mark as logged for this session
            session()->put($sessionKey, true);
        }
    }
}