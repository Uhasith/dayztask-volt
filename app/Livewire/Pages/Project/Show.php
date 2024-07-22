<?php

namespace App\Livewire\Pages\Project;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTracking;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\WireUiActions;

class Show extends Component
{
    use WireUiActions;
    use WithPagination;

    public $project;

    public $teamMembers = [];

    public $filterBy;

    public $sortBy;

    public $searchTerm = '';

    public $showOnlyMyTasks = false;

    public $showCompletedTasks = false;

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
        if ($this->searchTerm) {
            // Perform the search with Scout and get the IDs of matching tasks
            $taskIds = Task::search($this->searchTerm)->keys();

            // Now query those tasks to apply additional filters
            $query = Task::with(['project', 'users'])
                ->whereIn('id', $taskIds)
                ->whereHas('project', function ($query) {
                    $query->where('id', $this->project->id);
                });
        } else {
            // If no search term, just apply the filters directly
            $query = Task::with(['project', 'users'])
                ->whereHas('project', function ($query) {
                    $query->where('id', $this->project->id);
                });
        }

        // Apply my tasks filter
        if ($this->showOnlyMyTasks) {
            $query->whereHas('users', function ($query) {
                $query->where('users.id', Auth::user()->id);
            });
        }

        // Apply filter by user if present
        if ($this->filterBy) {
            $query->whereHas('users', function ($query) {
                $query->where('users.id', $this->filterBy);
            });
        }

        // Apply show completed tasks logic
        if ($this->showCompletedTasks) {
            $query->whereIn('status', ['todo', 'doing', 'done']);
        } else {
            $query->whereIn('status', ['todo', 'doing']);
        }

        // Apply sorting logic based on sortBy value
        if ($this->sortBy) {
            switch ($this->sortBy) {
                case 1:
                    $query->orderBy('deadline', 'desc');
                    break;
                case 2:
                    $query->orderBy('deadline', 'asc');
                    break;
                case 3:
                    $query->orderByRaw("
                                CASE
                                    WHEN estimate_time LIKE '% day%' THEN CAST(SUBSTRING_INDEX(estimate_time, ' ', 1) AS UNSIGNED) * 1440
                                    WHEN estimate_time LIKE '% hour%' THEN CAST(SUBSTRING_INDEX(estimate_time, ' ', 1) AS UNSIGNED) * 60
                                    WHEN estimate_time LIKE '% minute%' THEN CAST(SUBSTRING_INDEX(estimate_time, ' ', 1) AS UNSIGNED)
                                END DESC
                            ");
                    break;
                case 4:
                    $query->orderByRaw("
                                CASE
                                    WHEN estimate_time LIKE '% day%' THEN CAST(SUBSTRING_INDEX(estimate_time, ' ', 1) AS UNSIGNED) * 1440
                                    WHEN estimate_time LIKE '% hour%' THEN CAST(SUBSTRING_INDEX(estimate_time, ' ', 1) AS UNSIGNED) * 60
                                    WHEN estimate_time LIKE '% minute%' THEN CAST(SUBSTRING_INDEX(estimate_time, ' ', 1) AS UNSIGNED)
                                END ASC
                            ");
                    break;
                case 5:
                    // High priority first for descending order
                    $query->orderByRaw("FIELD(priority, 'low', 'medium', 'high') DESC");
                    break;
                case 6:
                    // High priority first for ascending order
                    $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low') DESC");
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }


        // Paginate the results
        $tasks = $query->paginate(6);

        return view('livewire.pages.project.show', [
            'tasks' => $tasks,
        ]);
    }
}
