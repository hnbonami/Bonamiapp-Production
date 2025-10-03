<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $password;
    public $loginUrl;

    public function __construct($name, $email, $password, $loginUrl)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->loginUrl = $loginUrl;
    }

    public function build()
    {
        return $this->subject('Je account bij Bonami Sportcoaching')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'loginUrl' => $this->loginUrl,
            ])
            ->view('emails.account_created');
    }
}
