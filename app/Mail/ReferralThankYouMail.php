<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralThankYouMail extends Mailable
{
    use Queueable, SerializesModels;

    public $referringCustomer;
    public $newCustomer;
    public $template;

    /**
     * Create a new message instance.
     */
    public function __construct($referringCustomer, $newCustomer, $template)
    {
        $this->referringCustomer = $referringCustomer;
        $this->newCustomer = $newCustomer;
        $this->template = $template;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bedankt voor je doorverwijzing! 🙏',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.referral-thank-you',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}