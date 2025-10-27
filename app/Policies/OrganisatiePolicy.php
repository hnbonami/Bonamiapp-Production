<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organisatie;

class OrganisatiePolicy
{
    /**
     * Bepaal of gebruiker alle organisaties mag bekijken
     */
    public function viewAny(User $user): bool
    {
        // Alleen SuperAdmin
        return $user->isSuperAdmin();
    }

    /**
     * Bepaal of gebruiker een specifieke organisatie mag bekijken
     */
    public function view(User $user, Organisatie $organisatie): bool
    {
        // SuperAdmin kan alles
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Gebruiker kan eigen organisatie bekijken
        return $user->organisatie_id === $organisatie->id;
    }

    /**
     * Bepaal of gebruiker een nieuwe organisatie mag aanmaken
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Bepaal of gebruiker een organisatie mag bewerken
     */
    public function update(User $user, Organisatie $organisatie): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Bepaal of gebruiker een organisatie mag verwijderen
     */
    public function delete(User $user, Organisatie $organisatie): bool
    {
        return $user->isSuperAdmin();
    }
}
