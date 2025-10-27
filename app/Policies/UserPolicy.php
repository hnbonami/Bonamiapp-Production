<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Bepaal of gebruiker alle users mag bekijken
     */
    public function viewAny(User $user): bool
    {
        // Alleen Admin en SuperAdmin
        return $user->isSuperAdmin() || $user->isBeheerder();
    }

    /**
     * Bepaal of gebruiker een specifieke user mag bekijken
     */
    public function view(User $user, User $targetUser): bool
    {
        // SuperAdmin kan alles
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admin kan users in eigen organisatie zien
        if ($user->isBeheerder()) {
            return $user->organisatie_id === $targetUser->organisatie_id;
        }
        
        // Gebruiker kan zichzelf altijd zien
        return $user->id === $targetUser->id;
    }

    /**
     * Bepaal of gebruiker een nieuwe user mag aanmaken
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder();
    }

    /**
     * Bepaal of gebruiker een user mag bewerken
     */
    public function update(User $user, User $targetUser): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admin mag users in eigen organisatie bewerken
        if ($user->isBeheerder()) {
            // Kan niet zichzelf downgraden
            if ($user->id === $targetUser->id && $targetUser->role !== 'admin') {
                return false;
            }
            return $user->organisatie_id === $targetUser->organisatie_id;
        }
        
        // Gebruiker kan eigen profiel bewerken (maar niet rol)
        return $user->id === $targetUser->id;
    }

    /**
     * Bepaal of gebruiker een user mag verwijderen
     */
    public function delete(User $user, User $targetUser): bool
    {
        // Kan zichzelf niet verwijderen
        if ($user->id === $targetUser->id) {
            return false;
        }
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admin mag users in eigen organisatie verwijderen
        return $user->isBeheerder() && $user->organisatie_id === $targetUser->organisatie_id;
    }

    /**
     * Bepaal of gebruiker user rol mag wijzigen
     */
    public function changeRole(User $user, User $targetUser): bool
    {
        // Alleen SuperAdmin en Admin
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isBeheerder()) {
            // Admin kan alleen in eigen organisatie
            // Admin kan niet zichzelf downgraden
            if ($user->id === $targetUser->id) {
                return false;
            }
            return $user->organisatie_id === $targetUser->organisatie_id;
        }
        
        return false;
    }
}
