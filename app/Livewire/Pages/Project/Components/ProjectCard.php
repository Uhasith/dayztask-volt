<?php

namespace App\Livewire\Pages\Project\Components;

use App\Services\Notifications\NotificationService;
use Livewire\Component;

class ProjectCard extends Component
{
    public $project;

    public function send()
    {
        $user = auth()->user();

        $title = 'Saved successfully';
        $body = 'Changes to the post have been saved.';

        // Use the service to send a notification
        app(NotificationService::class)->sendDBNotificationWithoutAction($user, $title, $body);
    }

    public function render()
    {
        return view('livewire.pages.project.components.project-card');
    }
}
