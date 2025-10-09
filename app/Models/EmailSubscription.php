<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'subscriber_type',
        'subscriber_id',
        'status',
        'unsubscribe_token',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_reason'
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';

    // Subscriber types
    const TYPE_KLANT = 'klant';
    const TYPE_MEDEWERKER = 'medewerker';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (!$subscription->unsubscribe_token) {
                $subscription->unsubscribe_token = Str::random(32);
            }
            if (!$subscription->subscribed_at && $subscription->status === self::STATUS_SUBSCRIBED) {
                $subscription->subscribed_at = now();
            }
        });
    }

    /**
     * Get the subscriber (Klant or Medewerker)
     */
    public function subscriber()
    {
        if ($this->subscriber_type === self::TYPE_KLANT) {
            return $this->belongsTo(\App\Models\Klant::class, 'subscriber_id');
        } else {
            return $this->belongsTo(\App\Models\Medewerker::class, 'subscriber_id');
        }
    }

    /**
     * Check if subscribed
     */
    public function isSubscribed(): bool
    {
        return $this->status === self::STATUS_SUBSCRIBED;
    }

    /**
     * Unsubscribe
     */
    public function unsubscribe(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
            'unsubscribe_reason' => $reason
        ]);
    }

    /**
     * Resubscribe
     */
    public function resubscribe(): void
    {
        $this->update([
            'status' => self::STATUS_SUBSCRIBED,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'unsubscribe_reason' => null
        ]);
    }

    /**
     * Get or create subscription for email
     */
    public static function getOrCreateForEmail(string $email, string $type = self::TYPE_KLANT, int $subscriberId = null): self
    {
        return self::firstOrCreate(
            ['email' => $email],
            [
                'subscriber_type' => $type,
                'subscriber_id' => $subscriberId,
                'status' => self::STATUS_SUBSCRIBED,
                'subscribed_at' => now()
            ]
        );
    }

    /**
     * Check if email is subscribed
     */
    public static function isEmailSubscribed(string $email): bool
    {
        $subscription = self::where('email', $email)->first();
        return $subscription ? $subscription->isSubscribed() : true; // Default to subscribed for new emails
    }

    /**
     * Scope for subscribed emails
     */
    public function scopeSubscribed($query)
    {
        return $query->where('status', self::STATUS_SUBSCRIBED);
    }

    /**
     * Scope for unsubscribed emails
     */
    public function scopeUnsubscribed($query)
    {
        return $query->where('status', self::STATUS_UNSUBSCRIBED);
    }
}