<?php

namespace App\Livewire\Pages\Project;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $searchTerm;

    public function render()
    {
        $projects =  Auth::user()->currentTeam->owner->projects()->orderBy('created_at', 'asc')->paginate(9);

        return view('livewire.pages.project.index', [
            'projects' => $projects,
        ]);
    }
}
