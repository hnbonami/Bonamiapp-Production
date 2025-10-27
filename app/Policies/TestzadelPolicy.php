<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Testzadel;

class TestzadelPolicy
{
    /**
     * Bepaal of gebruiker alle testzadels mag bekijken
     */
    public function viewAny(User $user): bool
    {
        // Alleen Admin en SuperAdmin
        return $user->isSuperAdmin() || $user->isBeheerder();
    }

    /**
     * Bepaal of gebruiker een specifieke testzadel mag bekijken
     */
    public function view(User $user, Testzadel $testzadel): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Bepaal of gebruiker een nieuwe testzadel mag aanmaken
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder();
    }

    /**
     * Bepaal of gebruiker een testzadel mag bewerken
     */
    public function update(User $user, Testzadel $testzadel): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder();
    }

    /**
     * Bepaal of gebruiker een testzadel mag verwijderen
     */
    public function delete(User $user, Testzadel $testzadel): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder();
    }
}
