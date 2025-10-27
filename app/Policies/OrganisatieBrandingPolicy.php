<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OrganisatieBranding;

class OrganisatieBrandingPolicy
{
    /**
     * Bepaal of gebruiker branding mag bekijken
     */
    public function view(User $user, OrganisatieBranding $branding): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admin/Medewerker kan eigen organisatie branding zien
        return $user->organisatie_id === $branding->organisatie_id;
    }

    /**
     * Bepaal of gebruiker branding mag bewerken
     */
    public function update(User $user, OrganisatieBranding $branding): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Alleen Admin mag branding bewerken
        return $user->isBeheerder() && $user->organisatie_id === $branding->organisatie_id;
    }

    /**
     * Bepaal of gebruiker nieuwe branding mag aanmaken
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder();
    }
}
