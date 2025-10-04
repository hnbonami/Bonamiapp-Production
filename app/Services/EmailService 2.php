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
    public function runTestzadelReminders(): int
    {
        $triggers = EmailTrigger::active()
            ->byType(EmailTrigger::TYPE_TESTZADEL_REMINDER)
            ->where(function($query) {
                $query->whereNull('last_run_at')
                      ->orWhere('last_run_at', '<=', now()->subDay());
            })
            ->get();

        $totalSent = 0;

        foreach ($triggers as $trigger) {
            if (!$trigger->shouldRun()) {
                continue;
            }

            $template = $trigger->emailTemplate;
            if (!$template || !$template->is_active) {
                continue;
            }

            // Get testzadels that need reminders
            $reminderDays = $trigger->conditions['reminder_days'] ?? 7;
            $testzadels = Testzadel::with('customer')
                ->where('status', 'uitgeleend')
                ->where('uitgeleend_op', '<=', now()->subDays($reminderDays))
                ->whereDoesntHave('emailLogs', function($query) use ($trigger) {
                    $query->where('email_trigger_id', $trigger->id)
                          ->where('status', EmailLog::STATUS_SENT)
                          ->where('created_at', '>=', now()->subDays(7)); // Don't spam
                })
                ->get();

            foreach ($testzadels as $testzadel) {
                if (!$testzadel->customer) continue;

                $variables = [
                    'voornaam' => $testzadel->customer->voornaam,
                    'naam' => $testzadel->customer->naam,
                    'email' => $testzadel->customer->email,
                    'merk' => $testzadel->merk,
                    'model' => $testzadel->model,
                    'uitgeleend_op' => $testzadel->uitgeleend_op->format('d-m-Y'),
                    'verwachte_retour' => $testzadel->uitgeleend_op->addDays(14)->format('d-m-Y')
                ];

                $metadata = [
                    'customer_id' => $testzadel->customer->id,
                    'testzadel_id' => $testzadel->id
                ];

                $this->sendEmail(
                    $template,
                    $testzadel->customer->email,
                    $testzadel->customer->voornaam . ' ' . $testzadel->customer->naam,
                    $variables,
                    $trigger,
                    EmailLog::TRIGGER_AUTOMATIC,
                    $metadata
                );

                $totalSent++;
            }

            $trigger->updateLastRun();
            $trigger->incrementEmailsSent($testzadels->count());
        }

        return $totalSent;
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
    public function sendWelcomeEmail(Customer $customer): ?EmailLog
    {
        $trigger = EmailTrigger::active()
            ->byType(EmailTrigger::TYPE_WELCOME_CUSTOMER)
            ->first();

        if (!$trigger || !$trigger->emailTemplate || !$trigger->emailTemplate->is_active) {
            return null;
        }

        $variables = [
            'voornaam' => $customer->voornaam,
            'naam' => $customer->naam,
            'email' => $customer->email
        ];

        $metadata = [
            'customer_id' => $customer->id
        ];

        return $this->sendEmail(
            $trigger->emailTemplate,
            $customer->email,
            $customer->voornaam . ' ' . $customer->naam,
            $variables,
            $trigger,
            EmailLog::TRIGGER_AUTOMATIC,
            $metadata
        );
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
}