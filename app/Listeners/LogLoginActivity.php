<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use App\Models\LoginActivity;

class LogLoginActivity
{
    public function handle($event)
    {
        // Alleen als er daadwerkelijk een user is
        if (!isset($event->user) || !$event->user) {
            return;
        }

        $user = $event->user;
        $request = request();
        
        // Veilig proberen om login activity aan te maken
        try {
            $userAgent = $request->userAgent() ?? 'Unknown';
            
            LoginActivity::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'device' => $this->getDeviceType($userAgent),
                'browser' => $this->getBrowserName($userAgent),
                'platform' => $this->getPlatformName($userAgent),
                'location' => $this->getLocationFromIp($request->ip()),
                'status' => ($event instanceof Login) ? 'success' : 'failed',
                'logged_in_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Stil falen - login process mag niet stuk door logging problemen
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