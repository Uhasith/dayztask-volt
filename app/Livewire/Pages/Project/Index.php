<?php

namespace App\Livewire\Pages\Project;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public function mount()
    {
        sleep(1);
    }
    
    public function render()
    {
        $user = User::find(Auth::user()->id);
        $projects = $user->projects()->orderBy('created_at', 'desc')->paginate(9);
        return view('livewire.pages.project.index', [
            'projects' => $projects,
        ]);
    }
}
