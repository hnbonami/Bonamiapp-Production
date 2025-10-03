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

    /**
     * Get the user account associated with this klant
     */
    public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'klant_id');
    }

    /**
     * Get avatar URL with fallback
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        return null;
    }

    /**
     * Sync avatar between klant and user (only if klant_id column exists)
     */
    public function syncAvatarWithUser($avatarPath)
    {
        // Update klant avatar
        $this->update(['avatar_path' => $avatarPath]);
        
        // Try to find user by email instead of klant_id relation
        try {
            $user = \App\Models\User::where('email', $this->email)->first();
            if ($user) {
                $user->update(['avatar_path' => $avatarPath]);
            }
        } catch (\Exception $e) {
            // If user sync fails, just continue - klant avatar is still updated
            \Log::info('Avatar sync with user failed (klant_id column may not exist): ' . $e->getMessage());
        }
    }
}
