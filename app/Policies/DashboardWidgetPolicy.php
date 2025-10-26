<?php

namespace App\Policies;

use App\Models\DashboardWidget;
use App\Models\User;

class DashboardWidgetPolicy
{
    /**
     * Determine if user can view any widgets
     */
    public function viewAny(User $user): bool
    {
        // Iedereen kan widgets bekijken
        return true;
    }

    /**
     * Determine if user can view specific widget
     */
    public function view(User $user, DashboardWidget $widget): bool
    {
        return $widget->canBeSeenBy($user);
    }

    /**
     * Determine if user can create widgets
     */
    public function create(User $user): bool
    {
        // Alleen medewerkers, admins en super admins mogen widgets maken
        return in_array($user->role, ['medewerker', 'admin', 'super_admin']);
    }

    /**
     * Determine if user can update widget
     */
    public function update(User $user, DashboardWidget $widget): bool
    {
        // Super admin kan alles
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admin kan alles binnen organisatie
        if ($user->role === 'admin') {
            return true;
        }

        // Medewerker kan alleen eigen widgets updaten
        return $widget->created_by === $user->id;
    }

    /**
     * Determine if user can delete widget
     */
    public function delete(User $user, DashboardWidget $widget): bool
    {
        // Super admin kan alles verwijderen
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admin kan alles binnen organisatie verwijderen
        if ($user->role === 'admin') {
            return true;
        }

        // Medewerker kan alleen eigen widgets verwijderen
        return $widget->created_by === $user->id;
    }

    /**
     * Determine if user can drag & drop widgets
     */
    public function rearrange(User $user): bool
    {
        // Iedereen mag zijn eigen layout aanpassen
        return true;
    }
}