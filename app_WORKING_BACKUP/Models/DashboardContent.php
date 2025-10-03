<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StaffNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'visibility',
        'user_id',
        'type',
        'tile_size', 
        'image_path',
        'background_color',
        'text_color',
        'priority',
        'sort_order',
        'is_archived',
        'expires_at',
        'template_id',
        'published_at',
        'is_pinned',
        'link_url',
        'open_in_new_tab'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
        'is_archived' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeVisibleFor($query, $userRole)
    {
        if (in_array($userRole, ['admin', 'medewerker'])) {
            return $query; // Staff ziet alles
        }
        
        return $query->where('visibility', 'all'); // Klanten zien alleen 'all'
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopePublished($query)
    {
        return $query->where(function($q) {
            $q->whereNull('published_at')
              ->orWhere('published_at', '<=', now());
        });
    }

    public function scopeByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low') DESC");
    }

    public function scopeByOrder($query)
    {
        return $query->orderBy('is_pinned', 'desc')
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getTileClassAttribute()
    {
        $classes = [
            'mini' => 'dashboard-tile-mini',
            'small' => 'dashboard-tile-small',
            'medium' => 'dashboard-tile-medium', 
            'large' => 'dashboard-tile-large',
            'banner' => 'dashboard-tile-banner'
        ];
        
        return $classes[$this->tile_size] ?? 'dashboard-tile-medium';
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => '#6b7280',
            'medium' => '#3b82f6',
            'high' => '#f59e0b',
            'urgent' => '#ef4444'
        ];
        
        return $colors[$this->priority] ?? $colors['medium'];
    }

    public function getTypeIconAttribute()
    {
        $icons = [
            'note' => '📝',
            'task' => '✅', 
            'announcement' => '📢',
            'image' => '🖼️',
            'mixed' => '📊'
        ];
        
        return $icons[$this->type] ?? $icons['note'];
    }

    // Methods
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canDrag($user)
    {
        return in_array($user->role, ['admin', 'medewerker']);
    }

    public function getImageUrl()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}