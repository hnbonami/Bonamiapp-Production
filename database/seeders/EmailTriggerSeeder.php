<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTrigger;
use App\Models\EmailTemplate;

class EmailTriggerSeeder extends Seeder
{
    public function run()
    {
        // Get templates
        $testzadelTemplate = EmailTemplate::where('type', 'testzadel_reminder')->first();
        $welcomeTemplate = EmailTemplate::where('type', 'welcome_customer')->first();
        $birthdayTemplate = EmailTemplate::where('type', 'birthday')->first();
        
        // Create automation triggers
        EmailTrigger::updateOrCreate(
            ['type' => 'testzadel_reminder'],
            [
                'name' => 'Testzadel Herinnering (21 dagen)',
                'type' => 'testzadel_reminder',
                'email_template_id' => $testzadelTemplate?->id,
                'is_active' => true,
                'conditions' => [
                    'days_overdue' => 21
                ],
                'settings' => [
                    'frequency' => 'daily',
                    'max_reminders' => 3
                ]
            ]
        );
        
        EmailTrigger::updateOrCreate(
            ['type' => 'birthday'],
            [
                'name' => 'Automatische Verjaardagsmails',
                'type' => 'birthday',
                'email_template_id' => $birthdayTemplate?->id,
                'is_active' => true,
                'conditions' => [
                    'send_on_birthday' => true
                ],
                'settings' => [
                    'frequency' => 'daily',
                    'send_time' => '09:00'
                ]
            ]
        );
        
        EmailTrigger::updateOrCreate(
            ['type' => 'welcome_customer'],
            [
                'name' => 'Welkom Nieuwe Klanten',
                'type' => 'welcome_customer',
                'email_template_id' => $welcomeTemplate?->id,
                'is_active' => true,
                'conditions' => [
                    'trigger_on_registration' => true
                ],
                'settings' => [
                    'delay_minutes' => 30
                ]
            ]
        );
        
        EmailTrigger::updateOrCreate(
            ['type' => 'welcome_employee'],
            [
                'name' => 'Welkom Nieuwe Medewerkers',
                'type' => 'welcome_employee',
                'email_template_id' => null, // Will be created later
                'is_active' => false,
                'conditions' => [
                    'trigger_on_hire' => true
                ],
                'settings' => [
                    'delay_minutes' => 60
                ]
            ]
        );
        
        EmailTrigger::updateOrCreate(
            ['type' => 'bikefit_reminder'],
            [
                'name' => 'Bikefit Herinnering',
                'type' => 'bikefit_reminder',
                'email_template_id' => null, // Will be created later
                'is_active' => false,
                'conditions' => [
                    'days_before' => 1
                ],
                'settings' => [
                    'frequency' => 'daily'
                ]
            ]
        );
        
        EmailTrigger::updateOrCreate(
            ['type' => 'follow_up'],
            [
                'name' => 'Follow-up Email',
                'type' => 'follow_up',
                'email_template_id' => null, // Will be created later
                'is_active' => false,
                'conditions' => [
                    'days_after_service' => 7
                ],
                'settings' => [
                    'frequency' => 'weekly'
                ]
            ]
        );
    }
}