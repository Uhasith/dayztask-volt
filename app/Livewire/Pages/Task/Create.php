<?php

namespace App\Livewire\Pages\Task;

use App\Models\Project;
use Livewire\Component;

class Create extends Component
{

    public $project;

    public function mount($uuid)
    {
        $this->project = Project::where('uuid', $uuid)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.pages.task.create');
    }
}
