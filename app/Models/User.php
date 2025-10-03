<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'voornaam',
        'naam',
        'name',
        'email',
        'password',
        'telefoonnummer',
        'geboortedatum', 
        'adres',
        'stad',
        'postcode',
        'geslacht',
        'avatar_path',
        'role',
        'klant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'geboortedatum' => 'date',
        ];
    }

    public function staffNotes()
    {
        return $this->belongsToMany(StaffNote::class)->withPivot('read_at')->withTimestamps();
    }

    /**
     * Get the klant associated with the user (if klant_id column exists)
     */
    public function klant()
    {
        try {
            return $this->belongsTo(\App\Models\Klant::class, 'klant_id');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get avatar URL - sync with klant avatar if customer
     */
    public function getAvatarUrlAttribute()
    {
        // First try user's own avatar
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        
        // If user is a customer, try to find klant by email
        if ($this->role === 'customer') {
            try {
                $klant = \App\Models\Klant::where('email', $this->email)->first();
                if ($klant && $klant->avatar_path) {
                    return asset('storage/' . $klant->avatar_path);
                }
            } catch (\Exception $e) {
                // Continue if klant lookup fails
            }
        }
        
        return null;
    }

    /**
     * Sync avatar between user and klant (if relation exists)
     */
    public function syncAvatarWithKlant($avatarPath)
    {
        // Update user avatar
        $this->update(['avatar_path' => $avatarPath]);
        
        // Try to sync with klant by email
        if ($this->role === 'customer') {
            try {
                $klant = \App\Models\Klant::where('email', $this->email)->first();
                if ($klant) {
                    $klant->update(['avatar_path' => $avatarPath]);
                }
            } catch (\Exception $e) {
                // If klant sync fails, just continue - user avatar is still updated
                \Log::info('Avatar sync with klant failed: ' . $e->getMessage());
            }
        }
    }
}
