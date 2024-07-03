<?php

namespace App\Livewire\Pages\Task\Components;

use App\Models\Task;
use App\Models\TaskTracking;
use App\Services\Notifications\NotificationService;
use App\Services\Task\TaskService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class TaskCard extends Component
{
    use WireUiActions;

    #[Locked]
    public $taskId;

    #[Locked]
    public $projectId;

    #[Locked]
    public $project;

    #[Locked]
    public $task;

    public $userAlredyTrackingThisTask = false;

    public $trackedTime = '00:00:00';

    public $timerRunning = false;

    public function mount($taskId)
    {
        $this->taskId = $taskId;
        $this->task = Task::where('id', $this->taskId)->with('users')->first();
        $this->project = $this->task->project;
        $this->projectId = $this->project->uuid;
        $this->trackedTime = $this->calculateTotalTrackedTime($taskId);
        $trackingTask = TaskTracking::where('user_id', Auth::user()->id)->where('task_id', $this->taskId)
            ->whereNull('end_time')
            ->where('enable_tracking', true)
            ->first();

        if ($trackingTask) {
            $this->userAlredyTrackingThisTask = true;
            $this->timerRunning = true;
        }
    }

    public function calculateTotalTrackedTime($taskId)
    {
        $taskService = app(TaskService::class);
        $time = $taskService->calculateTotalTrackedTime($taskId);

        return $time;
    }

    #[On('end-tracking')]
    public function toggleTimerButton($param)
    {
        if ($this->taskId == $param['id']) {
            $this->userAlredyTrackingThisTask = false;
            $this->timerRunning = false;
        }
    }

    public function startTracking($uuid)
    {
        try {
            $taskService = app(TaskService::class);
            $data = $taskService->startTracking($uuid);

            if ($data['taskId']) {
                $this->userAlredyTrackingThisTask = true;
                $this->dispatch('start-tracking', ['id' => $data['taskId']]);
            }

            if ($data['alreadyTrackingDifferentTask']) {
                $this->dispatch('end-tracking', ['id' => $data['alreadyTrackingDifferentTask']->task_id]);
            }
        } catch (Exception $e) {
            Log::error("Failed to start task tracking: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();
        }
    }

    public function stopTracking($uuid)
    {
        try {
            $taskService = app(TaskService::class);
            $taskId = $taskService->stopTracking($uuid, false);

            if ($taskId) {
                $this->dispatch('end-tracking', ['id' => $taskId]);
            }
        } catch (Exception $e) {
            Log::error("Failed to end task tracking: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();
        }
    }

    public function deleteTaskDialog($uuid)
    {
        $this->dialog()->confirm([
            'title' => 'Are you Sure ?',
            'description' => 'You want to delete this Task ?',
            'icon' => 'error',
            'accept' => [
                'label' => 'Yes, delete it',
                'method' => 'deleteTask',
                'params' => ''.$uuid.'',
            ],
        ]);
    }

    public function deleteTask($uuid)
    {
        try {
            $task = $this->project->tasks()->where('uuid', $uuid)->first();
            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return $this->redirectRoute('projects.show', $this->projectId);
            }
            $task->delete();
            app(NotificationService::class)->sendSuccessNotification('Task deleted successfully');
        } catch (Exception $e) {
            Log::error("Failed to delete task: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.show', $this->projectId);
        }

        return $this->redirectRoute('projects.show', $this->projectId);
    }

    public function render()
    {
        return view('livewire.pages.task.components.task-card');
    }
}
