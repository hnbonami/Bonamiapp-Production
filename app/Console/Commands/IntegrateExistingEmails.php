<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailIntegrationService;

class IntegrateExistingEmails extends Command
{
    protected $signature = 'email:integrate-existing';
    protected $description = 'Helper command to easily integrate existing email functionality with new template system';

    public function handle()
    {
        $this->info('📧 EMAIL INTEGRATION HELPER');
        $this->info('=====================================');
        
        $emailService = new EmailIntegrationService();
        
        // Check welke templates er zijn
        $templates = $emailService->getActiveTemplates();
        
        if ($templates->isEmpty()) {
            $this->warn('⚠️  Geen actieve email templates gevonden!');
            $this->info('Ga naar /admin/email/templates en maak eerst templates aan.');
            return Command::FAILURE;
        }
        
        $this->info('✅ Gevonden actieve templates:');
        foreach ($templates as $template) {
            $this->line("   - {$template->name} ({$template->type_name})");
        }
        
        $this->newLine();
        $this->info('🔗 INTEGRATIE VOORBEELDEN:');
        $this->info('=====================================');
        
        // Verjaardag email voorbeeld
        if ($emailService->hasActiveTemplate('birthday')) {
            $this->info('🎂 VERJAARDAG EMAILS:');
            $this->line('   In je bestaande birthday cron/scheduler:');
            $this->line('   
   use App\\Services\\EmailIntegrationService;
   
   $emailService = new EmailIntegrationService();
   foreach ($birthdayCustomers as $customer) {
       $emailService->sendBirthdayEmail($customer);
   }');
            $this->newLine();
        }
        
        // Welkom klant voorbeeld
        if ($emailService->hasActiveTemplate('welcome_customer')) {
            $this->info('👋 WELKOM NIEUWE KLANTEN:');
            $this->line('   In je Customer creation code:');
            $this->line('   
   use App\\Services\\EmailIntegrationService;
   
   // Na het aanmaken van een nieuwe klant:
   $emailService = new EmailIntegrationService();
   $emailService->sendWelcomeCustomerEmail($newCustomer);');
            $this->newLine();
        }
        
        // Testzadel reminder voorbeeld
        if ($emailService->hasActiveTemplate('testzadel_reminder')) {
            $this->info('🚲 TESTZADEL HERINNERINGEN:');
            $this->line('   In je testzadel reminder code:');
            $this->line('   
   use App\\Services\\EmailIntegrationService;
   
   $emailService = new EmailIntegrationService();
   foreach ($overdueTestzadels as $testzadel) {
       $customer = $testzadel->customer; // of hoe je customer data ophaalt
       $emailService->sendTestzadelReminderEmail($testzadel, $customer);
   }');
            $this->newLine();
        }
        
        $this->info('⚡ AUTOMATISCHE TRIGGERS:');
        $this->line('   Voor volledige automatisering gebruik:');
        $this->line('   php artisan email:run-triggers');
        $this->newLine();
        
        $this->info('🎯 VOLLEDIGE DOCUMENTATIE:');
        $this->line('   - Ga naar /admin/email/triggers voor configuratie');
        $this->line('   - Bekijk /admin/email/logs voor verzonden emails');
        $this->line('   - Upload je logo in /admin/email/settings');
        
        return Command::SUCCESS;
    }
}