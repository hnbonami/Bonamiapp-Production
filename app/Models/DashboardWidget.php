<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'content',
        'chart_type',
        'chart_data',
        'image_path',
        'button_text',
        'button_url',
        'button_color', // Knop kleur
        'background_color',
        'text_color',
        'grid_x',
        'grid_y',
        'grid_width',
        'grid_height',
        'visibility',
        'created_by',
        'organisatie_id', // ⚡ TOEGEVOEGD
        'is_active',
        'metric_type', // NIEUW: Type metric (custom, mijn_bikefits, totaal_klanten, etc.)
    ];

    protected $casts = [
        'chart_data' => 'array',
        'is_active' => 'boolean',
    ];

    // Widget types constanten
    const TYPE_CHART = 'chart';
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_BUTTON = 'button';
    const TYPE_METRIC = 'metric';

    // Chart types
    const CHART_LINE = 'line';
    const CHART_BAR = 'bar';
    const CHART_PIE = 'pie';
    const CHART_DOUGHNUT = 'doughnut';

    /**
     * Widget maker
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Organisatie eigenaar van dit widget
     */
    public function organisatie(): BelongsTo
    {
        return $this->belongsTo(Organisatie::class, 'organisatie_id');
    }

    /**
     * User layouts voor dit widget
     */
    public function userLayouts(): HasMany
    {
        return $this->hasMany(DashboardUserLayout::class, 'widget_id');
    }

    /**
     * Check of user dit widget mag zien op basis van visibility en role
     */
    public function canBeSeenBy(User $user): bool
    {
        // Altijd zichtbaar voor super admin
        if ($user->role === 'super_admin') {
            return true;
        }

        // Check visibility setting
        if ($this->visibility === 'only_me') {
            return $this->created_by === $user->id;
        }

        if ($this->visibility === 'medewerkers') {
            return in_array($user->role, ['medewerker', 'admin', 'super_admin']);
        }

        // 'everyone' - iedereen mag zien
        return true;
    }

    /**
     * Get layout voor specifieke user
     */
    public function getLayoutForUser(User $user)
    {
        return $this->userLayouts()->where('user_id', $user->id)->first();
    }

    // Scopes
    public function scopeForOrganisatie($query, $organisatieId)
    {
        return $query->where('organisatie_id', $organisatieId);
    }

    public function scopeVisibleFor($query, User $user)
    {
        // Super admin ziet alleen widgets van organisatie 1
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $query->where('organisatie_id', 1)
                ->where(function($q) use ($user) {
                    $q->where('visibility', '!=', 'only_me')
                      ->orWhere('created_by', $user->id);
                });
        }

        // Basis query: widgets van eigen organisatie
        $query->where('organisatie_id', $user->organisatie_id);

        // Klanten zien:
        // - everyone widgets
        // - only_me widgets die ze zelf hebben gemaakt (voor welkomst widget!)
        if ($user->role === 'klant') {
            return $query->where(function($q) use ($user) {
                $q->where('visibility', 'everyone')
                  ->orWhere(function($subQ) use ($user) {
                      $subQ->where('visibility', 'only_me')
                           ->where('created_by', $user->id);
                  });
            });
        }

        // Medewerkers en admins zien:
        // - everyone widgets
        // - medewerkers widgets
        // - only_me widgets die ze zelf hebben gemaakt
        return $query->where(function($q) use ($user) {
            $q->where('visibility', 'everyone')
              ->orWhere('visibility', 'medewerkers')
              ->orWhere(function($subQ) use ($user) {
                  $subQ->where('visibility', 'only_me')
                       ->where('created_by', $user->id);
              });
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function canBeViewedBy(User $user)
    {
        // Super admin mag alleen widgets van organisatie 1 zien
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $this->organisatie_id === 1;
        }

        // Anderen zien alleen widgets van hun eigen organisatie
        return $this->organisatie_id === $user->organisatie_id;
    }

    public function canBeEditedBy(User $user)
    {
        // Super admin mag alleen widgets van organisatie 1 bewerken
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $this->organisatie_id === 1;
        }

        // Klanten mogen nooit bewerken
        if ($user->role === 'klant') {
            return false;
        }

        // Check organisatie
        if ($this->organisatie_id !== $user->organisatie_id) {
            return false;
        }

        // Admin mag alles binnen eigen organisatie
        if (in_array($user->role, ['admin', 'organisatie_admin'])) {
            return true;
        }

        // Medewerker mag alleen eigen widgets
        return $this->created_by === $user->id;
    }

    public function canBeDeletedBy(User $user)
    {
        return $this->canBeEditedBy($user);
    }

    public function canBeDraggedBy(User $user)
    {
        // Super admin mag alleen widgets van organisatie 1 verplaatsen
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $this->organisatie_id === 1;
        }

        // Check organisatie
        if ($this->organisatie_id !== $user->organisatie_id) {
            return false;
        }

        // Iedereen binnen eigen organisatie mag drag & droppen
        return true;
    }

    public function canBeResizedBy(User $user)
    {
        // Super admin mag alleen widgets van organisatie 1 resizen
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $this->organisatie_id === 1;
        }

        // Klanten mogen niet resizen
        if ($user->role === 'klant') {
            return false;
        }

        // Check organisatie
        if ($this->organisatie_id !== $user->organisatie_id) {
            return false;
        }

        // Admin mag alles binnen eigen organisatie
        if (in_array($user->role, ['admin', 'organisatie_admin'])) {
            return true;
        }

        // Medewerker mag alleen eigen widgets
        return $this->created_by === $user->id;
    }
}