<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogLoginMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Log on every request for debugging - we'll optimize later
        if (auth()->check()) {
            // Check if we already logged this session
            $sessionKey = 'login_logged_' . auth()->id() . '_' . session()->getId();
            
            if (!session()->has($sessionKey)) {
                try {
                    $loginActivity = \App\Models\LoginActivity::create([
                        'user_id' => auth()->id(),
                        'logged_in_at' => now(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                    
                    // Mark this session as logged
                    session()->put($sessionKey, true);
                    
                    // Debug log
                    \Log::info('🎯 LOGIN ACTIVITY CREATED', [
                        'id' => $loginActivity->id,
                        'user_id' => auth()->id(),
                        'user_email' => auth()->user()->email,
                        'ip' => $request->ip(),
                        'timestamp' => now(),
                        'session_id' => session()->getId()
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('❌ Failed to log login activity: ' . $e->getMessage());
                }
            }
        }
        
        return $response;
    }
}