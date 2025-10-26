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
        'background_color',
        'text_color',
        'grid_x',
        'grid_y',
        'grid_width',
        'grid_height',
        'visibility',
        'created_by',
        'is_active',
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
}