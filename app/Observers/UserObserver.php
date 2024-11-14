<?php

namespace App\Observers;

use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Create a new team for the user
        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);

        // Create a new workspace for the user
        // $workspace = Workspace::forceCreate([
        //     'user_id' => $user->id,
        //     'team_id' => $team->id,
        //     'name' => $user->name.'\'s Workspace',
        //     'description' => $user->name.'\'s Workspace',
        // ]);

        // Set the current_workspace_id on the user
        $user->current_workspace_id = $team->workspaces()->first()->id;

        // Associate the team with the user
        $user->teams()->attach($team->id, ['role' => 'admin']);
        $user->current_team_id = $team->id;
        $user->save();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
