<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'login_at',
        'logout_at',
        'ip_address',
        'session_duration',
        'user_agent'
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime'
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a user login
     */
    public static function logLogin($userId, $ipAddress = null, $userAgent = null)
    {
        return self::create([
            'user_id' => $userId,
            'login_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    }

    /**
     * Log a user logout
     */
    public function logLogout()
    {
        $this->logout_at = now();
        $this->session_duration = $this->logout_at->diffInSeconds($this->login_at);
        $this->save();
    }

    /**
     * Get session duration in human readable format
     */
    public function getSessionDurationHumanAttribute()
    {
        if (!$this->session_duration) {
            return 'Onbekend';
        }

        $hours = floor($this->session_duration / 3600);
        $minutes = floor(($this->session_duration % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}u {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Cleanup old logs (older than 30 days)
     */
    public static function cleanup()
    {
        return self::where('login_at', '<', now()->subDays(30))->delete();
    }
}