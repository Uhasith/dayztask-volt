<?php

namespace App\Livewire\Pages\Project\Components;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Notifications\NotificationService;

class ProjectCard extends Component
{
    public $project;

    public function send()
    {
        $user = auth()->user();

        $title = 'Saved successfully';
        $body = 'Changes to the post have been saved.';

        // Use the service to send a notification
        app(NotificationService::class)->sendDBNotification($user, $title, $body);
    }

    public function render()
    {
        return view('livewire.pages.project.components.project-card');
    }
}
