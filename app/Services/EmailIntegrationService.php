<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\Klant;
use App\Models\Testzadel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailIntegrationService
{
    /**
     * Send testzadel reminder email using template system
     */
    public function sendTestzadelReminderEmail($klant, $variables)
    {
        try {
            // Find active testzadel reminder template
            $template = EmailTemplate::where('type', 'testzadel_reminder')
                                    ->where('is_active', true)
                                    ->first();
            
            if (!$template) {
                Log::error('No active testzadel reminder template found');
                return false;
            }
            
            // Render subject and body with variables
            $subject = $template->renderSubject($variables);
            $body = $template->renderBody($variables);
            
            Log::info('Sending testzadel reminder email', [
                'to' => $klant->email,
                'subject' => $subject,
                'template_id' => $template->id
            ]);
            
            // Send email
            Mail::html($body, function ($message) use ($klant, $subject) {
                $message->to($klant->email, $klant->voornaam . ' ' . $klant->naam)
                        ->subject($subject);
            });
            
            // Try to log the email - don't fail if logging fails
            try {
                EmailLog::create([
                    'recipient_email' => $klant->email,
                    'subject' => $subject,
                    'template_id' => $template->id,
                    'trigger_name' => 'testzadel_reminder',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'variables' => $variables
                ]);
            } catch (\Exception $logError) {
                Log::warning('Failed to log email (email was sent successfully): ' . $logError->getMessage());
            }
            
            Log::info('Testzadel reminder email sent successfully', [
                'recipient' => $klant->email,
                'template' => $template->name
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send testzadel reminder email: ' . $e->getMessage(), [
                'recipient' => $klant->email ?? 'unknown',
                'variables' => $variables,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Log failed email - don't fail the whole process if logging fails
            try {
                EmailLog::create([
                    'recipient_email' => $klant->email ?? 'unknown',
                    'subject' => $subject ?? 'Testzadel Herinnering',
                    'template_id' => $template->id ?? null,
                    'trigger_name' => 'testzadel_reminder',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'variables' => $variables
                ]);
            } catch (\Exception $logError) {
                Log::error('Failed to log email error: ' . $logError->getMessage());
            }
            
            return false;
        }
    }
    
    /**
     * Send birthday email
     */
    public function sendBirthdayEmail($klant, $variables)
    {
        try {
            $template = EmailTemplate::where('type', 'birthday')
                                    ->where('is_active', true)
                                    ->first();
            
            if (!$template) {
                Log::error('No active birthday template found');
                return false;
            }
            
            $subject = $template->renderSubject($variables);
            $body = $template->renderBody($variables);
            
            Mail::html($body, function ($message) use ($klant, $subject) {
                $message->to($klant->email, $klant->voornaam . ' ' . $klant->naam)
                        ->subject($subject);
            });
            
            EmailLog::create([
                'recipient_email' => $klant->email,
                'subject' => $subject,
                'template_id' => $template->id,
                'trigger_name' => 'birthday',
                'status' => 'sent',
                'sent_at' => now(),
                'variables' => $variables
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send birthday email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send welcome customer email
     */
    public function sendWelcomeCustomerEmail($klant, $variables)
    {
        try {
            $template = EmailTemplate::where('type', 'welcome_customer')
                                    ->where('is_active', true)
                                    ->first();
            
            if (!$template) {
                Log::error('No active welcome customer template found');
                return false;
            }
            
            $subject = $template->renderSubject($variables);
            $body = $template->renderBody($variables);
            
            Mail::html($body, function ($message) use ($klant, $subject) {
                $message->to($klant->email, $klant->voornaam . ' ' . $klant->naam)
                        ->subject($subject);
            });
            
            EmailLog::create([
                'recipient_email' => $klant->email,
                'subject' => $subject,
                'template_id' => $template->id,
                'trigger_name' => 'welcome_customer',
                'status' => 'sent',
                'sent_at' => now(),
                'variables' => $variables
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send welcome customer email: ' . $e->getMessage());
            return false;
        }
    }
}