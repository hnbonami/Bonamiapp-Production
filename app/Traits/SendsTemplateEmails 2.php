<?php

namespace App\Traits;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\EmailSettings;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

trait SendsTemplateEmails
{
    /**
     * Send email using template system
     */
    public function sendTemplateEmail(
        string $templateType,
        string $recipientEmail,
        string $recipientName,
        array $variables = [],
        array $metadata = []
    ): ?EmailLog {
        // Get template by type
        $template = EmailTemplate::where('type', $templateType)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning("No active template found for type: {$templateType}");
            return null;
        }

        // Get email settings
        $settings = EmailSettings::getSettings();

        // Add default variables
        $variables = array_merge([
            'bedrijf_naam' => $settings->company_name,
            'jaar' => date('Y'),
            'datum' => date('d-m-Y')
        ], $variables);

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
            Mail::html($bodyHtml, function ($message) use ($recipientEmail, $recipientName, $subject, $settings) {
                $message->to($recipientEmail, $recipientName)
                        ->subject($subject)
                        ->from(config('mail.from.address'), $settings->company_name);
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
     * Send birthday email
     */
    public function sendBirthdayEmail(string $email, string $name, array $extraVariables = []): ?EmailLog
    {
        $variables = array_merge([
            'voornaam' => $name,
            'naam' => '',
            'email' => $email
        ], $extraVariables);

        return $this->sendTemplateEmail(
            EmailTemplate::TYPE_BIRTHDAY,
            $email,
            $name,
            $variables,
            ['type' => 'birthday_email']
        );
    }

    /**
     * Send welcome customer email
     */
    public function sendWelcomeCustomerEmail(string $email, string $firstName, string $lastName = '', array $extraVariables = []): ?EmailLog
    {
        $variables = array_merge([
            'voornaam' => $firstName,
            'naam' => $lastName,
            'email' => $email
        ], $extraVariables);

        return $this->sendTemplateEmail(
            EmailTemplate::TYPE_WELCOME_CUSTOMER,
            $email,
            trim($firstName . ' ' . $lastName),
            $variables,
            ['type' => 'welcome_customer']
        );
    }

    /**
     * Send testzadel reminder email
     */
    public function sendTestzadelReminderEmail(
        string $email, 
        string $firstName, 
        string $lastName,
        string $merk,
        string $model,
        \DateTime $uitgeleendOp,
        array $extraVariables = []
    ): ?EmailLog {
        $variables = array_merge([
            'voornaam' => $firstName,
            'naam' => $lastName,
            'email' => $email,
            'merk' => $merk,
            'model' => $model,
            'uitgeleend_op' => $uitgeleendOp->format('d-m-Y'),
            'verwachte_retour' => $uitgeleendOp->modify('+14 days')->format('d-m-Y')
        ], $extraVariables);

        return $this->sendTemplateEmail(
            EmailTemplate::TYPE_TESTZADEL_REMINDER,
            $email,
            trim($firstName . ' ' . $lastName),
            $variables,
            ['type' => 'testzadel_reminder']
        );
    }

    /**
     * Send employee welcome email
     */
    public function sendWelcomeEmployeeEmail(string $email, string $firstName, string $lastName = '', array $extraVariables = []): ?EmailLog
    {
        $variables = array_merge([
            'voornaam' => $firstName,
            'naam' => $lastName,
            'email' => $email
        ], $extraVariables);

        return $this->sendTemplateEmail(
            EmailTemplate::TYPE_WELCOME_EMPLOYEE,
            $email,
            trim($firstName . ' ' . $lastName),
            $variables,
            ['type' => 'welcome_employee']
        );
    }
}