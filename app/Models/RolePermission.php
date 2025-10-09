<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'tab_name', 
        'can_access'
    ];

    protected $casts = [
        'can_access' => 'boolean'
    ];

    /**
     * Get all tabs accessible for a role
     */
    public static function getAccessibleTabs($roleName)
    {
        return self::where('role_name', $roleName)
                  ->where('can_access', true)
                  ->pluck('tab_name')
                  ->toArray();
    }

    /**
     * Check if role can access specific tab
     */
    public static function canAccessTab($roleName, $tabName)
    {
        return self::where('role_name', $roleName)
                  ->where('tab_name', $tabName)
                  ->where('can_access', true)
                  ->exists();
    }

    /**
     * Set tab access for role
     */
    public static function setTabAccess($roleName, $tabName, $canAccess)
    {
        return self::updateOrCreate(
            ['role_name' => $roleName, 'tab_name' => $tabName],
            ['can_access' => $canAccess]
        );
    }
}