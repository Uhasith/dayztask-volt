<?php

namespace App\Livewire\Pages\Project;

use Exception;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\WireUiActions;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Log;
use App\Services\Notifications\NotificationService;

class Show extends Component
{
    use WireUiActions;

    use WithPagination;

    public $project, $teamMembers = [], $filterBy, $sortBy;

    public function mount($uuid)
    {
        $this->project = Project::where('uuid', $uuid)->firstOrFail();
        $this->teamMembers = app(TeamService::class)->getTeamMembers();
    }

    public function deleteTaskDialog($uuid)
    {
        $this->dialog()->confirm([
            'title'       => 'Are you Sure ?',
            'description' => 'You want to delete this Task ?',
            'icon'        => 'error',
            'accept'      => [
                'label'  => 'Yes, delete it',
                'method' => 'deleteTask',
                'params' => '' . $uuid . '',
            ],
        ]);
    }

    public function deleteTask($uuid)
    {
        try {
            $task = $this->project->tasks()->where('uuid', $uuid)->first();
            if (!$task) {
                app(NotificationService::class)->sendExeptionNotification();
                return $this->redirectRoute('projects.show', $this->project->uuid);
            }
            $task->delete();
            app(NotificationService::class)->sendSuccessNotification('Task deleted successfully');
        } catch (Exception $e) {
            Log::error("Failed to delete task: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification("Failed to delete task due to a server error.");
            return $this->redirectRoute('projects.show', $this->project->uuid);
        }

        return $this->redirectRoute('projects.show', $this->project->uuid);
    }


    public function render()
    {
        $tasks = $this->project->tasks()->orderBy('created_at', 'desc')->with('project')->paginate(6);
        return view('livewire.pages.project.show', [
            'tasks' => $tasks
        ]);
    }
}
