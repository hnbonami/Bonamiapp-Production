<?php

namespace App\Mail;

use App\Models\Organisatie;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrganisatieUitnodiging extends Mailable
{
    use Queueable, SerializesModels;

    public $organisatie;
    public $password;
    public $loginUrl;

    public function __construct(Organisatie $organisatie, string $password, string $loginUrl)
    {
        $this->organisatie = $organisatie;
        $this->password = $password;
        $this->loginUrl = $loginUrl;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('🎉 Welkom bij Bonami Sportcoaching Platform')
            ->view('emails.organisatie-uitnodiging')
            ->with([
                'organisatie' => $this->organisatie,
                'password' => $this->password,
                'loginUrl' => $this->loginUrl,
            ]);
    }
}
