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
        'organisatie_id',
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

    public function organisatie()
    {
        return $this->belongsTo(Organisatie::class);
    }

    public function scopeVisibleFor($query, $userRole, $organisatieId = null)
    {
        // Superadmin ziet alles
        if ($userRole === 'superadmin') {
            return $query;
        }
        
        // Admin en medewerkers zien alleen content van eigen organisatie
        if (in_array($userRole, ['admin', 'medewerker', 'organisatie_admin'])) {
            if ($organisatieId) {
                return $query->where('organisatie_id', $organisatieId);
            }
            return $query;
        }
        
        // Klanten zien alleen 'all' content van hun organisatie
        $query->where('visibility', 'all');
        if ($organisatieId) {
            $query->where('organisatie_id', $organisatieId);
        }
        
        return $query;
    }

    // Nieuwe methods voor dashboard content systeem
    public function canDrag($user)
    {
        // Superadmin kan alles slepen
        if ($user->role === 'superadmin') {
            return true;
        }
        
        // Admin en medewerkers kunnen alleen content van eigen organisatie slepen
        if (in_array($user->role, ['admin', 'medewerker', 'organisatie_admin'])) {
            return $this->organisatie_id === $user->organisatie_id;
        }
        
        return false;
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
