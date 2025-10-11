<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referred_customer_id',
        'referring_customer_id',
        'referral_source',
        'referral_notes',
        'thank_you_email_sent',
        'thank_you_email_sent_at'
    ];

    protected $casts = [
        'thank_you_email_sent' => 'boolean',
        'thank_you_email_sent_at' => 'datetime'
    ];

    /**
     * De klant die werd doorverwezen (nieuwe klant)
     */
    public function referredCustomer()
    {
        return $this->belongsTo(Klant::class, 'referred_customer_id');
    }

    /**
     * De klant die de doorverwijzing deed
     */
    public function referringCustomer()
    {
        return $this->belongsTo(Klant::class, 'referring_customer_id');
    }

    /**
     * Beschikbare doorverwijzingsbronnen
     */
    public static function getReferralSources()
    {
        return [
            'via_internet' => 'Via internet/Google',
            'mond_aan_mond' => 'Mond aan mond',
            'sociale_media' => 'Sociale media',
            'bestaande_klant' => 'Bestaande klant',
            'andere' => 'Andere'
        ];
    }

    /**
     * Scope voor verschillende doorverwijzingsbronnen
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('referral_source', $source);
    }

    /**
     * Scope voor doorverwijzingen die nog geen bedankmail hebben gehad
     */
    public function scopePendingThankYou($query)
    {
        return $query->where('thank_you_email_sent', false)
                    ->whereNotNull('referring_customer_id');
    }

    /**
     * Markeer bedankmail als verstuurd
     */
    public function markThankYouEmailSent()
    {
        $this->update([
            'thank_you_email_sent' => true,
            'thank_you_email_sent_at' => now()
        ]);
    }
}