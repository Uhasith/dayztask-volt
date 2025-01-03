<?php

namespace App\Livewire\Pages\Project;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $searchTerm;

    public function render()
    {
        $projects = Project::where('workspace_id', Auth::user()->current_workspace_id)->orderBy('created_at', 'asc')->paginate(12);

        // Attach the first logo URL to each project
        foreach ($projects as $project) {
            $firstMediaUrl = $project->getFirstMediaUrl('company_logo');
            Log::info($firstMediaUrl ?: 'No logo found'); // Log the result
            $project->company_logo = $firstMediaUrl ?: null; // Assign null if no logo is found
        }

        return view('livewire.pages.project.index', [
            'projects' => $projects,
        ]);
    }
}
