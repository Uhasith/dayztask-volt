<?php

namespace App\Livewire\Pages\Task;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Team\TeamService;

class Create extends Component
{
    use WithFileUploads;
    
    public $project, $teamMembers = [];

    public $name, $assignTo = [], $attachments = [], $description, $priority = 'medium', $range = 'day', $time = 1, $deadline;

    public function mount($uuid)
    {
        $this->project = Project::where('uuid', $uuid)->firstOrFail();
        $this->teamMembers = app(TeamService::class)->getTeamMembers();
    }

    public function render()
    {
        return view('livewire.pages.task.create');
    }
}
