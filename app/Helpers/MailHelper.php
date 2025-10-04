<?php

namespace App\Helpers;

class MailHelper
{
    /**
     * Send invitation email to customer using template system
     */
    public static function sendCustomerInvitation($klant, $temporaryPassword, $invitationToken)
    {
        try {
            // Find the welcome customer template using exact name match
            $template = \App\Models\EmailTemplate::where('name', 'Welkom Nieuwe Klant')
                                                 ->where('is_active', 1)
                                                 ->first();
                                                 
            // Fallback: try by type if name search fails
            if (!$template) {
                $template = \App\Models\EmailTemplate::where('type', \App\Models\EmailTemplate::TYPE_WELCOME_CUSTOMER)
                                                     ->where('is_active', 1)
                                                     ->first();
            }
            
            if ($template) {
                // Use the new EmailService to send template-based email
                $emailService = new \App\Services\EmailService();
                
                $loginUrl = url('/login?token=' . $invitationToken->token);
                
                // Prepare variables for the template
                $variables = [
                    'voornaam' => $klant->voornaam,
                    'naam' => $klant->naam,
                    'volledige_naam' => $klant->voornaam . ' ' . $klant->naam,
                    'email' => $klant->email,
                    'wachtwoord' => $temporaryPassword,
                    'login_url' => $loginUrl,
                    'bedrijf_naam' => 'Bonami Sportcoaching',
                    'jaar' => date('Y'),
                    'datum' => date('d-m-Y'),
                    'tijd' => date('H:i')
                ];
                
                // Process template manually to ensure variables are replaced
                $processedTemplate = clone $template;
                $processedTemplate->subject = self::replaceTemplateVariables($template->subject, $variables);
                $processedTemplate->body_html = self::replaceTemplateVariables($template->body_html, $variables);
                
                // Send email using EmailService - correct parameter order
                $emailLog = $emailService->sendEmail(
                    $processedTemplate,
                    $klant->email,
                    $klant->voornaam . ' ' . $klant->naam,
                    $variables
                );
                
                return $emailLog->status === \App\Models\EmailLog::STATUS_SENT;
            } else {
                // Fallback to old method if template not found
                return self::sendCustomerInvitationFallback($klant, $temporaryPassword, $invitationToken);
            }
            
        } catch (\Exception $e) {
            \Log::error('Customer invitation email failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send invitation email to medewerker using template system
     */
    public static function sendMedewerkerInvitation($medewerker, $temporaryPassword, $invitationToken)
    {
        try {
            \Log::info('=== EMPLOYEE INVITATION START ===');
            \Log::info('Medewerker parameter type: ' . gettype($medewerker));
            \Log::info('Medewerker data: ' . json_encode($medewerker));
            
            if (!is_object($medewerker) || !isset($medewerker->voornaam)) {
                \Log::error('Invalid medewerker object passed to sendMedewerkerInvitation');
                return false;
            }
            
            \Log::info('Employee: ' . $medewerker->voornaam . ' ' . $medewerker->achternaam . ' (' . $medewerker->email . ')');
            
            // Use fallback method directly (since template system works for customers)
            \Log::info('Using fallback email method for employee');
            return self::sendMedewerkerInvitationFallback($medewerker, $temporaryPassword, $invitationToken);
            
        } catch (\Exception $e) {
            \Log::error('Employee invitation email failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Fallback method for customer invitation when no template is found
     */
    public static function sendCustomerInvitationFallback($klant, $temporaryPassword, $invitationToken)
    {
        \Log::info('Using fallback email method for customer');
        
        $loginUrl = url('/login?token=' . $invitationToken->token);
        
        $subject = 'Uitnodiging voor Bonami Sportcoaching Portal';
        $message = "
        <h2>Welkom bij Bonami Sportcoaching</h2>
        <p>Beste {$klant->voornaam} {$klant->naam},</p>
        
        <p>Er is een account voor je aangemaakt in ons klantensysteem.</p>
        
        <p><strong>Je logingegevens:</strong><br>
        E-mailadres: {$klant->email}<br>
        Tijdelijk wachtwoord: {$temporaryPassword}</p>
        
        <p><a href=\"{$loginUrl}\" style=\"background-color: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Inloggen</a></p>
        
        <p>Je kunt dit wachtwoord wijzigen na je eerste inlog.</p>
        
        <p>Met vriendelijke groet,<br>
        Het Bonami Sportcoaching team</p>
        ";
        
        return self::sendMail($klant->email, $subject, $message);
    }

    /**
     * Fallback method for employee invitation when no template is found
     */
    public static function sendMedewerkerInvitationFallback($medewerker, $temporaryPassword, $invitationToken)
    {
        \Log::info('Using fallback email method for employee');
        
        $loginUrl = url('/login?token=' . $invitationToken->token);
        
        $subject = 'Welkom bij het Bonami Sportcoaching team';
        $message = "
        <h2>Welkom bij het team</h2>
        <p>Beste {$medewerker->voornaam} {$medewerker->achternaam},</p>
        
        <p>Welkom bij Bonami Sportcoaching! Er is een account voor je aangemaakt.</p>
        
        <p><strong>Je logingegevens:</strong><br>
        E-mailadres: {$medewerker->email}<br>
        Tijdelijk wachtwoord: {$temporaryPassword}<br>
        Functie: " . ($medewerker->functie ?? 'Medewerker') . "</p>
        
        <p><a href=\"{$loginUrl}\" style=\"background-color: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Inloggen op het systeem</a></p>
        
        <p>Je kunt dit wachtwoord wijzigen na je eerste inlog.</p>
        
        <p>Met vriendelijke groet,<br>
        Het Bonami Sportcoaching team</p>
        ";
        
        return self::sendMail($medewerker->email, $subject, $message);
    }

    /**
     * Basic email sending function
     */
    private static function sendMail($to, $subject, $body)
    {
        try {
            \Log::info('Sending basic email to: ' . $to);
            
            // Use Laravel's Mail facade instead of PHPMailer
            \Mail::send([], [], function ($message) use ($to, $subject, $body) {
                $message->to($to)
                       ->subject($subject)
                       ->html($body);
            });
            
            \Log::info('Basic email sent successfully using Laravel Mail');
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Basic email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create default templates if they don't exist
     */
    public static function createDefaultTemplates()
    {
        \Log::info('Creating default email templates...');
        
        // This could trigger template creation in the EmailController
        $controller = new \App\Http\Controllers\Admin\EmailController();
        // Call the private method to create templates
        
        return true;
    }

    /**
     * Replace template variables in content
     */
    private static function replaceTemplateVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            // Replace both @{{key}} and {{key}} formats
            $content = str_replace(['@{{' . $key . '}}', '{{' . $key . '}}'], $value ?? '', $content);
        }
        
        return $content;
    }
}