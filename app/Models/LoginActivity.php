<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivity extends Model
{
    use HasFactory;

    protected $table = 'login_activities';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'logged_in_at',
        'logged_out_at',
        'session_duration',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
        'session_duration' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedLoggedInAtAttribute()
    {
        return $this->logged_in_at->format('d-m-Y H:i:s');
    }

    public function getFormattedLoggedOutAtAttribute()
    {
        return $this->logged_out_at ? $this->logged_out_at->format('d-m-Y H:i:s') : null;
    }

    public function getDeviceIconAttribute()
    {
        $device = strtolower($this->device ?? '');
        
        if (str_contains($device, 'mobile') || str_contains($device, 'phone')) {
            return '📱';
        } elseif (str_contains($device, 'tablet')) {
            return '📱';
        } else {
            return '💻';
        }
    }

    public function getBrowserIconAttribute()
    {
        $browser = strtolower($this->browser ?? '');
        
        if (str_contains($browser, 'chrome')) {
            return '🌐';
        } elseif (str_contains($browser, 'firefox')) {
            return '🦊';
        } elseif (str_contains($browser, 'safari')) {
            return '🧭';
        } elseif (str_contains($browser, 'edge')) {
            return '🌐';
        } else {
            return '🌍';
        }
    }

    /**
     * Get human readable session duration
     */
    public function getSessionDurationHumanAttribute()
    {
        if (!$this->session_duration) {
            return '-';
        }
        
        $hours = floor($this->session_duration / 3600);
        $minutes = floor(($this->session_duration % 3600) / 60);
        $seconds = $this->session_duration % 60;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}