<?php

namespace App\Livewire\Pages\Task;

use Exception;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Log;
use App\Services\Notifications\NotificationService;

class Create extends Component
{
    use WithFileUploads;

    public $project, $teamMembers = [];

    public $name, $assignTo = [], $attachments = [], $description, $priority = 'medium', $range = 'day', $time = 1, $deadline;

    public function mount($uuid)
    {
        try {
            $project = Project::where('uuid', $uuid)->first();
            if (!$project) {
                app(NotificationService::class)->sendExeptionNotification();
                return $this->redirectRoute('projects.index');
            }
            $this->teamMembers = app(TeamService::class)->getTeamMembers();
            $this->project = $project;
        } catch (Exception $e) {
            Log::error("Failed to find project: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification("Failed to create task due to a server error.");
            return $this->redirectRoute('projects.index');
        }
    }

    public function render()
    {
        return view('livewire.pages.task.create');
    }
}
