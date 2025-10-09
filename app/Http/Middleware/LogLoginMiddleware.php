<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginActivity;

class LogLoginMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Bewaar de auth status voor de request
        $wasAuthenticated = Auth::check();
        $previousUserId = $wasAuthenticated ? Auth::id() : null;

        $response = $next($request);

        // Check of user nu is ingelogd na de request
        $isNowAuthenticated = Auth::check();
        $currentUserId = $isNowAuthenticated ? Auth::id() : null;

        // Als user niet was ingelogd maar nu wel (= fresh login)
        if (!$wasAuthenticated && $isNowAuthenticated && $currentUserId) {
            // Extra check: is dit een POST naar login?
            if ($request->isMethod('POST') && $request->is('login')) {
                $this->logLoginActivity($request, Auth::user());
            }
        }

        return $response;
    }

    private function logLoginActivity($request, $user)
    {
        try {
            $userAgent = $request->userAgent() ?? 'Unknown';
            
            $activity = LoginActivity::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'device' => $this->getDeviceType($userAgent),
                'browser' => $this->getBrowserName($userAgent),
                'platform' => $this->getPlatformName($userAgent),
                'location' => $this->getLocationFromIp($request->ip()),
                'status' => 'success',
                'logged_in_at' => now(),
            ]);
            
            // Debug logging
            \Log::info('Login activity logged', [
                'user_id' => $user->id,
                'activity_id' => $activity->id,
                'ip' => $request->ip()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to log login activity: ' . $e->getMessage());
        }
    }

    private function getDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/Tablet/', $userAgent)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    private function getBrowserName($userAgent)
    {
        if (preg_match('/Chrome/', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    private function getPlatformName($userAgent)
    {
        if (preg_match('/Windows/', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iOS/', $userAgent)) {
            return 'iOS';
        } else {
            return 'Unknown';
        }
    }

    private function getLocationFromIp($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }
        return 'Unknown';
    }
}