<?php

namespace App\Services\Team;

use Illuminate\Support\Facades\Auth;

class TeamService
{
    public function getGuestUsers()
    {
        $user = Auth::user();
        if (! $user || ! $user->currentTeam) {
            return []; // Return an empty array or handle differently if needed
        }

        return $user->currentTeam->allUsers()->pluck('name', 'id');
    }

    public function getTeamMembers()
    {
        $user = Auth::user();
        if (! $user || ! $user->currentTeam) {
            return []; // Return an empty array or handle differently if needed
        }

        return $user->currentTeam->allUsers();
    }
}
