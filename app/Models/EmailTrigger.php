<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'email_template_id',
        'is_active',
        'conditions',
        'settings',
        'last_run_at',
        'emails_sent'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conditions' => 'array',
        'settings' => 'array',
        'last_run_at' => 'datetime'
    ];

    // Trigger types
    const TYPE_TESTZADEL_REMINDER = 'testzadel_reminder';
    const TYPE_BIRTHDAY = 'birthday';
    const TYPE_WELCOME_CUSTOMER = 'welcome_customer';
    const TYPE_WELCOME_EMPLOYEE = 'welcome_employee';
    const TYPE_BIKEFIT_REMINDER = 'bikefit_reminder';
    const TYPE_FOLLOW_UP = 'follow_up';

    public static function getTypes(): array
    {
        return [
            self::TYPE_TESTZADEL_REMINDER => 'Testzadel Herinnering',
            self::TYPE_BIRTHDAY => 'Verjaardag',
            self::TYPE_WELCOME_CUSTOMER => 'Welkom Nieuwe Klant',
            self::TYPE_WELCOME_EMPLOYEE => 'Welkom Nieuwe Medewerker',
            self::TYPE_BIKEFIT_REMINDER => 'Bikefit Herinnering',
            self::TYPE_FOLLOW_UP => 'Follow-up Email'
        ];
    }

    // Relationships
    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function getTypeNameAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Onbekend';
    }

    public function shouldRun(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if enough time has passed since last run
        if ($this->last_run_at) {
            $frequency = $this->settings['frequency'] ?? 'daily';
            $interval = match($frequency) {
                'hourly' => now()->subHour(),
                'daily' => now()->subDay(),
                'weekly' => now()->subWeek(),
                default => now()->subDay()
            };

            return $this->last_run_at <= $interval;
        }

        return true;
    }

    public function updateLastRun(): void
    {
        $this->update(['last_run_at' => now()]);
    }

    public function incrementEmailsSent(int $count = 1): void
    {
        $this->increment('emails_sent', $count);
    }
}