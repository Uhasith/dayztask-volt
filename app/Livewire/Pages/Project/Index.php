<?php

namespace App\Livewire\Pages\Project;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $searchTerm;

    public function render()
    {
        $projects = Project::where('workspace_id', Auth::user()->current_workspace_id)->orderBy('created_at', 'asc')->paginate(12);

        return view('livewire.pages.project.index', [
            'projects' => $projects,
        ]);
    }
}
