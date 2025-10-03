<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\EmailSettings;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailIntegrationService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = EmailSettings::getSettings();
    }

    /**
     * VERJAARDAGS EMAIL - Te gebruiken in je bestaande birthday cron/trigger
     */
    public function sendBirthdayEmail($customer): ?EmailLog
    {
        $template = EmailTemplate::where('type', EmailTemplate::TYPE_BIRTHDAY)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::info("Geen actieve verjaardag template gevonden voor {$customer->email}");
            return null;
        }

        $variables = [
            'voornaam' => $customer->voornaam ?? $customer->first_name ?? 'Beste klant',
            'naam' => $customer->naam ?? $customer->last_name ?? '',
            'email' => $customer->email,
            'bedrijf_naam' => $this->settings->company_name,
            'jaar' => date('Y'),
            'datum' => date('d-m-Y')
        ];

        return $this->sendTemplateEmail(
            $template,
            $customer->email,
            trim($variables['voornaam'] . ' ' . $variables['naam']),
            $variables,
            ['customer_id' => $customer->id, 'type' => 'birthday']
        );
    }

    /**
     * WELKOM NIEUWE KLANT - Te gebruiken wanneer nieuwe klant wordt aangemaakt
     */
    public function sendWelcomeCustomerEmail($customer): ?EmailLog
    {
        $template = EmailTemplate::where('type', EmailTemplate::TYPE_WELCOME_CUSTOMER)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::info("Geen actieve welkom klant template gevonden voor {$customer->email}");
            return null;
        }

        $variables = [
            'voornaam' => $customer->voornaam ?? $customer->first_name ?? 'Beste klant',
            'naam' => $customer->naam ?? $customer->last_name ?? '',
            'email' => $customer->email,
            'bedrijf_naam' => $this->settings->company_name,
            'jaar' => date('Y'),
            'datum' => date('d-m-Y')
        ];

        return $this->sendTemplateEmail(
            $template,
            $customer->email,
            trim($variables['voornaam'] . ' ' . $variables['naam']),
            $variables,
            ['customer_id' => $customer->id, 'type' => 'welcome_customer']
        );
    }

    /**
     * TESTZADEL HERINNERING - Te gebruiken voor uitstaande testzadels
     */
    public function sendTestzadelReminderEmail($testzadel, $customer): ?EmailLog
    {
        $template = EmailTemplate::where('type', EmailTemplate::TYPE_TESTZADEL_REMINDER)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::info("Geen actieve testzadel reminder template gevonden voor {$customer->email}");
            return null;
        }

        $uitgeleendOp = is_string($testzadel->uitgeleend_op) 
            ? \Carbon\Carbon::parse($testzadel->uitgeleend_op)
            : $testzadel->uitgeleend_op;

        $variables = [
            'voornaam' => $customer->voornaam ?? $customer->first_name ?? 'Beste klant',
            'naam' => $customer->naam ?? $customer->last_name ?? '',
            'email' => $customer->email,
            'merk' => $testzadel->merk ?? 'Onbekend merk',
            'model' => $testzadel->model ?? 'Onbekend model',
            'uitgeleend_op' => $uitgeleendOp->format('d-m-Y'),
            'verwachte_retour' => $uitgeleendOp->addDays(14)->format('d-m-Y'),
            'dagen_uit' => $uitgeleendOp->diffInDays(now()),
            'bedrijf_naam' => $this->settings->company_name,
            'jaar' => date('Y'),
            'datum' => date('d-m-Y')
        ];

        return $this->sendTemplateEmail(
            $template,
            $customer->email,
            trim($variables['voornaam'] . ' ' . $variables['naam']),
            $variables,
            [
                'customer_id' => $customer->id, 
                'testzadel_id' => $testzadel->id,
                'type' => 'testzadel_reminder'
            ]
        );
    }

    /**
     * WELKOM NIEUWE MEDEWERKER - Te gebruiken voor nieuwe staff
     */
    public function sendWelcomeEmployeeEmail($employee): ?EmailLog
    {
        $template = EmailTemplate::where('type', EmailTemplate::TYPE_WELCOME_EMPLOYEE)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::info("Geen actieve welkom medewerker template gevonden voor {$employee->email}");
            return null;
        }

        $variables = [
            'voornaam' => $employee->voornaam ?? $employee->first_name ?? $employee->name ?? 'Nieuwe collega',
            'naam' => $employee->naam ?? $employee->last_name ?? '',
            'email' => $employee->email,
            'bedrijf_naam' => $this->settings->company_name,
            'jaar' => date('Y'),
            'datum' => date('d-m-Y')
        ];

        return $this->sendTemplateEmail(
            $template,
            $employee->email,
            trim($variables['voornaam'] . ' ' . $variables['naam']),
            $variables,
            ['employee_id' => $employee->id, 'type' => 'welcome_employee']
        );
    }

    /**
     * Send customer invitation email
     */
    public function sendCustomerInvitationEmail($customer, $temporaryPassword, $invitationToken = null)
    {
        $template = EmailTemplate::where('type', 'welcome_customer')->where('is_active', true)->first();
        
        if (!$template) {
            return null;
        }

        $variables = [
            'voornaam' => $customer->voornaam ?? 'Beste klant',
            'naam' => $customer->naam ?? '',
            'email' => $customer->email,
            'temporary_password' => $temporaryPassword,
            'login_url' => url('/login'),
            'bedrijf_naam' => $this->getCompanyName(),
            'datum' => now()->format('d-m-Y'),
            'jaar' => now()->year,
        ];

        $processedTemplate = $this->processTemplate($template, $variables);
        
        return $this->sendEmail(
            $customer->email,
            $customer->voornaam . ' ' . $customer->naam,
            $processedTemplate['subject'],
            $processedTemplate['body'],
            $template->id
        );
    }

    /**
     * Send employee invitation email  
     */
    public function sendEmployeeInvitationEmail($employee, $temporaryPassword, $invitationToken = null)
    {
        $template = EmailTemplate::where('type', 'welcome_employee')->where('is_active', true)->first();
        
        if (!$template) {
            return null;
        }

        $variables = [
            'voornaam' => $employee->voornaam ?? 'Beste medewerker',
            'naam' => $employee->naam ?? '',
            'email' => $employee->email,
            'temporary_password' => $temporaryPassword,
            'login_url' => url('/login'),
            'bedrijf_naam' => $this->getCompanyName(),
            'datum' => now()->format('d-m-Y'),
            'jaar' => now()->year,
        ];

        $processedTemplate = $this->processTemplate($template, $variables);
        
        return $this->sendEmail(
            $employee->email,
            $employee->voornaam . ' ' . $employee->naam,
            $processedTemplate['subject'],
            $processedTemplate['body'],
            $template->id
        );
    }

    /**
     * Send testzadel reminder email
     */
    public function sendTestzadelReminderEmail($klant, $variables)
    {
        $template = EmailTemplate::where('name', 'testzadel_reminder')
            ->where('is_active', true)
            ->first();
            
        if (!$template) {
            \Log::warning('Testzadel reminder template not found or inactive');
            return false;
        }
        
        $processedTemplate = $this->processTemplate($template, $variables);
        
        return $this->sendEmail(
            $klant->email,
            $klant->voornaam . ' ' . $klant->naam,
            $processedTemplate['subject'],
            $processedTemplate['body'],
            $template->id
        );
    }

    /**
     * CORE EMAIL SENDING METHOD
     */
    private function sendTemplateEmail(
        EmailTemplate $template,
        string $recipientEmail,
        string $recipientName,
        array $variables = [],
        array $metadata = []
    ): EmailLog {
        // Render email content
        $subject = $template->renderSubject($variables);
        $bodyHtml = $template->renderBody($variables);

        // Create email log entry
        $emailLog = EmailLog::create([
            'email_template_id' => $template->id,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'status' => EmailLog::STATUS_PENDING,
            'trigger_type' => EmailLog::TRIGGER_MANUAL,
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
            Log::info("Template email sent successfully to {$recipientEmail} using template {$template->name}");

        } catch (\Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
            Log::error("Failed to send template email to {$recipientEmail}: " . $e->getMessage());
        }

        return $emailLog;
    }

    /**
     * BULK EMAIL FUNCTIONALITEIT
     */
    public function sendBulkEmail(EmailTemplate $template, array $recipients, array $globalVariables = []): array
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $recipientVariables = array_merge($globalVariables, [
                'voornaam' => $recipient['voornaam'] ?? $recipient['first_name'] ?? 'Beste klant',
                'naam' => $recipient['naam'] ?? $recipient['last_name'] ?? '',
                'email' => $recipient['email']
            ]);

            $result = $this->sendTemplateEmail(
                $template,
                $recipient['email'],
                trim($recipientVariables['voornaam'] . ' ' . $recipientVariables['naam']),
                $recipientVariables,
                ['type' => 'bulk_email', 'batch_id' => uniqid()]
            );

            $results[] = $result;
        }

        return $results;
    }

    /**
     * LEGACY SUPPORT - Verstuur email met oude view namen (backward compatibility)
     */
    public function sendLegacyEmail(string $oldViewName, $recipient, array $data = []): ?EmailLog
    {
        // Map old view names to new template types
        $templateMapping = [
            'emails.birthday' => EmailTemplate::TYPE_BIRTHDAY,
            'emails.account_created' => EmailTemplate::TYPE_GENERAL,
            'emails.klant-invitation' => EmailTemplate::TYPE_WELCOME_CUSTOMER,
            'emails.medewerker-invitation' => EmailTemplate::TYPE_WELCOME_EMPLOYEE,
            'emails.testzadel-reminder' => EmailTemplate::TYPE_TESTZADEL_REMINDER,
        ];

        $templateType = $templateMapping[$oldViewName] ?? null;
        
        if (!$templateType) {
            Log::warning("Geen template mapping gevonden voor legacy view: {$oldViewName}");
            return null;
        }

        $template = EmailTemplate::where('type', $templateType)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning("Geen actieve template gevonden voor type: {$templateType}");
            return null;
        }

        // Extract recipient info
        $email = is_array($recipient) ? $recipient['email'] : $recipient->email;
        $name = '';
        
        if (is_array($recipient)) {
            $name = ($recipient['voornaam'] ?? $recipient['first_name'] ?? '') . ' ' . 
                   ($recipient['naam'] ?? $recipient['last_name'] ?? '');
        } else {
            $name = ($recipient->voornaam ?? $recipient->first_name ?? '') . ' ' . 
                   ($recipient->naam ?? $recipient->last_name ?? '');
        }

        // Merge data with template variables
        $variables = array_merge([
            'voornaam' => is_array($recipient) ? ($recipient['voornaam'] ?? $recipient['first_name'] ?? 'Beste klant') : ($recipient->voornaam ?? $recipient->first_name ?? 'Beste klant'),
            'naam' => is_array($recipient) ? ($recipient['naam'] ?? $recipient['last_name'] ?? '') : ($recipient->naam ?? $recipient->last_name ?? ''),
            'email' => $email,
            'bedrijf_naam' => $this->settings->company_name,
            'jaar' => date('Y'),
            'datum' => date('d-m-Y')
        ], $data);

        return $this->sendTemplateEmail(
            $template,
            $email,
            trim($name),
            $variables,
            ['legacy_view' => $oldViewName, 'type' => 'legacy_migration']
        );
    }

    /**
     * Debug template processing
     */
    public function debugTemplate($template, $variables)
    {
        \Log::info('🔍 Template Debug:', [
            'template_subject' => $template->subject,
            'template_body_preview' => substr($template->body_html, 0, 200) . '...',
            'variables' => $variables,
        ]);
        
        $processed = $this->processTemplate($template, $variables);
        
        \Log::info('✅ Processed Template:', [
            'processed_subject' => $processed['subject'],
            'processed_body_preview' => substr($processed['body'], 0, 200) . '...',
        ]);
        
        return $processed;
    }

    /**
     * Process template by replacing variables
     */
    private function processTemplate($template, $variables)
    {
        $subject = $template->subject;
        $body = $template->body_html;
        
        // Replace all @{{variable}} patterns
        foreach ($variables as $key => $value) {
            $placeholder = '@{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }
        
        // Also replace {{variable}} patterns (without @)
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }
        
        return [
            'subject' => $subject,
            'body' => $body
        ];
    }

    /**
     * Send email using Laravel Mail
     */
    private function sendEmail($email, $name, $subject, $body, $templateId = null)
    {
        try {
            // Send the email
            Mail::send([], [], function ($message) use ($email, $name, $subject, $body) {
                $message->to($email, $name)
                       ->subject($subject)
                       ->html($body);
            });

            // Log the email (without template_id for now until migration runs)
            $emailLog = EmailLog::create([
                'recipient_email' => $email,
                'recipient_name' => $name,
                'subject' => $subject,
                'body_html' => $body,
                'status' => EmailLog::STATUS_SENT,
                'sent_at' => now(),
            ]);

            return $emailLog;

        } catch (\Exception $e) {
            // Log failed email (without template_id for now until migration runs)
            $emailLog = EmailLog::create([
                'recipient_email' => $email,
                'recipient_name' => $name,
                'subject' => $subject,
                'body_html' => $body,
                'status' => EmailLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            \Log::error('Email sending failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get company name from settings
     */
    private function getCompanyName()
    {
        $settings = EmailSettings::getSettings();
        return $settings->company_name ?? config('app.name', 'Bonami Cycling');
    }
}