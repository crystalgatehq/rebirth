<?php

namespace App\Policies;

use App\Models\ContactGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactGroupPolicy
{
    use HandlesAuthorization;

    public function view(User $user, ContactGroup $contactGroup): bool
    {
        return $contactGroup->canBeViewedBy($user);
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can create contact groups
    }

    public function update(User $user, ContactGroup $contactGroup): bool
    {
        return $contactGroup->canBeEditedBy($user);
    }

    public function delete(User $user, ContactGroup $contactGroup): bool
    {
        // Only the owner can delete the contact group
        return $user->id === $contactGroup->user_id;
    }

    public function manageMembers(User $user, ContactGroup $contactGroup): bool
    {
        // Only the owner or users with manage_members permission can manage members
        if ($user->id === $contactGroup->user_id) {
            return true;
        }

        $config = $contactGroup->configuration;
        $permissions = $config['permissions']['can_manage_members'] ?? ['owner'];

        if (in_array('owner', $permissions) && $contactGroup->user_id === $user->id) {
            return true;
        }

        if (in_array('team', $permissions) && !empty($contactGroup->shared_with_teams)) {
            $userTeamIds = $user->teams()->pluck('teams.id')->toArray();
            if (count(array_intersect($contactGroup->shared_with_teams, $userTeamIds)) > 0) {
                return true;
            }
        }

        return false;
    }
}
