<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'open_in_new_tab',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
        'is_archived' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleFor($query, $userRole)
    {
        if (in_array($userRole, ['superadmin', 'admin', 'medewerker'])) {
            return $query; // Staff ziet alles
        }
        
        return $query->where('visibility', 'all'); // Klanten zien alleen 'all'
    }

    // Nieuwe methods voor dashboard content systeem
    public function canDrag($user)
    {
        return in_array($user->role, ['superadmin', 'admin', 'medewerker']);
    }

    public function getTileClassAttribute()
    {
        if (!$this->tile_size) return 'dashboard-tile-medium';
        
        $classes = [
            'small' => 'dashboard-tile-small',
            'medium' => 'dashboard-tile-medium', 
            'large' => 'dashboard-tile-large',
            'banner' => 'dashboard-tile-banner'
        ];
        
        return $classes[$this->tile_size] ?? 'dashboard-tile-medium';
    }

    public function getPriorityColorAttribute()
    {
        if (!$this->priority) return '#3b82f6';
        
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
        if (!$this->type) return '📝';
        
        $icons = [
            'note' => '📝',
            'task' => '✅', 
            'announcement' => '📢',
            'image' => '🖼️',
            'mixed' => '📊'
        ];
        
        return $icons[$this->type] ?? $icons['note'];
    }

    public function getImageUrl()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
