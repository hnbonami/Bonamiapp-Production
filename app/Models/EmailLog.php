<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;
    
    // Constants for trigger types
    const TRIGGER_MANUAL = 'manual';
    const TRIGGER_AUTOMATIC = 'automatic';
    const TRIGGER_TESTZADEL_REMINDER = 'testzadel_reminder';
    const TRIGGER_BIRTHDAY = 'birthday';
    const TRIGGER_WELCOME_CUSTOMER = 'welcome_customer';
    const TRIGGER_WELCOME_EMPLOYEE = 'welcome_employee';
    
    // Constants for status
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_PENDING = 'pending';
    const STATUS_QUEUED = 'queued';
    
    protected $table = 'email_logs'; // Use new table instead of conflicts
    
    protected $fillable = [
        'trigger_name',
        'template_id', 
        'recipient_email',
        'variables',
        'sent_at',
        'status'
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
        'variables' => 'array'
    ];
    
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }
}