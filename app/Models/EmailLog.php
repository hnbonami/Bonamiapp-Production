<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'body_html',
        'body_text',
        'status',
        'sent_at',
        'error_message',
        'metadata'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';

    // Trigger types
    const TRIGGER_MANUAL = 'manual';
    const TRIGGER_AUTOMATIC = 'automatic';
    const TRIGGER_BULK = 'bulk';

    // Relationships
    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function emailTrigger()
    {
        return $this->belongsTo(EmailTrigger::class);
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Helper methods
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now()
        ]);
    }

        public static function markAsFailed($id, ?string $error = null)
    {
        return self::where('id', $id)->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
            'sent_at' => null,
            'updated_at' => now()
        ]);
    }

    public function markAsOpened(): void
    {
        if (!$this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
    }

    public function markAsClicked(): void
    {
        $this->update(['clicked_at' => now()]);
        
        // Also mark as opened if not already
        if (!$this->opened_at) {
            $this->markAsOpened();
        }
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SENT => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_BOUNCED => 'orange',
            default => 'gray'
        };
    }
}