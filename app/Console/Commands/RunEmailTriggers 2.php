<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;

class RunEmailTriggers extends Command
{
    protected $signature = 'email:run-triggers';
    protected $description = 'Run all active email triggers (testzadel reminders, birthdays, etc)';

    public function handle()
    {
        $emailService = new EmailService();
        
        $this->info('🤖 Running automatic email triggers...');
        
        // Run testzadel reminders
        $this->info('📧 Checking testzadel reminders...');
        $testzadelsSent = $emailService->runTestzadelReminders();
        $this->info("✅ Sent {$testzadelsSent} testzadel reminder emails");
        
        // Run birthday emails
        $this->info('🎂 Checking birthday emails...');
        $birthdaysSent = $emailService->runBirthdayEmails();
        $this->info("✅ Sent {$birthdaysSent} birthday emails");
        
        $totalSent = $testzadelsSent + $birthdaysSent;
        
        if ($totalSent > 0) {
            $this->info("🎉 Total emails sent: {$totalSent}");
        } else {
            $this->info("ℹ️  No emails needed to be sent at this time");
        }
        
        return Command::SUCCESS;
    }
}