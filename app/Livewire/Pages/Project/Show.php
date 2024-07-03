<?php

namespace App\Livewire\Pages\Project;

use Exception;
use App\Models\Task;
use App\Models\Project;
use Livewire\Component;
use App\Models\TaskTracking;
use Livewire\WithPagination;
use WireUi\Traits\WireUiActions;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Notifications\NotificationService;

class Show extends Component
{
    use WireUiActions;
    use WithPagination;

    public $project;

    public $teamMembers = [];

    public $filterBy;

    public $sortBy;

    public function mount($uuid)
    {
        $this->project = Project::where('uuid', $uuid)->firstOrFail();
        $this->teamMembers = app(TeamService::class)->getTeamMembers();
    }

    public function endTracking($id)
    {
        $userId = Auth::user()->id;
        $taskId = $id;

        // Find the active tracking record
        $taskTracking = TaskTracking::where('task_id', $taskId)
            ->where('user_id', $userId)
            ->whereNull('end_time')
            ->where('enable_tracking', true)
            ->latest()
            ->first();

        // End the current tracking session
        if ($taskTracking) {
            $taskTracking->update([
                'end_time' => now(),
                'enable_tracking' => false,
            ]);
        }

        // Disable any remaining active tracking sessions for the task
        TaskTracking::where('task_id', $taskId)
            ->where('user_id', $userId)
            ->where('enable_tracking', true)
            ->update(['enable_tracking' => false]);

        // Update the task status
        $task = Task::findOrFail($taskId);
        $task->update(['status' => 'todo']);

    }

  

    public function render()
    {
        $tasks = $this->project->tasks()->orderBy('created_at', 'desc')->with('project')->paginate(6);

        return view('livewire.pages.project.show', [
            'tasks' => $tasks,
        ]);
    }
}
