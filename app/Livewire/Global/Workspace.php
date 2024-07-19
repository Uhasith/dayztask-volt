<?php

namespace App\Livewire\Global;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Workspace extends Component
{
    public $workspaces, $workspaceId;

    public function mount()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // Retrieve workspaces for the current team
        $this->workspaces = $team->workspaces;

        // Check if the current_workspace_id is part of the team's workspaces
        if (!$this->workspaces->contains('id', $user->current_workspace_id)) {
            // Set current_workspace_id to the first workspace in the list if not present
            $firstWorkspace = $this->workspaces->first();

            if ($firstWorkspace) {
                $this->workspaceId = $firstWorkspace->id;
                $user->current_workspace_id = $firstWorkspace->id;
                $user->save();
            }
        } else {
            // If the current_workspace_id is valid, set it
            $this->workspaceId = $user->current_workspace_id;
        }
    }

    public function render()
    {
        return view('livewire.global.workspace');
    }
}
