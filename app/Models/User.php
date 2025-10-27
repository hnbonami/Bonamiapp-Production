<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Organisatie;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'voornaam',
        'achternaam',
        'email',
        'password',
        'role',
        'organisatie_id',
        'telefoonnummer',
        'geboortedatum',
        'geslacht',  // Zorg dat dit hier staat
        'straatnaam',
        'huisnummer',
        'postcode',
        'stad',
        'functie',
        'startdatum',
        'contract_type',
        'status',
        'bikefit',
        'inspanningstest',
        'upload_documenten',
        'notities',
        'avatar_path',
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
        return $this->hasOne(\App\Models\Klant::class, 'email', 'email');
    }

    /**
     * Relationship naar Medewerker model
     */
    public function medewerker()
    {
        return $this->hasOne(Medewerker::class);
    }

    /**
     * Relatie: user behoort tot een organisatie
     */
    public function organisatie(): BelongsTo
    {
        return $this->belongsTo(Organisatie::class, 'organisatie_id');
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
     * Check of user een superadmin is
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check of user een organisatie admin is (backwards compatible met 'admin')
     */
    public function isOrganisatieAdmin(): bool
    {
        return in_array($this->role, ['organisatie_admin', 'admin']);
    }

    /**
     * Check of user beheerder rechten heeft (superadmin, org admin, of oude admin)
     */
    public function isBeheerder(): bool
    {
        return in_array($this->role, ['superadmin', 'organisatie_admin', 'admin']);
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

    /**
     * Check of de gebruiker toegang heeft tot een specifieke feature
     * 
     * @param string $featureKey De key van de feature (bijv. 'klantenbeheer', 'bikefits')
     * @return bool
     */
    public function hasFeatureAccess(string $featureKey): bool
    {
        // Superadmin heeft altijd toegang tot alles
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Gebruiker zonder organisatie heeft geen toegang
        if (!$this->organisatie_id) {
            return false;
        }

        // Check of organisatie deze feature heeft
        return $this->organisatie->hasFeature($featureKey);
    }

    /**
     * Check of gebruiker toegang heeft tot multiple features (OR logica)
     * 
     * @param array $featureKeys Array van feature keys
     * @return bool True als gebruiker toegang heeft tot minimaal 1 van de features
     */
    public function hasAnyFeatureAccess(array $featureKeys): bool
    {
        foreach ($featureKeys as $key) {
            if ($this->hasFeatureAccess($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Diensten die deze coach kan uitvoeren (voor prestaties systeem)
     */
    public function diensten()
    {
        return $this->belongsToMany(\App\Models\Dienst::class, 'coach_diensten', 'user_id', 'dienst_id')
                    ->withPivot('custom_prijs', 'commissie_percentage', 'is_actief')
                    ->wherePivot('is_actief', true) // Specificeer pivot tabel voor is_actief
                    ->withTimestamps();
    }

    /**
     * Prestaties van deze coach
     */
    public function prestaties()
    {
        return $this->hasMany(\App\Models\Prestatie::class);
    }

    /**
     * Relatie met commissie factoren
     */
    public function commissieFactoren()
    {
        return $this->hasMany(MedewerkerCommissieFactor::class);
    }

    /**
     * Bereken commissie percentage voor een specifieke dienst
     * Rekening houdend met diploma, ervaring en anciënniteit bonussen
     * 
     * @param Dienst $dienst De dienst waarvoor commissie berekend moet worden
     * @return float Het finale commissie percentage
     */
    public function getCommissiePercentageVoorDienst(Dienst $dienst): float
    {
        // 1. Check eerst of er een dienst-specifieke custom commissie is
        $dienstSpecifiek = $this->commissieFactoren()
            ->where('dienst_id', $dienst->id)
            ->actief()
            ->first();
        
        if ($dienstSpecifiek && $dienstSpecifiek->custom_commissie_percentage !== null) {
            \Log::info('💰 Dienst-specifieke commissie gebruikt', [
                'user_id' => $this->id,
                'dienst_id' => $dienst->id,
                'custom_percentage' => $dienstSpecifiek->custom_commissie_percentage
            ]);
            
            return (float) $dienstSpecifiek->custom_commissie_percentage;
        }
        
        // 2. Haal algemene commissie factoren op
        $algemeen = $this->commissieFactoren()
            ->algemeen()
            ->actief()
            ->first();
        
        if (!$algemeen) {
            \Log::info('💰 Standaard dienst commissie gebruikt (geen factoren)', [
                'user_id' => $this->id,
                'dienst_id' => $dienst->id,
                'percentage' => $dienst->commissie_percentage
            ]);
            
            return (float) $dienst->commissie_percentage;
        }
        
        // 3. Bereken: basis dienst commissie + bonussen
        $basisCommissie = (float) $dienst->commissie_percentage;
        $totaleBonus = $algemeen->totale_bonus;
        $finaleCommissie = $basisCommissie + $totaleBonus;
        
        \Log::info('💰 Commissie berekend met bonussen', [
            'user_id' => $this->id,
            'dienst_id' => $dienst->id,
            'basis' => $basisCommissie,
            'diploma' => $algemeen->diploma_factor,
            'ervaring' => $algemeen->ervaring_factor,
            'ancienniteit' => $algemeen->ancienniteit_factor,
            'totale_bonus' => $totaleBonus,
            'finale_commissie' => $finaleCommissie
        ]);
        
        return $finaleCommissie;
    }

    /**
     * Haal algemene commissie factoren op voor deze medewerker
     * 
     * @return MedewerkerCommissieFactor|null
     */
    public function getAlgemeneCommissieFactoren(): ?MedewerkerCommissieFactor
    {
        return $this->commissieFactoren()
            ->algemeen()
            ->actief()
            ->first();
    }

    /**
     * Check of deze medewerker commissie factoren heeft
     * 
     * @return bool
     */
    public function heeftCommissieFactoren(): bool
    {
        return $this->commissieFactoren()->actief()->exists();
    }

    /**
     * Check of gebruiker een medewerker of stagiair is
     * Beide rollen hebben dezelfde rechten en mogelijkheden
     */
    public function isMedewerkerOfStagiair(): bool
    {
        return in_array($this->role, ['medewerker', 'stagiair']);
    }
    
    /**
     * Check of gebruiker admin is van een specifieke organisatie
     */
    public function isAdminOfOrganisatie($organisatieId)
    {
        // Superadmin heeft altijd toegang
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // Check of gebruiker bij deze organisatie hoort
        if ($this->organisatie_id != $organisatieId) {
            return false;
        }
        
        // Check of gebruiker admin role heeft
        return $this->hasRole('admin') || ($this->is_admin ?? false);
    }
    
    /**
     * Haal branding configuratie van gebruiker's organisatie op
     */
    public function getBranding()
    {
        if (!$this->organisatie_id) {
            return null;
        }
        
        $organisatie = $this->organisatie;
        
        if (!$organisatie || !$organisatie->hasCustomBrandingFeature()) {
            return null;
        }
        
        return $organisatie->getBrandingConfig();
    }
}
// End of User class
