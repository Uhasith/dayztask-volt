<?php

namespace App\Livewire\Pages\Project\Components;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class ProjectCreateDrawer extends Component
{
    public $showDrawer = false;
    public $project = null;

    #[On('close-modal')]
    public function close($id)
    {
        if ($id == 'create-project') {
            Log::info('Here');
            $this->showDrawer = false;
        }
    }

    #[On('openDrawer')]
    public function open($id)
    {
        $this->project = Project::find($id);
        if (!$this->project) {
            Notification::make()
                ->title('Something went wrong')
                ->danger()
                ->body('Please contact support team to resolve this issue.')
                ->send();
        } else {
            $this->showDrawer = true;
            $this->dispatch('open-modal', id: 'create-project');
        }
    }

    public function render()
    {
        return view('livewire.pages.project.components.project-create-drawer');
    }
}
