<?php

namespace App\Livewire\Pages\Project;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Team\TeamService;

class Show extends Component
{

    use WithPagination;

    public $project, $teamMembers = [], $filterBy, $sortBy;

    public function mount($uuid)
    {
        $this->project = Project::where('uuid', $uuid)->firstOrFail();
        $this->teamMembers = app(TeamService::class)->getTeamMembers();
        // sleep(1);
    }

    public function render()
    {
        $tasks = $this->project->tasks()->orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.pages.project.show', [
            'tasks' => $tasks
        ]);
    }
}
