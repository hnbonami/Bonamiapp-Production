<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardUserLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'widget_id',
        'grid_x',
        'grid_y',
        'grid_width',
        'grid_height',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /**
     * Gebruiker van deze layout
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Widget van deze layout
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(DashboardWidget::class);
    }
}