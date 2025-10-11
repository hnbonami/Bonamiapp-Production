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
    
    /**
     * Send welcome email (alias for backward compatibility)
     */
    public function sendWelcomeEmail($user, $template = null)
    {
        // Determine if this is a customer or employee based on the object
        if (isset($user->voornaam) && isset($user->naam)) {
            // This looks like a customer (klant)
            return $this->sendCustomerWelcomeEmail($user, $template);
        } else {
            // This might be an employee
            return $this->sendEmployeeWelcomeEmail($user, $template);
        }
    }
    
    /**
     * Send customer welcome email (backward compatibility alias)
     */
    public function sendCustomerWelcomeEmail($customer, $template = null)
    {
        try {
            \Log::info('🎯 SENDING CUSTOMER WELCOME EMAIL', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'template_provided' => $template ? 'yes' : 'no',
                'template_type' => is_array($template) ? 'array' : (is_object($template) ? get_class($template) : gettype($template)),
                'template_id' => is_object($template) ? $template->id : 'none'
            ]);

            if (empty($customer->email)) {
                \Log::warning('Customer has no email address for welcome email', [
                    'customer_id' => $customer->id
                ]);
                return false;
            }

            // If template is an array or not provided, try to find welcome_customer template
            if (!$template || is_array($template)) {
                \Log::info('Template is array or not provided, searching for welcome_customer template');
                $template = \App\Models\EmailTemplate::where('type', 'welcome_customer')
                                                   ->where('is_active', true)
                                                   ->first();
                \Log::info('Template search result', [
                    'found' => $template ? 'yes' : 'no',
                    'template_id' => $template->id ?? 'none'
                ]);
            }

            \Log::info('🚀 About to send customer welcome email to: ' . $customer->email);

            // Use the template subject and body_html from database
            $subject = $template ? $template->subject : 'Welkom bij ' . config('app.name', 'Bonami');
            $content = $this->processCustomerWelcomeTemplate($template, $customer);

            \Log::info('Email content prepared', [
                'subject' => $subject,
                'content_length' => strlen($content),
                'has_html' => strpos($content, '<') !== false
            ]);

            // Use Mail::html for proper HTML formatting
            \Illuminate\Support\Facades\Mail::html($content, function ($message) use ($customer, $subject) {
                $message->to($customer->email)
                        ->subject($subject);
            });

            \Log::info('✅ CUSTOMER WELCOME EMAIL SENT SUCCESSFULLY', [
                'to' => $customer->email,
                'customer_name' => $customer->voornaam . ' ' . $customer->naam,
                'subject' => $subject
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error('❌ FAILED TO SEND CUSTOMER WELCOME EMAIL: ' . $e->getMessage(), [
                'customer_id' => $customer->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send employee welcome email (backward compatibility alias)
     */
    public function sendEmployeeWelcomeEmail($employee, $template = null)
    {
        try {
            \Log::info('🎯 SENDING EMPLOYEE WELCOME EMAIL', [
                'employee_id' => $employee->id,
                'employee_email' => $employee->email,
                'template_provided' => $template ? 'yes' : 'no',
                'template_type' => is_array($template) ? 'array' : (is_object($template) ? get_class($template) : gettype($template)),
                'template_id' => is_object($template) ? $template->id : 'none'
            ]);

            if (empty($employee->email)) {
                \Log::warning('Employee has no email address for welcome email', [
                    'employee_id' => $employee->id
                ]);
                return false;
            }

            // If template is an array or not provided, try to find welcome_employee template
            if (!$template || is_array($template)) {
                \Log::info('Template is array or not provided, searching for welcome_employee template');
                $template = \App\Models\EmailTemplate::where('type', 'welcome_employee')
                                                   ->where('is_active', true)
                                                   ->first();
                \Log::info('Template search result', [
                    'found' => $template ? 'yes' : 'no',
                    'template_id' => $template->id ?? 'none'
                ]);
            }

            \Log::info('🚀 About to send employee welcome email to: ' . $employee->email);

            // Use the template subject and body_html from database
            $subject = $template ? $template->subject : 'Welkom in het team van ' . config('app.name', 'Bonami');
            $content = $this->processEmployeeWelcomeTemplate($template, $employee);

            \Log::info('Email content prepared', [
                'subject' => $subject,
                'content_length' => strlen($content),
                'has_html' => strpos($content, '<') !== false
            ]);

            // Use Mail::html for proper HTML formatting
            \Illuminate\Support\Facades\Mail::html($content, function ($message) use ($employee, $subject) {
                $message->to($employee->email)
                        ->subject($subject);
            });

            \Log::info('✅ EMPLOYEE WELCOME EMAIL SENT SUCCESSFULLY', [
                'to' => $employee->email,
                'employee_name' => $employee->voornaam . ' ' . $employee->achternaam,
                'subject' => $subject
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error('❌ FAILED TO SEND EMPLOYEE WELCOME EMAIL: ' . $e->getMessage(), [
                'employee_id' => $employee->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Process customer welcome template
     */
    private function processCustomerWelcomeTemplate($template, $customer)
    {
        if (!$template) {
            return "Welkom bij " . config('app.name', 'Bonami') . "!\n\nBeste " . $customer->voornaam . " " . $customer->naam . ",\n\nWelkom bij ons systeem!\n\nMet vriendelijke groet,\nHet " . config('app.name', 'Bonami') . " team";
        }

        // Use body_html from the EmailTemplate model
        $content = $template->body_html ?? $template->content ?? $template->inhoud ?? 'Welkom bij ons systeem!';

        // Get the temporary password from the latest invitation token for this customer
        $temporaryPassword = 'N/A';
        try {
            $invitationToken = \App\Models\InvitationToken::where('email', $customer->email)
                                                         ->where('type', 'klant')
                                                         ->latest()
                                                         ->first();
            if ($invitationToken && $invitationToken->temporary_password) {
                $temporaryPassword = $invitationToken->temporary_password;
            }
        } catch (\Exception $e) {
            \Log::warning('Could not retrieve temporary password for customer email template', [
                'customer_email' => $customer->email,
                'error' => $e->getMessage()
            ]);
        }

        // Replace placeholders using the @{{}} format
        $content = str_replace([
            '@{{voornaam}}',
            '@{{naam}}', 
            '@{{email}}',
            '@{{wachtwoord}}',
            '@{{temporary_password}}',
            '@{{bedrijf_naam}}',
            '@{{datum}}',
            '@{{jaar}}',
            // Also support old format for compatibility
            '{{customer_name}}',
            '{{klant_naam}}',
            '{{customer_email}}',
            '{{klant_email}}',
            '{{company_name}}',
            '{{app_name}}'
        ], [
            $customer->voornaam,
            $customer->naam,
            $customer->email,
            $temporaryPassword,
            $temporaryPassword,
            config('app.name', 'Bonami'),
            now()->format('d-m-Y'),
            now()->format('Y'),
            // Old format values
            $customer->voornaam . ' ' . $customer->naam,
            $customer->voornaam . ' ' . $customer->naam,
            $customer->email,
            $customer->email,
            config('app.name', 'Bonami'),
            config('app.name', 'Bonami')
        ], $content);

        return $content;
    }

    /**
     * Process employee welcome template
     */
    private function processEmployeeWelcomeTemplate($template, $employee)
    {
        if (!$template) {
            return "Welkom in het team van " . config('app.name', 'Bonami') . "!\n\nBeste " . $employee->voornaam . " " . $employee->achternaam . ",\n\nWelkom in ons team!\n\nMet vriendelijke groet,\nHet " . config('app.name', 'Bonami') . " team";
        }

        // Use body_html from the EmailTemplate model
        $content = $template->body_html ?? $template->content ?? $template->inhoud ?? 'Welkom in het team!';

        // Replace placeholders using the @{{}} format
        $content = str_replace([
            '@{{voornaam}}',
            '@{{naam}}',
            '@{{achternaam}}', 
            '@{{email}}',
            '@{{bedrijf_naam}}',
            '@{{datum}}',
            '@{{jaar}}',
            // Also support old format for compatibility
            '{{employee_name}}',
            '{{medewerker_naam}}',
            '{{employee_email}}',
            '{{medewerker_email}}',
            '{{company_name}}',
            '{{app_name}}'
        ], [
            $employee->voornaam,
            $employee->achternaam,
            $employee->achternaam,
            $employee->email,
            config('app.name', 'Bonami'),
            now()->format('d-m-Y'),
            now()->format('Y'),
            // Old format values
            $employee->voornaam . ' ' . $employee->achternaam,
            $employee->voornaam . ' ' . $employee->achternaam,
            $employee->email,
            $employee->email,
            config('app.name', 'Bonami'),
            config('app.name', 'Bonami')
        ], $content);

        return $content;
    }

    /**
     * Test email sending for debugging
     */
    public function testEmailSending($recipient = 'test@example.com')
    {
        try {
            \Log::info('🧪 TESTING EMAIL SENDING', [
                'recipient' => $recipient,
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
            ]);

            // Test 1: Simple text email
            \Illuminate\Support\Facades\Mail::raw('This is a test email from Bonami app', function ($message) use ($recipient) {
                $message->to($recipient)
                        ->subject('Test Email from Bonami');
            });

            \Log::info('✅ Test email sent successfully');
            return true;

        } catch (\Exception $e) {
            \Log::error('❌ Test email failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}