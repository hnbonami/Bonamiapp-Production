<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailIntegrationService
{
    public function __construct()
    {
        // No email settings dependency
    }

    /**
     * Send email using specific template type
     */
    public function sendTemplateEmail($templateType, $recipient, $variables = [])
    {
        try {
            $template = EmailTemplate::where('type', $templateType)
                ->where('is_active', true)
                ->first();
            
            if (!$template) {
                Log::error("Template not found for type: {$templateType}");
                return false;
            }
            
            // Render template with variables
            $subject = $template->renderSubject($variables);
            $htmlBody = $template->renderBody($variables);
            $textBody = strip_tags($htmlBody);
            
            // Send the email
            $emailSent = $this->sendEmail(
                $recipient['email'],
                $recipient['name'],
                $subject,
                $htmlBody,
                $textBody
            );
            
            if ($emailSent) {
                $this->logTrigger($templateType, $template->id, $recipient['email'], $variables);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Failed to send template email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send testzadel reminder email
     */
    public function sendTestzadelReminderEmail($klant, $variables = [])
    {
        $recipient = [
            'email' => $klant->email,
            'name' => $klant->voornaam . ' ' . $klant->naam
        ];
        
        return $this->sendTemplateEmail('testzadel_reminder', $recipient, $variables);
    }

    /**
     * Send birthday email
     */
    public function sendBirthdayEmail($klant, $variables = [])
    {
        $recipient = [
            'email' => $klant->email,
            'name' => $klant->voornaam . ' ' . $klant->naam
        ];
        
        return $this->sendTemplateEmail('birthday', $recipient, $variables);
    }

    /**
     * Send welcome email to new customer using EmailTemplate module
     */
    public function sendWelcomeEmail($klant, $variables = [])
    {
        try {
            // Look for template in EmailTemplate table
            $template = EmailTemplate::where('type', 'welcome_customer')
                ->where('is_active', true)
                ->first();
            
            if (!$template) {
                Log::error('Welcome customer template not found in EmailTemplate module');
                return false;
            }
            
            // Merge klant data with variables
            $allVariables = array_merge([
                'voornaam' => $klant->voornaam,
                'naam' => $klant->naam,
                'email' => $klant->email,
                'wachtwoord' => $variables['wachtwoord'] ?? $variables['temporary_password'] ?? '',
                'temporary_password' => $variables['temporary_password'] ?? $variables['wachtwoord'] ?? '',
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
            ], $variables);
            
            $recipient = [
                'email' => $klant->email,
                'name' => $klant->voornaam . ' ' . $klant->naam
            ];
            
            return $this->sendTemplateEmail('welcome_customer', $recipient, $allVariables);
            
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email to new employee using EmailTemplate module
     */
    public function sendEmployeeWelcomeEmail($medewerker, $variables = [])
    {
        try {
            // Look for template in EmailTemplate table
            $template = EmailTemplate::where('type', 'welcome_employee')
                ->where('is_active', true)
                ->first();
            
            if (!$template) {
                Log::error('Welcome employee template not found in EmailTemplate module');
                return false;
            }
            
            // Merge medewerker data with variables
            $allVariables = array_merge([
                'voornaam' => $medewerker->voornaam,
                'naam' => $medewerker->achternaam,
                'email' => $medewerker->email,
                'wachtwoord' => $variables['wachtwoord'] ?? $variables['temporary_password'] ?? '',
                'temporary_password' => $variables['temporary_password'] ?? $variables['wachtwoord'] ?? '',
                'functie' => $medewerker->functie ?? 'Medewerker',
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
            ], $variables);
            
            $recipient = [
                'email' => $medewerker->email,
                'name' => $medewerker->voornaam . ' ' . $medewerker->achternaam
            ];
            
            return $this->sendTemplateEmail('welcome_employee', $recipient, $allVariables);
            
        } catch (\Exception $e) {
            Log::error('Failed to send employee welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send actual email using Laravel Mail
     */
    private function sendEmail($toEmail, $toName, $subject, $htmlBody, $textBody = null)
    {
        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $subject, $htmlBody, $textBody) {
                $message->to($toEmail, $toName)
                    ->subject($subject)
                    ->html($htmlBody);
                
                if ($textBody) {
                    $message->text($textBody);
                }
                
                // Set from address from config or default
                $message->from(
                    config('mail.from.address', 'info@bonami-sportcoaching.be'),
                    config('mail.from.name', 'Bonami Sportcoaching')
                );
            });

            Log::info("Email sent successfully to {$toEmail}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log email trigger for statistics
     */
    private function logTrigger($triggerType, $templateId, $recipientEmail, $variables = [])
    {
        try {
            \App\Models\EmailLog::create([
                'trigger_name' => $triggerType,
                'template_id' => $templateId,
                'recipient_email' => $recipientEmail,
                'variables' => $variables,
                'sent_at' => now(),
                'status' => 'sent'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log email trigger: ' . $e->getMessage());
        }
    }
}