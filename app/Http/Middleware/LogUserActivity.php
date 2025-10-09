<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginActivity;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Check if user just logged in (session has 'login' flag or user wasn't authenticated before)
        if (Auth::check() && session()->has('just_logged_in')) {
            $this->logActivity($request, Auth::user());
            session()->forget('just_logged_in');
        }

        return $response;
    }

    private function logActivity($request, $user)
    {
        $userAgent = $request->userAgent();

        LoginActivity::create([
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