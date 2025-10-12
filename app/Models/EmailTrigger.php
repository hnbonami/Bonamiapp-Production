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
    const TYPE_REFERRAL_THANK_YOU = 'referral_thank_you';
    const TYPE_KLANT_INVITATION = 'klant_invitation';
    const TYPE_MEDEWERKER_INVITATION = 'medewerker_invitation';

    protected $fillable = [
        'trigger_key',
        'name',
        'type',
        'trigger_type',
        'description',
        'email_template_id',
        'is_active',
        'conditions',
        'settings',
        'trigger_data',
        'last_run_at',
        'emails_sent',
        'created_by'
    ];
    
    protected $casts = [
        'conditions' => 'array',
        'settings' => 'array',
        'trigger_data' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    /**
     * Relationship naar EmailTemplate
     */
    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /**
     * Trigger type namen voor display
     */
    public function getTypeNameAttribute()
    {
        $typeNames = [
            'testzadel_reminder' => 'Testzadel Herinneringen',
            'birthday' => 'Verjaardag Felicitaties',
            'welcome_customer' => 'Welkom Nieuwe Klanten',
            'welcome_employee' => 'Welkom Nieuwe Medewerkers',
            'referral_thank_you' => 'Doorverwijzing Dankje Email',
            'klant_invitation' => 'Klant Uitnodigingen',
            'medewerker_invitation' => 'Medewerker Uitnodigingen',
        ];
        
        $triggerType = $this->trigger_type ?? $this->type;
        return $typeNames[$triggerType] ?? ucfirst(str_replace('_', ' ', $triggerType));
    }
}