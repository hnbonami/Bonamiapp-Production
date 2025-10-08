<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTrigger extends Model
{
    use HasFactory;

    // Trigger types
    const TYPE_TESTZADEL_REMINDER = 'testzadel_reminder';
    const TYPE_BIRTHDAY = 'birthday';
    const TYPE_WELCOME_CUSTOMER = 'welcome_customer';
    const TYPE_WELCOME_EMPLOYEE = 'welcome_employee';

    protected $fillable = [
        'name',
        'type',
        'email_template_id',
        'is_active',
        'conditions',
        'settings',
        'emails_sent',
        'last_run_at',
        'trigger_name',
        'template_id', 
        'recipient_email',
        'variables',
        'sent_at',
        'status'
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
        'last_run_at' => 'datetime',
        'variables' => 'array',
        'conditions' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    public static function getTypes(): array
    {
        return [
            self::TYPE_TESTZADEL_REMINDER => 'Testzadel Herinnering',
            self::TYPE_BIRTHDAY => 'Verjaardag',
            self::TYPE_WELCOME_CUSTOMER => 'Welkom Klant',
            self::TYPE_WELCOME_EMPLOYEE => 'Welkom Medewerker',
        ];
    }

    public function getTypeNameAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Onbekend';
    }
    
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}