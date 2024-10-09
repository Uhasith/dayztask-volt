<?php

namespace App\Livewire\Global;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Workspace extends Component
{
    public $workspaces;

    public $workspaceId;

    public function mount()
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $team = $user->currentTeam;

        // Retrieve workspaces for the current team
        $this->workspaces = $team->workspaces;

        // Check if the current_workspace_id is part of the team's workspaces
        if (! $this->workspaces->contains('id', $user->current_workspace_id)) {
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

        Session::put('workspaceId', $this->workspaceId);
    }

    public function updatedWorkspaceId($value)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $user->current_workspace_id = $value;
        $user->save();
        Session::put('workspaceId', $value);

        return $this->redirectRoute('dashboard');
    }

    public function render()
    {
        return view('livewire.global.workspace');
    }
}
