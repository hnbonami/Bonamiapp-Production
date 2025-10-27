<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sjabloon;

class SjabloonPolicy
{
    /**
     * Bepaal of gebruiker alle sjablonen mag bekijken
     */
    public function viewAny(User $user): bool
    {
        // Admin en SuperAdmin
        return $user->isSuperAdmin() || $user->isBeheerder();
    }

    /**
     * Bepaal of gebruiker een specifieke sjabloon mag bekijken
     */
    public function view(User $user, Sjabloon $sjabloon): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Bepaal of gebruiker een nieuwe sjabloon mag aanmaken
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder();
    }

    /**
     * Bepaal of gebruiker een sjabloon mag bewerken
     */
    public function update(User $user, Sjabloon $sjabloon): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder();
    }

    /**
     * Bepaal of gebruiker een sjabloon mag verwijderen
     */
    public function delete(User $user, Sjabloon $sjabloon): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder();
    }
}
