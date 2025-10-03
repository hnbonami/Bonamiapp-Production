<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    /**
     * Send invitation email to customer
     */
    public static function sendCustomerInvitation($klant, $temporaryPassword, $invitationToken)
    {
        try {
            // Find the customer invitation template using correct field names
            $template = \App\Models\EmailTemplate::where('name', 'Klant Uitnodiging')
                                                 ->orWhere('name', 'Customer Invitation')
                                                 ->orWhere('type', 'customer_invitation')
                                                 ->where('is_active', 1)
                                                 ->first();
            
            if (!$template) {
                // Create the missing customer invitation template
                $template = \App\Models\EmailTemplate::create([
                    'name' => 'Klant Uitnodiging',
                    'type' => 'customer_invitation',
                    'subject' => 'Welkom bij @{{bedrijf_naam}} - Je account is klaar!',
                    'body_html' => '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0; border-radius: 10px; overflow: hidden;">
                        <div style="text-align: center; padding: 40px 20px; color: white;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">@{{bedrijf_naam}}</h1>
                            <h2 style="margin: 20px 0 0 0; font-size: 22px; font-weight: normal;">Welkom @{{voornaam}}! 👋</h2>
                        </div>
                        
                        <div style="background: white; padding: 40px 30px; color: #333;">
                            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                Beste @{{voornaam}} @{{naam}},
                            </p>
                            
                            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                Je bent uitgenodigd om toegang te krijgen tot onze klanten portal waar je je afspraken kunt beheren en je testresultaten kunt bekijken.
                            </p>
                            
                            <div style="background: #e8f4f8; padding: 25px; border-radius: 8px; margin: 30px 0; border-left: 4px solid #3498db;">
                                <h3 style="color: #2c3e50; margin: 0 0 15px 0; font-size: 18px;">🔐 Je logingegevens:</h3>
                                <p style="margin: 5px 0; color: #34495e;"><strong>Email:</strong> @{{email}}</p>
                                <p style="margin: 5px 0; color: #34495e;"><strong>Tijdelijk wachtwoord:</strong> <code style="background: #f1f2f6; padding: 4px 8px; border-radius: 4px; font-family: monospace;">@{{password}}</code></p>
                            </div>
                            
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="@{{login_url}}" style="background: #c8e1eb; color: #111; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 4px 15px rgba(200, 225, 235, 0.4);">
                                    🚀 Inloggen op het portaal
                                </a>
                            </div>
                            
                            <p style="font-size: 14px; color: #7f8c8d; text-align: center; margin: 30px 0 10px 0;">
                                Je kunt ook handmatig inloggen op: <a href="@{{website_url}}/login" style="color: #3498db;">@{{website_url}}/login</a>
                            </p>
                            
                            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 15px; margin: 30px 0;">
                                <p style="margin: 0; color: #856404; font-size: 14px;">
                                    <strong>⚠️ Belangrijk:</strong> Wijzig je wachtwoord na de eerste login voor de veiligheid.
                                </p>
                            </div>
                            
                            <div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 1px solid #ecf0f1;">
                                <p style="color: #7f8c8d; margin: 0; font-size: 16px;">
                                    Met vriendelijke groet,<br>
                                    <strong style="color: #2c3e50;">Het @{{bedrijf_naam}} Team</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'description' => 'Template voor uitnodiging nieuwe klanten',
                    'is_active' => true,
                ]);
                
                \Log::info('Created missing customer invitation template');
            }
            
            $loginUrl = url('/login?token=' . $invitationToken->token);
            
            // Use the correct field names from your template system
            $subject = $template->subject;
            $message = $template->body_html;
            
            // Replace placeholders with actual values
            $replacements = [
                '@{{voornaam}}' => $klant->voornaam,
                '@{{naam}}' => $klant->naam,
                '@{{bedrijf_naam}}' => config('app.name', 'Bonami Sportcoaching'),
                '@{{email}}' => $klant->email,
                '@{{password}}' => $temporaryPassword,
                '@{{login_url}}' => $loginUrl,
                '@{{website_url}}' => url('/'),
                '@{{jaar}}' => date('Y'),
            ];
            
            foreach ($replacements as $placeholder => $value) {
                $subject = str_replace($placeholder, $value, $subject);
                $message = str_replace($placeholder, $value, $message);
            }
            
            // Send email using Laravel's Mail facade
            try {
                \Mail::send([], [], function ($mail) use ($klant, $subject, $message) {
                    $mail->to($klant->email, $klant->voornaam . ' ' . $klant->naam)
                         ->subject($subject)
                         ->html($message);
                });
                
                \Log::info('Customer invitation email sent successfully using template: ' . $template->name . ' to: ' . $klant->email);
                return true;
                
            } catch (\Exception $e) {
                \Log::error('Failed to send customer invitation email: ' . $e->getMessage());
                return false;
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to send customer invitation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send invitation email to medewerker
     */
    public static function sendMedewerkerInvitation($medewerker, $temporaryPassword, $invitationToken)
    {
        try {
            // Find the employee invitation template using correct field names
            $template = \App\Models\EmailTemplate::where('name', 'Medewerker Uitnodiging')
                                                 ->orWhere('type', 'employee_invitation')
                                                 ->where('is_active', 1)
                                                 ->first();
            
            if (!$template) {
                \Log::warning('No employee invitation template found, using fallback');
                return self::sendMedewerkerInvitationFallback($medewerker, $temporaryPassword, $invitationToken);
            }
            
            $loginUrl = url('/login?token=' . $invitationToken->token);
            
            // Use the correct field names from your template system
            $subject = $template->subject;
            $message = $template->body_html;
            
            // Replace placeholders with actual values
            $replacements = [
                '@{{voornaam}}' => $medewerker->voornaam,
                '@{{naam}}' => $medewerker->achternaam,
                '@{{bedrijf_naam}}' => config('app.name', 'Bonami Sportcoaching'),
                '@{{email}}' => $medewerker->email,
                '@{{password}}' => $temporaryPassword,
                '@{{login_url}}' => $loginUrl,
                '@{{website_url}}' => url('/'),
                '@{{jaar}}' => date('Y'),
            ];
            
            foreach ($replacements as $placeholder => $value) {
                $subject = str_replace($placeholder, $value, $subject);
                $message = str_replace($placeholder, $value, $message);
            }
            
            // Send email using Laravel's Mail facade
            try {
                \Mail::send([], [], function ($mail) use ($medewerker, $subject, $message) {
                    $mail->to($medewerker->email, $medewerker->voornaam . ' ' . $medewerker->achternaam)
                         ->subject($subject)
                         ->html($message);
                });
                
                \Log::info('Employee invitation email sent successfully using template: ' . $template->name . ' to: ' . $medewerker->email);
                return true;
                
            } catch (\Exception $e) {
                \Log::error('Failed to send employee invitation email: ' . $e->getMessage());
                return false;
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to send employee invitation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fallback method for customer invitation when no template is found
     */
    public static function sendCustomerInvitationFallback($klant, $temporaryPassword, $invitationToken)
    {
        $loginUrl = url('/login?token=' . $invitationToken->token);
        
        $subject = 'Uitnodiging voor Bonami Sportcoaching Portal';
        $message = "
        <h2>Welkom bij Bonami Sportcoaching</h2>
        <p>Beste {$klant->voornaam} {$klant->naam},</p>
        
        <p>Je bent uitgenodigd om toegang te krijgen tot onze klanten portal waar je je afspraken kunt beheren en je testresultaten kunt bekijken.</p>
        
        <p><strong>Je inloggegevens:</strong></p>
        <ul>
            <li>Email: {$klant->email}</li>
            <li>Tijdelijk wachtwoord: {$temporaryPassword}</li>
        </ul>
        
        <p><a href='{$loginUrl}' style='background: #c8e1eb; color: #111; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Inloggen</a></p>
        
        <p>Je kunt ook handmatig inloggen op: " . url('/login') . "</p>
        
        <p><strong>Belangrijk:</strong> Wijzig je wachtwoord na de eerste login voor de veiligheid.</p>
        
        <p>Met vriendelijke groet,<br>
        Het Bonami Sportcoaching Team</p>
        ";
        
        try {
            \Mail::send([], [], function ($mail) use ($klant, $subject, $message) {
                $mail->to($klant->email, $klant->voornaam . ' ' . $klant->naam)
                     ->subject($subject)
                     ->html($message);
            });
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Fallback customer invitation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fallback method for employee invitation when no template is found
     */
    public static function sendMedewerkerInvitationFallback($medewerker, $temporaryPassword, $invitationToken)
    {
        $loginUrl = url('/login?token=' . $invitationToken->token);
        
        $subject = 'Uitnodiging voor Bonami Sportcoaching Portal';
        $message = "
        <h2>Welkom bij Bonami Sportcoaching</h2>
        <p>Beste {$medewerker->voornaam} {$medewerker->achternaam},</p>
        
        <p>Je bent uitgenodigd om toegang te krijgen tot de Bonami Sportcoaching medewerker portal.</p>
        
        <p><strong>Je inloggegevens:</strong></p>
        <ul>
            <li>Email: {$medewerker->email}</li>
            <li>Tijdelijk wachtwoord: {$temporaryPassword}</li>
        </ul>
        
        <p><a href='{$loginUrl}' style='background: #c8e1eb; color: #111; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Inloggen</a></p>
        
        <p>Je kunt ook handmatig inloggen op: " . url('/login') . "</p>
        
        <p><strong>Belangrijk:</strong> Wijzig je wachtwoord na de eerste login voor de veiligheid.</p>
        
        <p>Met vriendelijke groet,<br>
        Het Bonami Sportcoaching Team</p>
        ";
        
        try {
            \Mail::send([], [], function ($mail) use ($medewerker, $subject, $message) {
                $mail->to($medewerker->email, $medewerker->voornaam . ' ' . $medewerker->achternaam)
                     ->subject($subject)
                     ->html($message);
            });
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Fallback employee invitation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create default email templates if they don't exist
     */
    public static function createDefaultTemplates()
    {
        try {
            // Customer invitation template
            \App\Models\EmailTemplate::updateOrCreate(
                ['naam' => 'Klant Uitnodiging'],
                [
                    'type' => 'customer_invitation',
                    'onderwerp' => 'Welkom bij {{bedrijf_naam}} - Je account is klaar!',
                    'inhoud' => '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0; border-radius: 10px; overflow: hidden;">
                        <div style="text-align: center; padding: 40px 20px; color: white;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">{{bedrijf_naam}}</h1>
                            <h2 style="margin: 20px 0 0 0; font-size: 22px; font-weight: normal;">Welkom {{voornaam}}! 👋</h2>
                        </div>
                        
                        <div style="background: white; padding: 40px 30px; color: #333;">
                            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                Beste {{voornaam}} {{naam}},
                            </p>
                            
                            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                Je bent uitgenodigd om toegang te krijgen tot onze klanten portal waar je je afspraken kunt beheren en je testresultaten kunt bekijken.
                            </p>
                            
                            <div style="background: #e8f4f8; padding: 25px; border-radius: 8px; margin: 30px 0; border-left: 4px solid #3498db;">
                                <h3 style="color: #2c3e50; margin: 0 0 15px 0; font-size: 18px;">🔐 Je logingegevens:</h3>
                                <p style="margin: 5px 0; color: #34495e;"><strong>Email:</strong> {{email}}</p>
                                <p style="margin: 5px 0; color: #34495e;"><strong>Tijdelijk wachtwoord:</strong> <code style="background: #f1f2f6; padding: 4px 8px; border-radius: 4px; font-family: monospace;">{{password}}</code></p>
                            </div>
                            
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{login_url}}" style="background: #c8e1eb; color: #111; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 4px 15px rgba(200, 225, 235, 0.4);">
                                    🚀 Inloggen op het portaal
                                </a>
                            </div>
                            
                            <p style="font-size: 14px; color: #7f8c8d; text-align: center; margin: 30px 0 10px 0;">
                                Je kunt ook handmatig inloggen op: <a href="{{website_url}}/login" style="color: #3498db;">{{website_url}}/login</a>
                            </p>
                            
                            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 15px; margin: 30px 0;">
                                <p style="margin: 0; color: #856404; font-size: 14px;">
                                    <strong>⚠️ Belangrijk:</strong> Wijzig je wachtwoord na de eerste login voor de veiligheid.
                                </p>
                            </div>
                            
                            <div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 1px solid #ecf0f1;">
                                <p style="color: #7f8c8d; margin: 0; font-size: 16px;">
                                    Met vriendelijke groet,<br>
                                    <strong style="color: #2c3e50;">Het {{bedrijf_naam}} Team</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'actief' => true,
                ]
            );

            // Employee invitation template
            \App\Models\EmailTemplate::updateOrCreate(
                ['naam' => 'Medewerker Uitnodiging'],
                [
                    'type' => 'employee_invitation',
                    'onderwerp' => 'Welkom bij {{bedrijf_naam}} - Medewerker toegang',
                    'inhoud' => '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0; border-radius: 10px; overflow: hidden;">
                        <div style="text-align: center; padding: 40px 20px; color: white;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">{{bedrijf_naam}}</h1>
                            <h2 style="margin: 20px 0 0 0; font-size: 22px; font-weight: normal;">Welkom medewerker {{voornaam}}! 👋</h2>
                        </div>
                        
                        <div style="background: white; padding: 40px 30px; color: #333;">
                            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                                Beste {{voornaam}} {{naam}},
                            </p>
                            
                            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                Je bent uitgenodigd om toegang te krijgen tot de {{bedrijf_naam}} medewerker portal.
                                Hier kun je klanten beheren, afspraken inplannen en testresultaten bijhouden.
                            </p>
                            
                            <div style="background: #e8f4f8; padding: 25px; border-radius: 8px; margin: 30px 0; border-left: 4px solid #3498db;">
                                <h3 style="color: #2c3e50; margin: 0 0 15px 0; font-size: 18px;">🔐 Je logingegevens:</h3>
                                <p style="margin: 5px 0; color: #34495e;"><strong>Email:</strong> {{email}}</p>
                                <p style="margin: 5px 0; color: #34495e;"><strong>Tijdelijk wachtwoord:</strong> <code style="background: #f1f2f6; padding: 4px 8px; border-radius: 4px; font-family: monospace;">{{password}}</code></p>
                            </div>
                            
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{login_url}}" style="background: #c8e1eb; color: #111; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 4px 15px rgba(200, 225, 235, 0.4);">
                                    🚀 Inloggen op het portaal
                                </a>
                            </div>
                            
                            <p style="font-size: 14px; color: #7f8c8d; text-align: center; margin: 30px 0 10px 0;">
                                Je kunt ook handmatig inloggen op: <a href="{{website_url}}/login" style="color: #3498db;">{{website_url}}/login</a>
                            </p>
                            
                            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 15px; margin: 30px 0;">
                                <p style="margin: 0; color: #856404; font-size: 14px;">
                                    <strong>⚠️ Belangrijk:</strong> Wijzig je wachtwoord na de eerste login voor de veiligheid.
                                </p>
                            </div>
                            
                            <div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 1px solid #ecf0f1;">
                                <p style="color: #7f8c8d; margin: 0; font-size: 16px;">
                                    Met vriendelijke groet,<br>
                                    <strong style="color: #2c3e50;">Het {{bedrijf_naam}} Team</strong>
                                </p>
                            </div>
                        </div>
                    </div>',
                    'actief' => true,
                ]
            );
            
            \Log::info('Default email templates created successfully');
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Failed to create default email templates: ' . $e->getMessage());
            return false;
        }
    }
}