<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisatie extends Model
{
    use HasFactory;

    protected $table = 'organisaties';

    protected $fillable = [
        'naam',
        'email',
        'telefoon',
        'adres',
        'postcode',
        'plaats',
        'btw_nummer',
        'logo_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'sidebar_color',
        'text_color',
        'custom_css',
        'branding_enabled',
        'status',
        'trial_eindigt_op',
        'maandelijkse_prijs',
        'notities',
    ];

    protected $casts = [
        'trial_eindigt_op' => 'date',
        'maandelijkse_prijs' => 'decimal:2',
    ];

    /**
     * Relatie: organisatie heeft meerdere gebruikers
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organisatie_id');
    }

    /**
     * Relatie: organisatie heeft meerdere klanten
     */
    public function klanten(): HasMany
    {
        return $this->hasMany(Klant::class, 'organisatie_id');
    }

    /**
     * Check of organisatie actief is
     */
    public function isActief(): bool
    {
        return $this->status === 'actief';
    }

    /**
     * Check of trial verlopen is
     */
    public function isTrialVerlopen(): bool
    {
        if ($this->status !== 'trial' || !$this->trial_eindigt_op) {
            return false;
        }

        return $this->trial_eindigt_op->isPast();
    }

    /**
     * Scope: alleen actieve organisaties
     */
    public function scopeActief($query)
    {
        return $query->where('status', 'actief');
    }

    /**
     * Features die deze organisatie heeft
     */
    public function features()
    {
        return $this->belongsToMany(Feature::class, 'organisatie_features')
            ->withPivot(['expires_at', 'is_actief', 'notities'])
            ->withTimestamps();
    }

    /**
     * Check of organisatie een specifieke feature heeft
     * 
     * @param string $featureKey De key van de feature (bijv. 'bikefits')
     * @return bool
     */
    public function hasFeature(string $featureKey): bool
    {
        // Superadmin organisatie heeft altijd alle features
        if ($this->id === 1) {
            \Log::info("✅ Organisatie {$this->id} is superadmin - heeft alle features");
            return true;
        }

        $hasFeature = $this->features()
            ->where('key', $featureKey)
            ->where('organisatie_features.is_actief', true)
            ->where(function($query) {
                $query->whereNull('organisatie_features.expires_at')
                      ->orWhere('organisatie_features.expires_at', '>', now());
            })
            ->exists();

        \Log::info("🔍 Feature check voor organisatie {$this->id} ({$this->naam})", [
            'feature_key' => $featureKey,
            'has_access' => $hasFeature,
            'total_features' => $this->features()->count(),
            'active_features' => $this->features()->wherePivot('is_actief', true)->count()
        ]);

        return $hasFeature;
    }

    /**
     * Geef een feature aan deze organisatie
     * 
     * @param string|Feature $feature Feature key of Feature model
     * @param array $options Opties zoals expires_at, notities
     * @return void
     */
    public function enableFeature($feature, array $options = []): void
    {
        $featureId = $feature instanceof Feature ? $feature->id : Feature::where('key', $feature)->firstOrFail()->id;
        
        $this->features()->syncWithoutDetaching([
            $featureId => array_merge([
                'is_actief' => true,
                'expires_at' => $options['expires_at'] ?? null,
                'notities' => $options['notities'] ?? null,
            ], $options)
        ]);
    }

    /**
     * Haal een feature weg van deze organisatie
     * 
     * @param string|Feature $feature Feature key of Feature model
     * @return void
     */
    public function disableFeature($feature): void
    {
        $featureId = $feature instanceof Feature ? $feature->id : Feature::where('key', $feature)->firstOrFail()->id;
        
        $this->features()->updateExistingPivot($featureId, [
            'is_actief' => false
        ]);
    }

    /**
     * Toggle een feature voor deze organisatie
     * 
     * @param string|Feature $feature Feature key of Feature model
     * @return bool Nieuwe status (true = enabled, false = disabled)
     */
    public function toggleFeature($feature): bool
    {
        if ($this->hasFeature($feature)) {
            $this->disableFeature($feature);
            return false;
        } else {
            $this->enableFeature($feature);
            return true;
        }
    }

    /**
     * Haal alle actieve features op voor deze organisatie
     */
    public function activeFeatures()
    {
        return $this->features()
            ->wherePivot('is_actief', true)
            ->where(function($query) {
                $query->whereNull('organisatie_features.expires_at')
                      ->orWhere('organisatie_features.expires_at', '>', now());
            });
    }
    
    /**
     * Geef logo URL terug (of fallback)
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path && $this->branding_enabled) {
            return asset('storage/' . $this->logo_path);
        }
        return asset('images/bonami-logo.png'); // Fallback naar default Bonami logo
    }
    
    /**
     * Geef favicon URL terug (of fallback)
     */
    public function getFaviconUrlAttribute()
    {
        if ($this->favicon_path && $this->branding_enabled) {
            return asset('storage/' . $this->favicon_path);
        }
        return asset('favicon.ico'); // Fallback
    }
    
    /**
     * Haal actieve themakleuren op
     */
    public function getThemeColorsAttribute()
    {
        if (!$this->branding_enabled) {
            // Return default Bonami kleuren
            return [
                'primary' => '#3b82f6',
                'secondary' => '#c8e1eb',
                'sidebar' => '#1e293b',
                'text' => '#111111',
            ];
        }
        
        return [
            'primary' => $this->primary_color,
            'secondary' => $this->secondary_color,
            'sidebar' => $this->sidebar_color,
            'text' => $this->text_color,
        ];
    }
}
