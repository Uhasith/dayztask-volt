<?php

namespace App\Livewire\Pages\Task;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Global\Quill;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Log;

class Create extends Component
{
    public $project, $teamMembers = [];

    public $name, $assignTo = [], $description;

    public function mount($uuid)
    {
        $this->project = Project::where('uuid', $uuid)->firstOrFail();
        $this->teamMembers = app(TeamService::class)->getTeamMembers();
    }

    #[On(Quill::EVENT_VALUE_UPDATED)] 
    public function quill_value_updated($value)
    {
        $this->description = $value;
    }

    public function render()
    {
        return view('livewire.pages.task.create');
    }
}
