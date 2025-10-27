<?php

namespace App\Policies;

use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardWidgetPolicy
{
    use HandlesAuthorization;

    /**
     * Bepaal of user widgets mag bekijken
     */
    public function viewAny(User $user)
    {
        // Iedereen die ingelogd is mag dashboard zien
        return true;
    }

    /**
     * Bepaal of user een specifieke widget mag bekijken
     */
    public function view(User $user, DashboardWidget $widget)
    {
        // Super admin mag alleen widgets van organisatie 1 zien
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $widget->organisatie_id === 1;
        }

        // Anderen zien alleen widgets van hun eigen organisatie
        return $widget->organisatie_id === $user->organisatie_id;
    }

    /**
     * Bepaal of user een nieuwe widget mag aanmaken
     */
    public function create(User $user)
    {
        // Super admin mag alleen widgets aanmaken in organisatie 1
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $user->organisatie_id === 1;
        }

        // Klanten mogen GEEN widgets aanmaken
        if ($user->role === 'klant') {
            return false;
        }

        // Admin en medewerkers mogen widgets aanmaken
        return in_array($user->role, ['admin', 'organisatie_admin', 'medewerker']);
    }

    /**
     * Bepaal of user een widget mag bewerken
     */
    public function update(User $user, DashboardWidget $widget)
    {
        // Super admin mag alleen widgets van organisatie 1 bewerken
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $widget->organisatie_id === 1;
        }

        // Klanten mogen NOOIT bewerken
        if ($user->role === 'klant') {
            return false;
        }

        // Check of widget tot eigen organisatie behoort
        if ($widget->organisatie_id !== $user->organisatie_id) {
            return false;
        }

        // Admin mag alles binnen eigen organisatie bewerken
        if (in_array($user->role, ['admin', 'organisatie_admin'])) {
            return true;
        }

        // Medewerker mag alleen eigen widgets bewerken
        if ($user->role === 'medewerker') {
            return $widget->created_by === $user->id;
        }

        return false;
    }

    /**
     * Bepaal of user een widget mag verwijderen
     */
    public function delete(User $user, DashboardWidget $widget)
    {
        // Super admin mag alleen widgets van organisatie 1 verwijderen
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $widget->organisatie_id === 1;
        }

        // Klanten mogen NOOIT verwijderen
        if ($user->role === 'klant') {
            return false;
        }

        // Check of widget tot eigen organisatie behoort
        if ($widget->organisatie_id !== $user->organisatie_id) {
            return false;
        }

        // Admin mag alles binnen eigen organisatie verwijderen
        if (in_array($user->role, ['admin', 'organisatie_admin'])) {
            return true;
        }

        // Medewerker mag alleen eigen widgets verwijderen
        if ($user->role === 'medewerker') {
            return $widget->created_by === $user->id;
        }

        return false;
    }

    /**
     * Bepaal of user widgets mag drag & droppen
     */
    public function drag(User $user, DashboardWidget $widget)
    {
        // Super admin mag alleen widgets van organisatie 1 verplaatsen
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $widget->organisatie_id === 1;
        }

        // Check of widget tot eigen organisatie behoort
        if ($widget->organisatie_id !== $user->organisatie_id) {
            return false;
        }

        // Iedereen binnen eigen organisatie mag drag & droppen
        return true;
    }

    /**
     * Bepaal of user widgets mag resizen
     */
    public function resize(User $user, DashboardWidget $widget)
    {
        // Super admin mag alleen widgets van organisatie 1 resizen
        if (in_array($user->role, ['super_admin', 'superadmin'])) {
            return $widget->organisatie_id === 1;
        }

        // Klanten mogen NIET resizen
        if ($user->role === 'klant') {
            return false;
        }

        // Check of widget tot eigen organisatie behoort
        if ($widget->organisatie_id !== $user->organisatie_id) {
            return false;
        }

        // Admin mag alles binnen eigen organisatie resizen
        if (in_array($user->role, ['admin', 'organisatie_admin'])) {
            return true;
        }

        // Medewerker mag alleen eigen widgets resizen
        if ($user->role === 'medewerker') {
            return $widget->created_by === $user->id;
        }

        return false;
    }
}