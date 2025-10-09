<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'platform',
        'location',
        'status',
        'logged_in_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedLoggedInAtAttribute()
    {
        return $this->logged_in_at->format('d-m-Y H:i:s');
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
}