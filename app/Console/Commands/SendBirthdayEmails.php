<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailIntegrationService;
use App\Models\EmailTrigger;

class SendBirthdayEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:send-birthday';

    /**
     * The console command description.
     */
    protected $description = 'Verstuur verjaardag emails naar klanten die vandaag jarig zijn';

    /**
     * Execute the console command.
     */
    public function handle(EmailIntegrationService $emailService)
    {
        $this->info('🎂 Starting birthday email trigger...');
        
        try {
            // Zoek actieve birthday trigger
            $trigger = EmailTrigger::where('trigger_type', 'birthday')
                ->where('is_active', true)
                ->first();
            
            if (!$trigger) {
                $this->warn('⚠️ No active birthday trigger found. Creating one...');
                
                // Maak automatisch een birthday trigger aan als die niet bestaat
                $trigger = EmailTrigger::create([
                    'name' => 'Verjaardag Felicitatie',
                    'trigger_type' => 'birthday',
                    'description' => 'Automatisch aangemaakt - Verstuur verjaardagswensen naar klanten',
                    'is_active' => true,
                    'frequency' => 'daily',
                    'organisatie_id' => 1 // Default organisatie
                ]);
                
                $this->info('✅ Birthday trigger created');
            }
            
            // Run de birthday trigger
            $emailsSent = $emailService->runTrigger($trigger);
            
            if ($emailsSent > 0) {
                $this->info("✅ Birthday emails sent: {$emailsSent}");
            } else {
                $this->info('ℹ️ No birthdays today');
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Birthday email trigger failed: ' . $e->getMessage());
            \Log::error('Birthday command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}