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
                    'recipient_name' => $klant->voornaam . ' ' . $klant->naam,
                    'subject' => $subject,
                    'body_html' => $body,
                    'email_template_id' => $template->id,
                    'trigger_name' => 'testzadel_reminder',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'variables' => $variables
                ]);
                Log::info('✅ Email logged successfully', ['recipient' => $klant->email]);
            } catch (\Exception $logError) {
                Log::warning('Failed to log email (email was sent successfully): ' . $logError->getMessage());
                // Fallback: direct DB insert
                try {
                    \DB::table('email_logs')->insert([
                        'recipient_email' => $klant->email,
                        'recipient_name' => $klant->voornaam . ' ' . $klant->naam,
                        'subject' => $subject,
                        'body_html' => $body ?? '',
                        'email_template_id' => $template->id,
                        'trigger_name' => 'testzadel_reminder',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    Log::info('✅ Email logged via direct DB insert');
                } catch (\Exception $dbError) {
                    Log::error('❌ Both EmailLog model and direct DB insert failed: ' . $dbError->getMessage());
                }
            }
            
            Log::info('Testzadel reminder email sent successfully', [
                'recipient' => $klant->email,
                'template' => $template->name
            ]);
            
            // Update trigger statistics
            $this->updateTriggerStats('testzadel_reminder', true);
            
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
                    'body_html' => '',
                    'email_template_id' => $template->id ?? null,
                    'trigger_name' => 'testzadel_reminder',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'variables' => $variables
                ]);
            } catch (\Exception $logError) {
                Log::error('Failed to log email error: ' . $logError->getMessage());
            }
            
            // Update trigger statistics with failure
            $this->updateTriggerStats('testzadel_reminder', false);
            
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
            
            // Log the email
            try {
                EmailLog::create([
                    'recipient_email' => $klant->email,
                    'recipient_name' => $klant->voornaam . ' ' . $klant->naam,
                    'subject' => $subject,
                    'body_html' => $body,
                    'email_template_id' => $template->id,
                    'trigger_name' => 'birthday',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'variables' => $variables
                ]);
                Log::info('✅ Birthday email logged successfully', ['recipient' => $klant->email]);
            } catch (\Exception $logError) {
                Log::warning('Failed to log birthday email: ' . $logError->getMessage());
                // Fallback: direct DB insert
                try {
                    \DB::table('email_logs')->insert([
                        'recipient_email' => $klant->email,
                        'recipient_name' => $klant->voornaam . ' ' . $klant->naam,
                        'subject' => $subject,
                        'body_html' => $body ?? '',
                        'email_template_id' => $template->id,
                        'trigger_name' => 'birthday',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    Log::info('✅ Birthday email logged via direct DB insert');
                } catch (\Exception $dbError) {
                    Log::error('❌ Birthday email logging failed completely: ' . $dbError->getMessage());
                }
            }
            
            // Update trigger statistics
            $this->updateTriggerStats('birthday', true);
            
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
            
            // Log the email
            try {
                EmailLog::create([
                    'recipient_email' => $klant->email,
                    'recipient_name' => $klant->voornaam . ' ' . $klant->naam,
                    'subject' => $subject,
                    'body_html' => $body,
                    'email_template_id' => $template->id,
                    'trigger_name' => 'welcome_customer',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'variables' => $variables
                ]);
                Log::info('✅ Welcome customer email logged successfully', ['recipient' => $klant->email]);
            } catch (\Exception $logError) {
                Log::warning('Failed to log welcome customer email: ' . $logError->getMessage());
                // Fallback: direct DB insert
                try {
                    \DB::table('email_logs')->insert([
                        'recipient_email' => $klant->email,
                        'recipient_name' => $klant->voornaam . ' ' . $klant->naam,
                        'subject' => $subject,
                        'body_html' => $body ?? '',
                        'email_template_id' => $template->id,
                        'trigger_name' => 'welcome_customer',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    Log::info('✅ Welcome customer email logged via direct DB insert');
                } catch (\Exception $dbError) {
                    Log::error('❌ Welcome customer email logging failed completely: ' . $dbError->getMessage());
                }
            }
            
            // Update trigger statistics
            $this->updateTriggerStats('welcome_customer', true);
            
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

            
            \Log::info('🚀 FORCE LOG ENTRY CREATED');

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

            // Update trigger statistics directly
            $this->updateTriggerStats('welcome_customer', true);
            
            // Also log manually for backup
            try {
                EmailLog::create([
                    'recipient_email' => $customer->email,
                    'recipient_name' => $customer->voornaam . ' ' . $customer->naam,
                    'subject' => $subject,
                    'body_html' => $content ?? '',
                    'email_template_id' => $template->id ?? null,
                    'trigger_name' => 'welcome_customer',
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
                \Log::info('✅ Customer welcome email logged via EmailLog model');
            } catch (\Exception $logError) {
                \Log::warning('EmailLog model failed, trying direct DB insert: ' . $logError->getMessage());
                try {
                    \DB::table('email_logs')->insert([
                        'recipient_email' => $customer->email,
                        'recipient_name' => $customer->voornaam . ' ' . $customer->naam,
                        'subject' => $subject,
                        'body_html' => $content ?? '',
                        'email_template_id' => $template->id ?? null,
                        'trigger_name' => 'welcome_customer',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    \Log::info('✅ Customer welcome email logged via direct DB insert');
                } catch (\Exception $dbError) {
                    \Log::error('❌ All logging methods failed: ' . $dbError->getMessage());
                }
            }

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

            // Update trigger statistics
            $this->updateTriggerStats('welcome_employee', true);
            
            // Also log manually for backup
            try {
                EmailLog::create([
                    'recipient_email' => $employee->email,
                    'recipient_name' => $employee->voornaam . ' ' . $employee->achternaam,
                    'subject' => $subject,
                    'body_html' => $content ?? '',
                    'email_template_id' => $template->id ?? null,
                    'trigger_name' => 'welcome_employee',
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
                \Log::info('✅ Employee welcome email logged via EmailLog model');
            } catch (\Exception $logError) {
                \Log::warning('EmailLog model failed, trying direct DB insert: ' . $logError->getMessage());
                try {
                    \DB::table('email_logs')->insert([
                        'recipient_email' => $employee->email,
                        'recipient_name' => $employee->voornaam . ' ' . $employee->achternaam,
                        'subject' => $subject,
                        'body_html' => $content ?? '',
                        'email_template_id' => $template->id ?? null,
                        'trigger_name' => 'welcome_employee',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    \Log::info('✅ Employee welcome email logged via direct DB insert');
                } catch (\Exception $dbError) {
                    \Log::error('❌ All employee logging methods failed: ' . $dbError->getMessage());
                }
            }

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
            '@{{website_url}}',
            '@{{datum}}',
            '@{{jaar}}',
            '@{{tijd}}',
            '@{{unsubscribe_url}}',
            '@{{marketing_unsubscribe_url}}',
            '@{{preferences_url}}',
            '@{{email_id}}',
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
            config('app.url', 'https://bonami-sportcoaching.be'),
            now()->format('d-m-Y'),
            now()->format('Y'),
            now()->format('H:i'),
            $this->generateSafeRoute('unsubscribe', ['email' => $customer->email, 'token' => $this->generateUnsubscribeToken($customer->email)]),
            $this->generateSafeRoute('marketing.unsubscribe', ['email' => $customer->email, 'token' => $this->generateUnsubscribeToken($customer->email)]),
            $this->generateSafeRoute('email.preferences', ['email' => $customer->email, 'token' => $this->generateUnsubscribeToken($customer->email)]),
            'CUST-' . $customer->id . '-' . time(),
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
            '@{{website_url}}',
            '@{{datum}}',
            '@{{jaar}}',
            '@{{tijd}}',
            '@{{unsubscribe_url}}',
            '@{{marketing_unsubscribe_url}}',
            '@{{preferences_url}}',
            '@{{email_id}}',
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
            config('app.url', 'https://bonami-sportcoaching.be'),
            now()->format('d-m-Y'),
            now()->format('Y'),
            now()->format('H:i'),
            $this->generateSafeRoute('unsubscribe', ['email' => $employee->email, 'token' => $this->generateUnsubscribeToken($employee->email)]),
            $this->generateSafeRoute('marketing.unsubscribe', ['email' => $employee->email, 'token' => $this->generateUnsubscribeToken($employee->email)]),
            $this->generateSafeRoute('email.preferences', ['email' => $employee->email, 'token' => $this->generateUnsubscribeToken($employee->email)]),
            'EMP-' . $employee->id . '-' . time(),
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

    /**
     * Update trigger statistics after sending email
     */
    private function updateTriggerStats($triggerType, $success = true)
    {
        try {
            // Find or create trigger record
            $trigger = \App\Models\EmailTrigger::firstOrCreate(
                ['type' => $triggerType],
                [
                    'name' => ucfirst(str_replace('_', ' ', $triggerType)),
                    'description' => 'Auto-generated trigger for ' . $triggerType,
                    'is_active' => true,
                    'emails_sent' => 0
                ]
            );
            
            // Update statistics
            if ($success) {
                $trigger->increment('emails_sent');
            }
            $trigger->update(['last_run_at' => now()]);
            
            \Log::info('Trigger statistics updated', [
                'trigger_type' => $triggerType,
                'emails_sent' => $trigger->emails_sent,
                'success' => $success
            ]);
            
        } catch (\Exception $e) {
            \Log::warning('Failed to update trigger statistics', [
                'trigger_type' => $triggerType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manual trigger stats update - use this after sending any email
     */
    public function incrementTriggerStats($triggerType)
    {
        $this->updateTriggerStats($triggerType, true);
    }

    /**
     * Force update trigger statistics with actual email counts
     */
    public function forceUpdateTriggerStats()
    {
        try {
            // Count emails by trigger_name from email_logs
            $triggers = [
                'welcome_customer' => \App\Models\EmailLog::where('trigger_name', 'welcome_customer')->count(),
                'welcome_employee' => \App\Models\EmailLog::where('trigger_name', 'welcome_employee')->count(),
                'testzadel_reminder' => \App\Models\EmailLog::where('trigger_name', 'testzadel_reminder')->count(),
                'birthday' => \App\Models\EmailLog::where('trigger_name', 'birthday')->count(),
            ];
            
            foreach ($triggers as $type => $count) {
                if ($count > 0) {
                    $trigger = \App\Models\EmailTrigger::firstOrCreate(
                        ['type' => $type],
                        [
                            'name' => ucfirst(str_replace('_', ' ', $type)),
                            'description' => 'Auto-generated trigger for ' . $type,
                            'is_active' => true,
                        ]
                    );
                    
                    $trigger->update([
                        'emails_sent' => $count,
                        'last_run_at' => now()
                    ]);
                    
                    \Log::info("Updated trigger {$type}: {$count} emails");
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Force update trigger stats failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Debug email logging issues
     */
    public function debugEmailLogging()
    {
        try {
            \Log::info('🔍 DEBUGGING EMAIL LOGGING SYSTEM');
            
            // Check if EmailLog model exists and is accessible
            $emailLogExists = class_exists(\App\Models\EmailLog::class);
            \Log::info('EmailLog model exists: ' . ($emailLogExists ? 'YES' : 'NO'));
            
            // Check database connection
            try {
                $dbConnected = \DB::connection()->getPdo();
                \Log::info('Database connection: OK');
            } catch (\Exception $e) {
                \Log::error('Database connection: FAILED - ' . $e->getMessage());
                return false;
            }
            
            // Check if email_logs table exists
            try {
                $tableExists = \Schema::hasTable('email_logs');
                \Log::info('email_logs table exists: ' . ($tableExists ? 'YES' : 'NO'));
                
                if ($tableExists) {
                    // Check table structure
                    $columns = \Schema::getColumnListing('email_logs');
                    \Log::info('email_logs columns: ' . implode(', ', $columns));
                    
                    // Count existing records
                    $count = \DB::table('email_logs')->count();
                    \Log::info('Existing email_logs count: ' . $count);
                    
                    // Get latest records
                    $latest = \DB::table('email_logs')->latest('created_at')->limit(3)->get();
                    \Log::info('Latest 3 records: ', $latest->toArray());
                }
                
            } catch (\Exception $e) {
                \Log::error('email_logs table check failed: ' . $e->getMessage());
            }
            
            // Test direct insert
            try {
                \DB::table('email_logs')->insert([
                    'recipient_email' => 'debug@test.com',
                    'recipient_name' => 'Debug Test',
                    'subject' => 'Debug Test Email',
                    'body_html' => '<p>Debug test email content</p>',
                    'trigger_name' => 'debug_test',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                \Log::info('✅ Direct DB insert test: SUCCESS');
                
                // Clean up test record
                \DB::table('email_logs')->where('recipient_email', 'debug@test.com')->delete();
                
            } catch (\Exception $e) {
                \Log::error('❌ Direct DB insert test: FAILED - ' . $e->getMessage());
            }
            
            // Test EmailLog model
            if ($emailLogExists) {
                try {
                    $testLog = \App\Models\EmailLog::create([
                        'recipient_email' => 'model-test@test.com',
                        'recipient_name' => 'Model Test',
                        'subject' => 'Model Test Email',
                        'body_html' => '<p>Model test email content</p>',
                        'trigger_name' => 'model_test',
                        'status' => 'sent',
                        'sent_at' => now()
                    ]);
                    \Log::info('✅ EmailLog model test: SUCCESS');
                    
                    // Clean up
                    $testLog->delete();
                    
                } catch (\Exception $e) {
                    \Log::error('❌ EmailLog model test: FAILED - ' . $e->getMessage());
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Debug email logging failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate secure unsubscribe token for email
     */
    private function generateUnsubscribeToken($email)
    {
        // Create a secure token based on email and app key
        return hash('sha256', $email . config('app.key') . date('Y-m'));
    }

    /**
     * Generate safe route URLs that don't crash if routes don't exist
     */
    private function generateSafeRoute($routeName, $parameters = [])
    {
        try {
            if (\Route::has($routeName)) {
                return route($routeName, $parameters);
            } else {
                // Fallback URLs
                $email = $parameters['email'] ?? 'unknown';
                $token = $parameters['token'] ?? 'no-token';
                
                switch($routeName) {
                    case 'unsubscribe':
                        return config('app.url') . '/unsubscribe?email=' . urlencode($email) . '&token=' . $token;
                    case 'marketing.unsubscribe':
                        return config('app.url') . '/marketing/unsubscribe?email=' . urlencode($email) . '&token=' . $token;
                    case 'email.preferences':
                        return config('app.url') . '/email/preferences?email=' . urlencode($email) . '&token=' . $token;
                    default:
                        return config('app.url') . '/unsubscribe?email=' . urlencode($email);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Route generation failed for ' . $routeName . ': ' . $e->getMessage());
            return config('app.url') . '/unsubscribe';
        }
    }

    /**
     * Quick test method to debug welcome email issues
     */
    public function testWelcomeEmailSending()
    {
        try {
            \Log::info('🧪 TESTING WELCOME EMAIL SYSTEM');
            
            // Test 1: Check if we can find customers
            $customer = \App\Models\Klant::first();
            if (!$customer) {
                \Log::error('❌ No customers found for testing');
                return false;
            }
            
            \Log::info('✅ Found test customer: ' . $customer->email);
            
            // Test 2: Check if we can find welcome template
            $template = \App\Models\EmailTemplate::where('type', 'welcome_customer')
                                               ->where('is_active', true)
                                               ->first();
            
            if (!$template) {
                \Log::warning('⚠️ No active welcome_customer template found');
                
                // Check if any welcome template exists
                $anyTemplate = \App\Models\EmailTemplate::where('type', 'welcome_customer')->first();
                if ($anyTemplate) {
                    \Log::info('Found inactive welcome template: ' . $anyTemplate->name);
                } else {
                    \Log::error('❌ No welcome_customer template exists at all');
                }
            } else {
                \Log::info('✅ Found active welcome template: ' . $template->name);
            }
            
            // Test 3: Try to send test email
            \Log::info('🚀 Attempting to send test welcome email...');
            $result = $this->sendCustomerWelcomeEmail($customer);
            
            if ($result) {
                \Log::info('✅ Test welcome email sent successfully!');
            } else {
                \Log::error('❌ Test welcome email failed');
            }
            
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('❌ Welcome email test failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send customer referral thank you email - NIEUWE FUNCTIONALITEIT
     * Voorzichtig toegevoegd zonder bestaande systeem te verstoren
     */
    public function sendReferralThankYouEmail($referringCustomer, $referredCustomer)
    {
        try {
            \Log::info('🎉 SENDING REFERRAL THANK YOU EMAIL', [
                'referring_customer' => $referringCustomer->email,
                'referred_customer' => $referredCustomer->email
            ]);

            // Zoek active referral thank you template
            $template = EmailTemplate::where('type', 'referral_thank_you')
                                    ->where('is_active', true)
                                    ->first();
            
            if (!$template) {
                \Log::warning('⚠️ No active referral thank you template found');
                return false;
            }

            // Bereid variabelen voor
            $variables = [
                'voornaam' => $referringCustomer->voornaam,
                'naam' => $referringCustomer->naam,
                'email' => $referringCustomer->email,
                'referred_customer_name' => $referredCustomer->voornaam . ' ' . $referredCustomer->naam,
                'referred_customer_email' => $referredCustomer->email,
                'referral_date' => now()->format('d-m-Y'),
                'datum' => now()->format('d-m-Y'),
                'tijd' => now()->format('H:i'),
                'bedrijf_naam' => config('app.name', 'Bonami'),
                'website_url' => config('app.url', 'https://bonami-sportcoaching.be'),
                'email_id' => 'REF-' . $referringCustomer->id . '-' . $referredCustomer->id . '-' . time(),
                'unsubscribe_url' => $this->generateSafeRoute('unsubscribe', ['email' => $referringCustomer->email, 'token' => $this->generateUnsubscribeToken($referringCustomer->email)])
            ];
            
            $subject = $template->renderSubject($variables);
            $body = $template->renderBody($variables);
            
            // Verstuur email
            Mail::html($body, function ($message) use ($referringCustomer, $subject) {
                $message->to($referringCustomer->email, $referringCustomer->voornaam . ' ' . $referringCustomer->naam)
                        ->subject($subject);
            });
            
            // Log de email (gebruikt bestaand systeem)
            try {
                EmailLog::create([
                    'recipient_email' => $referringCustomer->email,
                    'recipient_name' => $referringCustomer->voornaam . ' ' . $referringCustomer->naam,
                    'subject' => $subject,
                    'body_html' => $body,
                    'email_template_id' => $template->id,
                    'trigger_name' => 'referral_thank_you',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'variables' => $variables
                ]);
                \Log::info('✅ Referral thank you email logged successfully');
            } catch (\Exception $logError) {
                \Log::warning('Failed to log referral email (but email was sent): ' . $logError->getMessage());
                
                // Fallback: direct DB insert (bestaand systeem)
                try {
                    \DB::table('email_logs')->insert([
                        'recipient_email' => $referringCustomer->email,
                        'recipient_name' => $referringCustomer->voornaam . ' ' . $referringCustomer->naam,
                        'subject' => $subject,
                        'body_html' => $body ?? '',
                        'email_template_id' => $template->id,
                        'trigger_name' => 'referral_thank_you',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    \Log::info('✅ Referral email logged via direct DB insert');
                } catch (\Exception $dbError) {
                    \Log::error('❌ All referral email logging failed: ' . $dbError->getMessage());
                }
            }
            
            // Update trigger statistics (gebruikt bestaand systeem)
            $this->updateTriggerStats('referral_thank_you', true);
            
            \Log::info('✅ Referral thank you email sent successfully');
            return true;
            
        } catch (\Exception $e) {
            \Log::error('❌ Failed to send referral thank you email: ' . $e->getMessage(), [
                'referring_customer' => $referringCustomer->email ?? 'unknown',
                'referred_customer' => $referredCustomer->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update trigger statistics met failure
            $this->updateTriggerStats('referral_thank_you', false);
            
            return false;
        }
    }
}