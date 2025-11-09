<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $emailData;
    protected $template;
    protected $organisatie;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData, EmailTemplate $template, $organisatie = null)
    {
        $this->emailData = $emailData;
        $this->template = $template;
        $this->organisatie = $organisatie;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        \Log::info('🚀🚀🚀 TEMPLATE MAIL BUILD GESTART 🚀🚀🚀', [
            'constructor_organisatie' => $this->organisatie ? $this->organisatie->id : 'NULL',
            'template_id' => $this->template ? $this->template->id : 'NULL'
        ]);
        
        // STAP 1: Bepaal organisatie (meerdere fallbacks)
        $organisatie = $this->organisatie;
        
        // Fallback 1: Via auth user
        if (!$organisatie && auth()->check()) {
            $organisatie = auth()->user()->organisatie;
            \Log::warning('⚠️ Organisatie via fallback 1: auth user', [
                'organisatie_id' => $organisatie ? $organisatie->id : null,
                'organisatie_naam' => $organisatie ? $organisatie->naam : null
            ]);
        }
        
        // Fallback 2: Via template organisatie_id
        if (!$organisatie && $this->template && $this->template->organisatie_id) {
            $organisatie = \App\Models\Organisatie::find($this->template->organisatie_id);
            \Log::warning('⚠️ Organisatie via fallback 2: template organisatie_id', [
                'organisatie_id' => $organisatie ? $organisatie->id : null,
                'organisatie_naam' => $organisatie ? $organisatie->naam : null
            ]);
        }
        
        // STAP 2: Bepaal afzender naam en email
        $fromName = 'Performance Pulse'; // Default fallback
        $fromEmail = config('mail.from.address');
        
        if ($organisatie) {
            // REFRESH organisatie data (belangrijk voor up-to-date email settings!)
            $organisatie->refresh();
            
            // Haal organisatie-specifieke afzender op
            $fromName = $organisatie->email_from_name;
            
            // Als email_from_name leeg is, gebruik bedrijf_naam of naam
            if (empty($fromName)) {
                $fromName = $organisatie->bedrijf_naam ?? $organisatie->naam;
            }
            
            // Gebruik organisatie email adres indien ingesteld
            if (!empty($organisatie->email_from_address)) {
                $fromEmail = $organisatie->email_from_address;
            }
            
            \Log::info('✅ Email afzender ingesteld', [
                'from_name' => $fromName,
                'from_email' => $fromEmail,
                'organisatie' => $organisatie->naam,
                'organisatie_id' => $organisatie->id,
                'email_from_name_db' => $organisatie->email_from_name,
                'bedrijf_naam_db' => $organisatie->bedrijf_naam,
                'constructor_organisatie_meegegeven' => $this->organisatie ? 'ja' : 'nee'
            ]);
        } else {
            \Log::error('❌ GEEN ORGANISATIE GEVONDEN - Performance Pulse wordt gebruikt als afzender!', [
                'template' => $this->template ? $this->template->name : 'unknown',
                'template_id' => $this->template ? $this->template->id : null,
                'template_org_id' => $this->template ? $this->template->organisatie_id : null,
                'auth_user' => auth()->check() ? auth()->id() : 'niet ingelogd',
                'auth_user_org_id' => auth()->check() ? auth()->user()->organisatie_id : null
            ]);
        }
        
        // STAP 3: Bouw email met correcte afzender
        // BELANGRIJK: from() overschrijft de .env configuratie
        \Log::info('📧 Email bouwen met afzender', [
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'subject' => $this->emailData['subject'] ?? $this->template->subject
        ]);
        
        $mail = $this->from($fromEmail, $fromName)
                     ->subject($this->emailData['subject'] ?? $this->template->subject)
                     ->html($this->emailData['body']);
        
        \Log::info('✅✅✅ TEMPLATE MAIL BUILD VOLTOOID ✅✅✅', [
            'from_email_set' => $fromEmail,
            'from_name_set' => $fromName
        ]);
        
        return $mail;
    }
}
