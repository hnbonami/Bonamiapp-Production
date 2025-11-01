<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\LoginActivity;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            // Vind de laatste actieve sessie
            $lastActivity = LoginActivity::where('user_id', $event->user->id)
                ->whereNull('logged_out_at')
                ->latest('logged_in_at')
                ->first();

            if ($lastActivity) {
                $logoutTime = now();
                $sessionDuration = $lastActivity->logged_in_at->diffInSeconds($logoutTime);
                
                $lastActivity->update([
                    'logged_out_at' => $logoutTime,
                    'session_duration' => $sessionDuration,
                ]);

                \Log::info('User logged out successfully', [
                    'user_id' => $event->user->id,
                    'email' => $event->user->email,
                    'session_duration' => gmdate('H:i:s', $sessionDuration),
                    'timestamp' => $logoutTime
                ]);
            }
        }
    }
}