<?php

namespace App\Livewire\Pages\Project;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        $user = User::find(Auth::user()->id);
        $projects = $user->projects()->orderBy('created_at', 'asc')->paginate(9);

        return view('livewire.pages.project.index', [
            'projects' => $projects,
        ]);
    }
}
