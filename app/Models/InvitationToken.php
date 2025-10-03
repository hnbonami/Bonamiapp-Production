<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InvitationToken extends Model
{
    protected $fillable = [
        'email',
        'token', 
        'type',
        'temporary_password',
        'used',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isValid()
    {
        return !$this->used && !$this->isExpired();
    }

    public static function createForKlant($email, $temporaryPassword)
    {
        return self::create([
            'email' => $email,
            'token' => \Str::random(60),
            'type' => 'klant',
            'temporary_password' => $temporaryPassword,
            'expires_at' => Carbon::now()->addDays(7)
        ]);
    }

    public static function createForMedewerker($email, $temporaryPassword)
    {
        return self::create([
            'email' => $email,
            'token' => \Str::random(60),
            'type' => 'medewerker',
            'temporary_password' => $temporaryPassword,
            'expires_at' => Carbon::now()->addDays(7)
        ]);
    }
}