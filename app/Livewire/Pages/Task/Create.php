<?php

namespace App\Livewire\Pages\Task;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Livewire\Global\Quill;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Log;

class Create extends Component
{
    use WithFileUploads;
    
    public $project, $teamMembers = [];

    public $name, $assignTo = [], $attachments = [], $description, $priority = 'medium', $range = 'day', $time = 1, $deadline, $newAvatar = [];

    public function mount($uuid)
    {
        $this->project = Project::where('uuid', $uuid)->firstOrFail();
        $this->teamMembers = app(TeamService::class)->getTeamMembers();
    }

    #[On(Quill::EVENT_VALUE_UPDATED)] 
    public function quill_value_updated($value)
    {
        Log::info($value);
        $this->description = $value;
    }

    public function render()
    {
        return view('livewire.pages.task.create');
    }
}
