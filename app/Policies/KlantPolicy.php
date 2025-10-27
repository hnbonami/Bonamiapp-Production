<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Klant;

class KlantPolicy
{
    /**
     * Bepaal of gebruiker alle klanten mag bekijken
     */
    public function viewAny(User $user): bool
    {
        // Alleen staff mag klanten lijst zien
        return $user->isSuperAdmin() || $user->isBeheerder() || $user->isMedewerker();
    }

    /**
     * Bepaal of gebruiker een specifieke klant mag bekijken
     */
    public function view(User $user, Klant $klant): bool
    {
        // SuperAdmin kan alles
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admin/Medewerker kan klanten in eigen organisatie zien
        if ($user->isBeheerder() || $user->isMedewerker()) {
            return $user->organisatie_id === $klant->organisatie_id;
        }
        
        // Klant kan alleen eigen profiel zien
        if ($user->isKlant()) {
            return $user->email === $klant->email;
        }
        
        return false;
    }

    /**
     * Bepaal of gebruiker een nieuwe klant mag aanmaken
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBeheerder() || $user->isMedewerker();
    }

    /**
     * Bepaal of gebruiker een klant mag bewerken
     */
    public function update(User $user, Klant $klant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Admin/Medewerker mag klanten in eigen organisatie bewerken
        if ($user->isBeheerder() || $user->isMedewerker()) {
            return $user->organisatie_id === $klant->organisatie_id;
        }
        
        return false;
    }

    /**
     * Bepaal of gebruiker een klant mag verwijderen
     */
    public function delete(User $user, Klant $klant): bool
    {
        // Alleen Admin mag verwijderen
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->isBeheerder() && $user->organisatie_id === $klant->organisatie_id;
    }

    /**
     * Bepaal of gebruiker klant mag herstellen (na soft delete)
     */
    public function restore(User $user, Klant $klant): bool
    {
        return $this->delete($user, $klant);
    }

    /**
     * Bepaal of gebruiker klant permanent mag verwijderen
     */
    public function forceDelete(User $user, Klant $klant): bool
    {
        // Alleen SuperAdmin mag force delete
        return $user->isSuperAdmin();
    }
}
