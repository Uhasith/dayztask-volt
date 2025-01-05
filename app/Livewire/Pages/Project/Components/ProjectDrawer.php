<?php

namespace App\Livewire\Pages\Project\Components;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use App\Services\Notifications\NotificationService;

class ProjectDrawer extends Component
{
    public $showEditDrawer = false;

    public $showCreateDrawer = false;

    public $project = null;

    #[On('close-modal')]
    public function close($id)
    {
        if ($id == 'project-drawer') {
            $this->reset();
        }
    }

    #[On('openProjectCreateDrawer')]
    public function openCreateDrawer()
    {
        $this->showCreateDrawer = true;
        $this->dispatch('open-modal', id: 'project-drawer');
    }

    #[On('openProjectEditDrawer')]
    public function openEditDrawer($id)
    {
        $this->project = Project::find($id);
        if (! $this->project) {
            // Use the service to send a notification
            app(NotificationService::class)->sendExeptionNotification();
        } else {
            $this->showEditDrawer = true;
            $this->dispatch('open-modal', id: 'project-drawer');
        }
    }

    public function render()
    {
        return view('livewire.pages.project.components.project-drawer');
    }
}
