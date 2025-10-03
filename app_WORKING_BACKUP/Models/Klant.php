<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Klant extends Model
{
    use HasFactory;

    protected $table = 'klanten';

    protected $fillable = [
        'voornaam',
        'naam', 
        'email',
        'website',
        'telefoonnummer',
        'telefoon',
        'voorkeur_contact',
        'nieuwsbrief',
        'marketing_emails',
        'mobiel',
        'straatnaam',
        'huisnummer', 
        'adres',
        'postcode',
        'stad',
        'provincie',
        'land',
        'btw_nummer',
        'factuuradres_anders',
        'factuur_straat',
        'factuur_huisnummer',
        'factuur_postcode', 
        'factuur_stad',
        'noodcontact_naam',
        'noodcontact_telefoon',
        'noodcontact_relatie',
        'geboortedatum',
        'leeftijd',
        'geslacht',
        'lengte',
        'gewicht',
        'beroep',
        'sport',
        'discipline',
        'niveau',
        'ervaring_jaren',
        'trainingsuren_per_week',
        'competitief',
        'club',
        'herkomst',
        'referentie',
        'status',
        'actief',
        'laatste_afspraak',
        'avatar_path',
        'avatar_url',
        'social_media',
        'medische_geschiedenis',
        'allergieën',
        'medicijnen',
        'blessures',
        'huisarts',
        'fysiotherapeut',
        'doelen',
        'notities',
        'bio',
        'avatar',
        'klant_sinds',
        'eerste_afspraak',
        'last_login',
    ];

    public function inspanningstests()
    {
        return $this->hasMany(Inspanningstest::class);
    }

    public function bikefits()
    {
        return $this->hasMany(Bikefit::class);
    }

    // TODO: bikefit relatie toevoegen als het model en tabel bestaat
}
