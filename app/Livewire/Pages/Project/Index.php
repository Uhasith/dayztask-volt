<?php

namespace App\Livewire\Pages\Project;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public function mount()
    {
        sleep(3);
    }
    
    public function render()
    {
        $user = User::find(Auth::user()->id);
        return view('livewire.pages.project.index', [
            'projects' => $user->projects()->paginate(9),
        ]);
    }
}
