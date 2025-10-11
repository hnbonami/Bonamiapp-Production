<?php

namespace App\Services;

use App\Models\CustomerReferral;
use App\Models\Klant;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    protected $emailService;

    public function __construct(EmailIntegrationService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * VEILIG verwerken van nieuwe klant referral
     */
    public function processNewCustomerReferral($newCustomer, $referralData)
    {
        try {
            Log::info('🎯 PROCESSING CUSTOMER REFERRAL (SAFE)', [
                'new_customer' => $newCustomer->email,
                'referral_source' => $referralData['source'] ?? 'unknown',
                'referring_customer_id' => $referralData['referring_customer_id'] ?? null
            ]);

            // VEILIGHEIDSCHECK: Alleen als source is opgegeven
            if (empty($referralData['source'])) {
                Log::info('No referral source provided - skipping referral processing');
                return null;
            }

            // Maak referral record aan
            $referral = CustomerReferral::create([
                'referred_customer_id' => $newCustomer->id,
                'referring_customer_id' => $referralData['referring_customer_id'] ?? null,
                'referral_source' => $referralData['source'],
                'referral_notes' => $referralData['notes'] ?? null
            ]);

            Log::info('✅ Referral record created', ['referral_id' => $referral->id]);

            // ALLEEN email versturen als er een doorverwijzende klant is EN het mond-aan-mond is
            if ($referralData['source'] === 'mond_aan_mond' && $referralData['referring_customer_id']) {
                $this->sendReferralThankYouEmail($referral);
            }

            return $referral;

        } catch (\Exception $e) {
            Log::error('❌ FAILED to process customer referral (NON-CRITICAL): ' . $e->getMessage(), [
                'new_customer_id' => $newCustomer->id ?? null,
                'referral_data' => $referralData,
                'trace' => $e->getTraceAsString()
            ]);
            
            // BELANGRIJK: Return null maar crash NIET - bestaande klant aanmaak moet blijven werken
            return null;
        }
    }

    /**
     * VEILIG versturen van bedankmail
     */
    public function sendReferralThankYouEmail($referral)
    {
        try {
            if (!$referral->referringCustomer) {
                Log::warning('No referring customer found for referral', ['referral_id' => $referral->id]);
                return false;
            }

            // Check of email al verstuurd is
            if ($referral->thank_you_email_sent) {
                Log::info('Thank you email already sent', ['referral_id' => $referral->id]);
                return true;
            }

            Log::info('🎉 Sending referral thank you email', [
                'referring_customer' => $referral->referringCustomer->email,
                'referred_customer' => $referral->referredCustomer->email
            ]);

            // Gebruik BESTAANDE EmailIntegrationService
            $success = $this->emailService->sendReferralThankYouEmail(
                $referral->referringCustomer,
                $referral->referredCustomer
            );

            if ($success) {
                $referral->markThankYouEmailSent();
                Log::info('✅ Referral thank you email sent successfully');
            } else {
                Log::error('❌ Failed to send referral thank you email');
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('❌ REFERRAL EMAIL ERROR (NON-CRITICAL): ' . $e->getMessage(), [
                'referral_id' => $referral->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return false maar crash NIET
            return false;
        }
    }

    /**
     * VEILIG ophalen van beschikbare klanten voor doorverwijzing
     */
    public function getAvailableReferringCustomers($excludeCustomerId = null)
    {
        try {
            return Klant::query()
                ->select('id', 'voornaam', 'naam', 'email')
                ->when($excludeCustomerId, function ($query) use ($excludeCustomerId) {
                    $query->where('id', '!=', $excludeCustomerId);
                })
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->orderBy('voornaam')
                ->orderBy('naam')
                ->get()
                ->map(function ($klant) {
                    return [
                        'id' => $klant->id,
                        'name' => $klant->voornaam . ' ' . $klant->naam,
                        'email' => $klant->email
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Failed to get referring customers: ' . $e->getMessage());
            return collect(); // Return empty collection
        }
    }

    /**
     * Referral statistieken (voor later)
     */
    public function getReferralStats()
    {
        try {
            return [
                'total_referrals' => CustomerReferral::count(),
                'word_of_mouth_referrals' => CustomerReferral::bySource('mond_aan_mond')->count(),
                'pending_thank_you_emails' => CustomerReferral::pendingThankYou()->count(),
                'thank_you_emails_sent' => CustomerReferral::where('thank_you_email_sent', true)->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get referral stats: ' . $e->getMessage());
            return [
                'total_referrals' => 0,
                'word_of_mouth_referrals' => 0,
                'pending_thank_you_emails' => 0,
                'thank_you_emails_sent' => 0,
            ];
        }
    }
}