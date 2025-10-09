<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleTestPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'test_type',
        'can_access',
        'can_create', 
        'can_edit'
    ];

    protected $casts = [
        'can_access' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean'
    ];

    /**
     * Check if role can perform action on test type
     */
    public static function canPerformAction($roleName, $testType, $action = 'access')
    {
        $permission = self::where('role_name', $roleName)
                         ->where('test_type', $testType)
                         ->first();

        if (!$permission) {
            return false;
        }

        switch ($action) {
            case 'access':
                return $permission->can_access;
            case 'create':
                return $permission->can_create;
            case 'edit':
                return $permission->can_edit;
            default:
                return false;
        }
    }

    /**
     * Get all test types accessible for role
     */
    public static function getAccessibleTests($roleName, $action = 'access')
    {
        $query = self::where('role_name', $roleName);
        
        switch ($action) {
            case 'create':
                $query->where('can_create', true);
                break;
            case 'edit':
                $query->where('can_edit', true);
                break;
            default:
                $query->where('can_access', true);
        }

        return $query->pluck('test_type')->toArray();
    }

    /**
     * Set test permission for role
     */
    public static function setTestPermission($roleName, $testType, $permissions)
    {
        return self::updateOrCreate(
            ['role_name' => $roleName, 'test_type' => $testType],
            $permissions
        );
    }
}