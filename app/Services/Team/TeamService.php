<?php

namespace App\Services\Team;

use Illuminate\Support\Facades\Auth;

class TeamService
{
    public function getGuestUsers()
    {
        $guestUsers = Auth::user()->currentTeam->allUsers()->pluck('name', 'id');
        return $guestUsers;
    }

     public function getTeamMembers()
    {
        $teamMembers = Auth::user()->currentTeam->allUsers();
        return $teamMembers;
    }
}
