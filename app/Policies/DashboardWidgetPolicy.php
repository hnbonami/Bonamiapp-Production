<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DashboardWidget;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardWidgetPolicy
{
    use HandlesAuthorization;

    /**
     * Bepaal of user widgets mag bekijken
     */
    public function viewAny(User $user): bool
    {
        // Iedereen die ingelogd is mag dashboard zien
        return true;
    }

    /**
     * Bepaal of user een specifieke widget mag bekijken
     */
    public function view(User $user, DashboardWidget $widget): bool
    {
        // Als de zichtbaarheid 'iedereen' is, mag iedereen het zien
        if ($widget->visibility === 'everyone') {
            return true;
        }
        
        // Als de zichtbaarheid 'alleen ik' is, mag alleen de maker het zien
        if ($widget->visibility === 'only_me') {
            return $user->id === $widget->created_by;
        }
        
        // Anders, alleen zien binnen eigen organisatie
        return $user->organisatie_id === $widget->organisatie_id;
    }

    /**
     * Bepaal of user een nieuwe widget mag aanmaken
     */
    public function create(User $user): bool
    {
        // Alleen admin, organisatie admin en superadmin mogen widgets aanmaken
        return in_array($user->role, ['admin', 'organisatie_admin', 'superadmin']);
    }

    /**
     * Bepaal of user een widget mag bewerken
     */
    public function update(User $user, DashboardWidget $widget): bool
    {
        // Admin en superadmin mogen alles bewerken
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return true;
        }
        
        // Anders, alleen de maker mag bewerken
        return $user->id === $widget->created_by;
    }

    /**
     * Bepaal of user een widget mag verwijderen
     */
    public function delete(User $user, DashboardWidget $widget): bool
    {
        // Admin en superadmin mogen alles verwijderen
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return true;
        }
        
        // Anders, alleen de maker mag verwijderen
        return $user->id === $widget->created_by;
    }

    /**
     * Bepaal of user de lay-out mag bijwerken
     */
    public function updateLayout(User $user): bool
    {
        // Iedereen mag de lay-out bijwerken
        return true;
    }
}