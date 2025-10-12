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
        'telefoonnummer',
        'geboortedatum',
        'straatnaam',        // TOEGEVOEGD
        'huisnummer',        // TOEGEVOEGD
        'adres',
        'postcode',
        'stad',              // TOEGEVOEGD
        'land',
        'geslacht',          // TOEGEVOEGD
        'status',            // TOEGEVOEGD
        'sport',             // TOEGEVOEGD
        'niveau',            // TOEGEVOEGD
        'club',              // TOEGEVOEGD
        'herkomst',          // TOEGEVOEGD
        'hoe_ontdekt',
        'opmerkingen',
        'is_active',
        'opmerking_bool',
        'is_gesynchroniseerd'
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
     * Get the user associated with this klant
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper om te checken of klant een user account heeft
     */
    public function hasUserAccount()
    {
        return !is_null($this->user_id);
    }

    /**
     * Maak een user account aan voor deze klant
     */
    public function createUserAccount($password = null)
    {
        if ($this->hasUserAccount()) {
            return $this->user;
        }

        $password = $password ?: \Str::random(8);

        $user = User::create([
            'name' => $this->naam,
            'email' => $this->email,
            'password' => \Hash::make($password),
            'role' => 'klant', // Consistent gebruik van 'klant' in plaats van 'customer'
            'email_verified_at' => now()
        ]);

        $this->update(['user_id' => $user->id]);

        \Log::info("User account aangemaakt voor klant {$this->id}: {$user->email} met rol 'klant'");

        return $user;
    }

    /**
     * Sync klant data naar user account
     */
    public function syncToUserAccount()
    {
        if ($this->hasUserAccount()) {
            $this->user->update([
                'name' => $this->naam,
                'email' => $this->email,
                'role' => 'klant'
            ]);
        }
    }

    // NIEUWE REFERRAL RELATIES - VEILIG TOEGEVOEGD
    /**
     * Referrals this customer has made (customers they referred)
     */
    public function referralsMade()
    {
        return $this->hasMany(\App\Models\CustomerReferral::class, 'referring_customer_id');
    }

    /**
     * The referral record for this customer (how they were referred)
     */
    public function referralReceived()
    {
        return $this->hasOne(\App\Models\CustomerReferral::class, 'referred_customer_id');
    }

    /**
     * Get the customer who referred this customer
     */
    public function getReferredByAttribute()
    {
        return $this->referralReceived?->referringCustomer;
    }

    /**
     * Get count of successful referrals this customer made
     */
    public function getReferralCountAttribute()
    {
        return $this->referralsMade()->count();
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
