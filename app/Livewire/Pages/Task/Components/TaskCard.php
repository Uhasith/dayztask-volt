<?php

namespace App\Livewire\Pages\Task\Components;

use Exception;
use App\Models\Task;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\TaskTracking;
use Livewire\Attributes\Locked;
use WireUi\Traits\WireUiActions;
use App\Services\Task\TaskService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Notifications\NotificationService;

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
    public $userAssigned = false;
    public $subTasksCount = 0;
    public $completedPrecent = 0;

    public function mount($taskId)
    {
        $this->taskId = $taskId;
        $this->task = Task::where('id', $this->taskId)->with('users', 'subtasks')->first();
        $this->project = $this->task->project;
        $this->projectId = $this->project->uuid;
        $this->trackedTime = $this->calculateTotalTrackedTimeOnTask($this->taskId, Auth::user()->id);

        if (!empty($this->task['subtasks'])) {
            $this->subTasksCount = count($this->task['subtasks']);
            if ($this->subTasksCount > 0) {
                $completedCount = $this->task['subtasks']->where('is_completed', true)->count();
                $this->completedPrecent = ($completedCount / $this->subTasksCount) * 100;
            } else {
                $this->completedPrecent = 0; // or handle this case appropriately
            }
        }
      
        if (!empty($this->task['users'])) {
            foreach ($this->task['users'] as $user) {

                if($user->id == Auth::user()->id) {
                    $this->userAssigned = true;
                }

                $user->trackedTime = $this->calculateTotalTrackedTimeOnTask($this->taskId, $user->id);
                $userTrackingTask = TaskTracking::where('user_id', $user->id)
                    ->where('task_id', $this->taskId)
                    ->whereNull('end_time')
                    ->where('enable_tracking', true)
                    ->first();

                $user->userAlreadyTrackingThisTask = $userTrackingTask ? true : false;
                $user->timerRunning = $userTrackingTask ? true : false;
            }
        }

        $trackingTask = TaskTracking::where('user_id', Auth::user()->id)->where('task_id', $this->taskId)
            ->whereNull('end_time')
            ->where('enable_tracking', true)
            ->first();

        if ($trackingTask) {
            $this->userAlredyTrackingThisTask = true;
            $this->timerRunning = true;
        }
    }

    public function calculateTotalTrackedTimeOnTask($taskId, $userId)
    {
        $taskService = app(TaskService::class);
        $time = $taskService->calculateTotalTrackedTime($taskId, $userId);

        return $time;
    }

    public function markAsDone()
    {
        try {
            $taskService = app(TaskService::class);
            $taskService->markAsDone($this->taskId);
            app(NotificationService::class)->sendSuccessNotification('Task marked as done successfully');
        } catch (Exception $e) {
            Log::error("Failed to start task tracking: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();
        }
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
