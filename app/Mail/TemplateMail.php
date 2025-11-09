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
        // Gebruik organisatie-specifieke afzender naam (overschrijft .env config!)
        $fromName = null;
        $fromEmail = null;
        
        if ($this->organisatie) {
            $fromName = $this->organisatie->email_from_name 
                        ?? $this->organisatie->bedrijf_naam 
                        ?? $this->organisatie->naam;
            
            $fromEmail = $this->organisatie->email_from_address 
                         ?? null;
        }
        
        // Fallback naar .env configuratie als organisatie geen custom waarden heeft
        if (!$fromName) {
            $fromName = config('mail.from.name', 'Performance Pulse');
        }
        
        if (!$fromEmail) {
            $fromEmail = config('mail.from.address');
        }
        
        \Log::info('📧 Email wordt verzonden met afzender', [
            'from_name' => $fromName,
            'from_email' => $fromEmail,
            'template' => $this->template->name,
            'organisatie' => $this->organisatie->naam ?? 'geen organisatie'
        ]);
        
        // BELANGRIJK: from() overschrijft de .env configuratie
        return $this->from($fromEmail, $fromName)
                    ->subject($this->emailData['subject'] ?? $this->template->subject)
                    ->html($this->emailData['body']);
    }
}
