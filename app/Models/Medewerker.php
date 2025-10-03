<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Medewerker extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'medewerkers';

    protected $fillable = [
        // Basis persoonlijke info
        'voornaam',
        'achternaam',
        'email',
        'telefoonnummer',
        'telefoon',
        'mobiel',
        'website',
        'straatnaam',
        'huisnummer',
        'adres',
        'postcode',
        'stad',
        'provincie',
        'land',
        'geboortedatum',
        'leeftijd',
        'geslacht',
        'bsn',
        'nationaliteit',
        
        // Adres informatie
        'straatnaam',
        'huisnummer',
        'adres',
        'postcode',
        'stad',
        'provincie',
        'land',
        
        // Werk gerelateerd
        'functie',
        'rol',
        'afdeling',
        'salaris',
        'toegangsrechten',
        'toegangsniveau',
        'status',
        'in_dienst_sinds',
        'startdatum',
        'uit_dienst',
        'contract_type',
        'uurloon',
        'uren_per_week',
        
        // Vaardigheden en ervaring
        'certificaten',
        'specialisaties',
        'opleidingen',
        'werkervaring',
        'talen',
        
        // Contact voorkeuren
        'voorkeur_contact',
        'nieuwsbrief',
        'werkgerelateerde_emails',
        
        // Noodcontact
        'noodcontact_naam',
        'noodcontact_telefoon',
        'noodcontact_relatie',
        
        // Financieel
        'iban',
        'bank_naam',
        'btw_nummer',
        'kvk_nummer',
        
        // Beschikbaarheid
        'beschikbaarheid',
        'max_klanten_per_dag',
        'weekend_beschikbaar',
        'avond_beschikbaar',
        
        // Profiel
        'bio',
        'avatar_path',
        'bikefit',
        'inspanningstest',
        'avatar_url',
        'social_media',
        
        // Notities
        'notities',
        'intern_notities',
        
        // Relaties
        'user_id',
        'aangemaakt_door',
        'laatste_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'geboortedatum' => 'date',
        'in_dienst_sinds' => 'date',
        'startdatum' => 'date',
        'uit_dienst' => 'date',
        'laatste_login' => 'datetime',
        'salaris' => 'decimal:2',
        'bikefit' => 'boolean',
        'inspanningstest' => 'boolean',
        'weekend_beschikbaar' => 'boolean',
        'avond_beschikbaar' => 'boolean',
        'nieuwsbrief' => 'boolean',
        'werkgerelateerde_emails' => 'boolean',
        'certificaten' => 'array',
        'specialisaties' => 'array',
        'talen' => 'array',
        'beschikbaarheid' => 'array',
        'social_media' => 'array',
        'deleted_at' => 'datetime',
    ];

    // Accessor voor volledige naam (read-only)
    public function getNaamAttribute()
    {
        return $this->voornaam . ' ' . $this->achternaam;
    }
}


