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
    const TYPE_KLANT_INVITATION = 'klant_invitation';
    const TYPE_MEDEWERKER_INVITATION = 'medewerker_invitation';

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
        'status',
        'description',
        'configuration'
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
        'last_run_at' => 'datetime',
        'variables' => 'array',
        'conditions' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'configuration' => 'array'
    ];

    public static function getTypes(): array
    {
        return [
            self::TYPE_TESTZADEL_REMINDER => 'Testzadel Herinnering',
            self::TYPE_BIRTHDAY => 'Verjaardag',
            self::TYPE_WELCOME_CUSTOMER => 'Welkom Klant',
            self::TYPE_WELCOME_EMPLOYEE => 'Welkom Medewerker',
            self::TYPE_KLANT_INVITATION => 'Klant Uitnodiging',
            self::TYPE_MEDEWERKER_INVITATION => 'Medewerker Uitnodiging',
        ];
    }

    public function getTypeNameAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Onbekend';
    }
    
    /**
     * Get the template associated with this trigger
     */
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Get the email logs for this trigger
     */
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'trigger_name', 'type');
    }
}