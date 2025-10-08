<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailTrigger;
use App\Models\EmailLog;
use App\Models\EmailSettings;
use App\Models\Customer;
use App\Models\Testzadel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = EmailSettings::getSettings();
    }

    /**
     * Send an email using a template
     */
    public function sendEmail(
        EmailTemplate $template,
        string $recipientEmail,
        string $recipientName,
        array $variables = [],
        ?EmailTrigger $trigger = null,
        string $triggerType = EmailLog::TRIGGER_MANUAL,
        array $metadata = []
    ): EmailLog {
        // Add default variables
        $variables = array_merge([
            'bedrijf_naam' => $this->settings->company_name,
            'jaar' => date('Y'),
            'datum' => date('d-m-Y')
        ], $variables);

        // Render email content
        $subject = $template->renderSubject($variables);
        $bodyHtml = $template->renderBody($variables);

        // Create email log entry
        $emailLog = EmailLog::create([
            'email_template_id' => $template->id,
            'email_trigger_id' => $trigger?->id,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'status' => EmailLog::STATUS_PENDING,
            'trigger_type' => $triggerType,
            'metadata' => $metadata
        ]);

        try {
            // Send email using Laravel Mail
            Mail::html($bodyHtml, function ($message) use ($recipientEmail, $recipientName, $subject) {
                $message->to($recipientEmail, $recipientName)
                        ->subject($subject)
                        ->from(config('mail.from.address'), $this->settings->company_name);
            });

            $emailLog->markAsSent();
            Log::info("Email sent successfully to {$recipientEmail}");

        } catch (\Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
            Log::error("Failed to send email to {$recipientEmail}: " . $e->getMessage());
        }

        return $emailLog;
    }

    /**
     * Run testzadel reminder triggers
     */
    public static function runTestzadelReminders()
    {
        try {
            // Get overdue testzadels with automatic mailing enabled
            $overdueTestzadels = \App\Models\Testzadel::where('status', 'uitgeleend')
                ->where('automatisch_mailtje', true)
                ->where('verwachte_retour_datum', '<', now())
                ->whereNull('laatste_herinnering')
                ->orWhere('laatste_herinnering', '<', now()->subDays(7))
                ->get();
                
            $emailService = new \App\Services\EmailIntegrationService();
            $sentCount = 0;
            
            foreach ($overdueTestzadels as $testzadel) {
                try {
                    $variables = [
                        'voornaam' => $testzadel->klant->voornaam,
                        'naam' => $testzadel->klant->naam,
                        'email' => $testzadel->klant->email,
                        'merk' => $testzadel->zadel_merk,
                        'model' => $testzadel->zadel_model,
                        'type' => $testzadel->zadel_type,
                        'breedte' => $testzadel->zadel_breedte,
                        'uitgeleend_op' => $testzadel->uitleen_datum->format('d/m/Y'),
                        'verwachte_retour' => $testzadel->verwachte_retour_datum->format('d/m/Y'),
                        'bedrijf_naam' => 'Bonami Sportcoaching',
                        'datum' => now()->format('d/m/Y'),
                        'jaar' => now()->format('Y'),
                    ];
                    
                    $emailResult = $emailService->sendTestzadelReminderEmail(
                        $testzadel->klant,
                        $variables
                    );
                    
                    if ($emailResult) {
                        $testzadel->update([
                            'herinnering_verstuurd' => true,
                            'herinnering_verstuurd_op' => now(),
                            'laatste_herinnering' => now()
                        ]);
                        $sentCount++;
                    }
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to send automatic reminder for testzadel ' . $testzadel->id . ': ' . $e->getMessage());
                }
            }
            
            return $sentCount;
            
        } catch (\Exception $e) {
            \Log::error('Failed to run testzadel reminders: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Run birthday email triggers
     */
    public function runBirthdayEmails(): int
    {
        $triggers = EmailTrigger::active()
            ->byType(EmailTrigger::TYPE_BIRTHDAY)
            ->get();

        $totalSent = 0;

        foreach ($triggers as $trigger) {
            $template = $trigger->emailTemplate;
            if (!$template || !$template->is_active) {
                continue;
            }

            // Get customers with birthday today
            $customers = Customer::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [date('m-d')])
                ->whereDoesntHave('emailLogs', function($query) use ($trigger) {
                    $query->where('email_trigger_id', $trigger->id)
                          ->where('status', EmailLog::STATUS_SENT)
                          ->whereYear('created_at', date('Y')); // Only one per year
                })
                ->get();

            foreach ($customers as $customer) {
                $variables = [
                    'voornaam' => $customer->voornaam,
                    'naam' => $customer->naam,
                    'email' => $customer->email
                ];

                $metadata = [
                    'customer_id' => $customer->id,
                    'birthday_year' => date('Y')
                ];

                $this->sendEmail(
                    $template,
                    $customer->email,
                    $customer->voornaam . ' ' . $customer->naam,
                    $variables,
                    $trigger,
                    EmailLog::TRIGGER_AUTOMATIC,
                    $metadata
                );

                $totalSent++;
            }

            $trigger->updateLastRun();
            $trigger->incrementEmailsSent($customers->count());
        }

        return $totalSent;
    }

    /**
     * Send welcome email to new customer
     */
    public static function sendWelcomeEmail($klant, $temporaryPassword = null)
    {
        try {
            $emailService = new \App\Services\EmailIntegrationService();
            
            $variables = [
                'voornaam' => $klant->voornaam,
                'naam' => $klant->naam,
                'email' => $klant->email,
                'temporary_password' => $temporaryPassword,
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
            ];
            
            return $emailService->sendWelcomeEmail($klant, $variables);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_sent' => EmailLog::sent()->count(),
            'sent_today' => EmailLog::sent()->today()->count(),
            'sent_this_week' => EmailLog::sent()->thisWeek()->count(),
            'failed_count' => EmailLog::failed()->count(),
            'open_rate' => $this->calculateOpenRate(),
            'click_rate' => $this->calculateClickRate(),
            'recent_emails' => EmailLog::with(['emailTemplate', 'emailTrigger'])
                ->latest()
                ->limit(10)
                ->get()
        ];
    }

    private function calculateOpenRate(): float
    {
        $totalSent = EmailLog::sent()->count();
        if ($totalSent === 0) return 0;

        $opened = EmailLog::sent()->whereNotNull('opened_at')->count();
        return round(($opened / $totalSent) * 100, 1);
    }

    private function calculateClickRate(): float
    {
        $totalSent = EmailLog::sent()->count();
        if ($totalSent === 0) return 0;

        $clicked = EmailLog::sent()->whereNotNull('clicked_at')->count();
        return round(($clicked / $totalSent) * 100, 1);
    }

    /**
     * Run birthday reminder triggers
     */
    public static function runBirthdayReminders()
    {
        try {
            // Get customers with birthday today
            $customers = \App\Models\Klant::whereMonth('geboortedatum', now()->month)
                                         ->whereDay('geboortedatum', now()->day)
                                         ->get();
            
            $emailService = new \App\Services\EmailIntegrationService();
            $sentCount = 0;
            
            foreach ($customers as $customer) {
                try {
                    $variables = [
                        'voornaam' => $customer->voornaam,
                        'naam' => $customer->naam,
                        'email' => $customer->email,
                        'bedrijf_naam' => 'Bonami Sportcoaching',
                        'datum' => now()->format('d/m/Y'),
                        'jaar' => now()->format('Y'),
                    ];
                    
                    if ($emailService->sendBirthdayEmail($customer, $variables)) {
                        $sentCount++;
                    }
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to send birthday email for customer ' . $customer->id . ': ' . $e->getMessage());
                }
            }
            
            return $sentCount;
            
        } catch (\Exception $e) {
            \Log::error('Failed to run birthday reminders: ' . $e->getMessage());
            return 0;
        }
    }
}