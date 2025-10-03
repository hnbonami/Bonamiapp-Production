<?php

namespace App\Policies;

use App\Models\StaffNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffNotePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true; // Iedereen mag dashboard content bekijken
    }

    public function view(User $user, StaffNote $staffNote)
    {
        // Staff ziet alles, klanten zien alleen 'all' visibility
        if (in_array($user->role, ['admin', 'medewerker'])) {
            return true;
        }
        
        return $staffNote->visibility === 'all';
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'medewerker']);
    }

    public function update(User $user, StaffNote $staffNote)
    {
        return in_array($user->role, ['admin', 'medewerker']);
    }

    public function delete(User $user, StaffNote $staffNote)
    {
        return in_array($user->role, ['admin', 'medewerker']);
    }

    public function manage(User $user)
    {
        // Voor drag & drop en order wijzigingen
        return in_array($user->role, ['admin', 'medewerker']);
    }
}