<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;

class RunEmailTriggers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:run-triggers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all email triggers (testzadel reminders, birthday emails, etc.)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Running email triggers...');

        try {
            $emailService = new EmailService();
            
            // Run testzadel reminders
            $this->info('📧 Checking testzadel reminders...');
            $testzadelsSent = $emailService->runTestzadelReminders();
            $this->info("   ✅ Sent {$testzadelsSent} testzadel reminder emails");
            
            // Run birthday emails
            $this->info('🎂 Checking birthday emails...');
            $birthdaysSent = $this->runBirthdayEmails();
            $this->info("   ✅ Sent {$birthdaysSent} birthday emails");
            
            $totalSent = $testzadelsSent + $birthdaysSent;
            
            if ($totalSent > 0) {
                $this->info("🎉 Total emails sent: {$totalSent}");
            } else {
                $this->info("ℹ️  No emails needed to be sent at this time");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error running email triggers: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Run birthday email trigger
     */
    private function runBirthdayEmails()
    {
        try {
            // Find birthday trigger
            $birthdayTrigger = \App\Models\EmailTrigger::where('type', \App\Models\EmailTrigger::TYPE_BIRTHDAY)
                                                     ->where('is_active', true)
                                                     ->first();
            
            if (!$birthdayTrigger || !$birthdayTrigger->emailTemplate) {
                $this->warn('   ⚠️  No active birthday trigger or template found');
                return 0;
            }

            // Get customers with birthday today
            $customers = \App\Models\Klant::whereMonth('geboortedatum', now()->month)
                                         ->whereDay('geboortedatum', now()->day)
                                         ->whereNotNull('email')
                                         ->get();

            if ($customers->isEmpty()) {
                $this->info('   ℹ️  No customers with birthday today');
                return 0;
            }

            $emailsSent = 0;

            foreach ($customers as $customer) {
                // Check if birthday email was already sent today
                $alreadySent = \App\Models\EmailLog::where('recipient_email', $customer->email)
                                                  ->where('trigger_name', 'birthday')
                                                  ->whereDate('created_at', today())
                                                  ->exists();

                if ($alreadySent) {
                    $this->info("   ⏭️  Birthday email already sent to {$customer->email} today");
                    continue;
                }

                // Prepare variables for the email template
                $variables = [
                    'voornaam' => $customer->voornaam,
                    'naam' => $customer->naam,
                    'email' => $customer->email,
                    'bedrijf_naam' => 'Bonami Sportcoaching',
                    'datum' => now()->format('d/m/Y'),
                    'jaar' => now()->format('Y'),
                ];

                try {
                    // Send email using the template
                    $subject = $birthdayTrigger->emailTemplate->renderSubject($variables);
                    $body = $birthdayTrigger->emailTemplate->renderBody($variables);

                    // Use your existing email sending method
                    \Mail::html($body, function ($message) use ($customer, $subject) {
                        $message->to($customer->email)
                                ->subject($subject);
                    });

                    // Log the email
                    \App\Models\EmailLog::create([
                        'recipient_email' => $customer->email,
                        'subject' => $subject,
                        'template_id' => $birthdayTrigger->emailTemplate->id,
                        'trigger_name' => 'birthday',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'variables' => $variables
                    ]);

                    $this->info("   📤 Birthday email sent to {$customer->voornaam} ({$customer->email})");
                    $emailsSent++;

                } catch (\Exception $e) {
                    $this->error("   ❌ Failed to send birthday email to {$customer->email}: " . $e->getMessage());
                    
                    // Log failed email
                    \App\Models\EmailLog::create([
                        'recipient_email' => $customer->email,
                        'subject' => $subject ?? 'Birthday email',
                        'template_id' => $birthdayTrigger->emailTemplate->id,
                        'trigger_name' => 'birthday',
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'variables' => $variables
                    ]);
                }
            }

            // Update trigger statistics
            $birthdayTrigger->increment('emails_sent', $emailsSent);
            $birthdayTrigger->update(['last_run_at' => now()]);

            return $emailsSent;

        } catch (\Exception $e) {
            $this->error('Birthday trigger failed: ' . $e->getMessage());
            return 0;
        }
    }
}