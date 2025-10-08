<?php

namespace App\Helpers;

use App\Models\EmailTemplate;
use App\Services\EmailIntegrationService;
use Illuminate\Support\Facades\Log;

class MailHelper
{
    /**
     * Send customer invitation using EmailTemplate module
     */
    public static function sendCustomerInvitation($klant, $temporaryPassword, $invitationToken)
    {
        try {
            // Look for invitation template in EmailTemplate table
            $template = EmailTemplate::where('type', 'welcome_customer')
                ->where('is_active', true)
                ->first();
            
            if (!$template) {
                Log::error('Customer invitation template not found in EmailTemplate module');
                return false;
            }
            
            // Use EmailIntegrationService
            $emailService = new EmailIntegrationService();
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
            Log::error('Failed to send customer invitation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send employee invitation using EmailTemplate module
     */
    public static function sendMedewerkerInvitation($medewerker, $temporaryPassword, $invitationToken)
    {
        try {
            // Look for employee invitation template in EmailTemplate table
            $template = EmailTemplate::where('type', 'welcome_employee')
                ->where('is_active', true)
                ->first();
            
            if (!$template) {
                Log::error('Employee invitation template not found in EmailTemplate module');
                return false;
            }
            
            // Use EmailIntegrationService
            $emailService = new EmailIntegrationService();
            $variables = [
                'voornaam' => $medewerker->voornaam,
                'naam' => $medewerker->achternaam,
                'email' => $medewerker->email,
                'temporary_password' => $temporaryPassword,
                'functie' => $medewerker->functie ?? 'Medewerker',
                'bedrijf_naam' => 'Bonami Sportcoaching',
                'datum' => now()->format('d/m/Y'),
                'jaar' => now()->format('Y'),
            ];
            
            return $emailService->sendEmployeeWelcomeEmail($medewerker, $variables);
            
        } catch (\Exception $e) {
            Log::error('Failed to send employee invitation: ' . $e->getMessage());
            return false;
        }
    }
}