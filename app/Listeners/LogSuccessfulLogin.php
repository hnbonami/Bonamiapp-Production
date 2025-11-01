<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginActivity;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        LoginActivity::create([
            'user_id' => $event->user->id,
            'logged_in_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        \Log::info('User logged in successfully', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip' => request()->ip(),
            'timestamp' => now()
        ]);
    }
}