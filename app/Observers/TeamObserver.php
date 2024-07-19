<?php

namespace App\Observers;

use App\Models\Team;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class TeamObserver
{
    /**
     * Handle the Team "created" event.
     */
    public function created(Team $team): void
    {
        $workspace = Workspace::forceCreate([
            'user_id' => $team->user_id,
            'team_id' => $team->id,
            'name' => $team->name.'\'s Workspace',
            'description' => $team->name.'\'s Workspace',
        ]);

        $user = Auth::user();
        $user->current_workspace_id = $workspace->id;
        $user->save();

    }

    /**
     * Handle the Team "updated" event.
     */
    public function updated(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "deleted" event.
     */
    public function deleted(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "restored" event.
     */
    public function restored(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "force deleted" event.
     */
    public function forceDeleted(Team $team): void
    {
        //
    }
}
