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

    /**
     * Get the login activities for the user.
     */
    // Add relationship to LoginActivity in User model
    public function loginActivities()
    {
        return $this->hasMany(\App\Models\LoginActivity::class);
    }

    public function lastLoginActivity()
    {
        return $this->hasOne(\App\Models\LoginActivity::class)->latest('logged_in_at');
    }

    // Helper to get login count
    public function getLoginCountAttribute()
    {
        return $this->loginActivities()->count();
    }    public function staffNotes()
    {
        return $this->belongsToMany(StaffNote::class)->withPivot('read_at')->withTimestamps();
    }

    /**
     * Relationship naar Klant model
     */
    public function klant()
    {
        return $this->hasOne(Klant::class);
    }

    /**
     * Relationship naar Medewerker model
     */
    public function medewerker()
    {
        return $this->hasOne(Medewerker::class);
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

    /**
     * Login logs relationship
     */
    public function loginLogs()
    {
        return $this->hasMany(UserLoginLog::class);
    }

    /**
     * Check if user can access specific tab
     */
    public function canAccessTab($tabName)
    {
        if (!$this->role) {
            return false;
        }

        return RolePermission::canAccessTab($this->role, $tabName);
    }

    /**
     * Check if user can perform action on test type
     */
    public function canCreateTest($testType)
    {
        if (!$this->role) {
            return false;
        }

        return RoleTestPermission::canPerformAction($this->role, $testType, 'create');
    }

    public function canEditTest($testType)
    {
        if (!$this->role) {
            return false;
        }

        return RoleTestPermission::canPerformAction($this->role, $testType, 'edit');
    }

    public function canAccessTest($testType)
    {
        if (!$this->role) {
            return false;
        }

        return RoleTestPermission::canPerformAction($this->role, $testType, 'access');
    }

    /**
     * Get all accessible tabs for user
     */
    public function getAccessibleTabs()
    {
        if (!$this->role) {
            return [];
        }

        return RolePermission::getAccessibleTabs($this->role);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($roleName)
    {
        return $this->role === $roleName;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff (admin or medewerker)
     */
    public function isStaff()
    {
        return in_array($this->role, ['admin', 'medewerker']);
    }

    /**
     * Check of user een klant is
     */
    public function isKlant()
    {
        return $this->role === 'klant';
    }

    /**
     * Check of user een medewerker is
     */
    public function isMedewerker()
    {
        return $this->role === 'medewerker';
    }

    /**
     * Get latest login log
     */
    public function getLatestLoginAttribute()
    {
        return $this->loginLogs()->latest('login_at')->first();
    }

    /**
     * Update login statistics
     */
    public function updateLoginStats()
    {
        $this->increment('login_count');
        $this->update(['last_login_at' => now()]);
    }
}
