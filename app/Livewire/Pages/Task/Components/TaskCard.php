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
    public  $projectId;

    public $task, $userAlredyTrackingThisTask = false, $trackedTime = '00:00:00', $timerRunning = false;

    public function mount($taskId)
    {
        $this->taskId = $taskId;
        $this->task = Task::where('id', $this->taskId)->with('users')->first();
        $this->projectId = $this->task->project->uuid;
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
        return  $time;
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

                return $this->redirectRoute('projects.show', $this->project->uuid);
            }
            $task->delete();
            app(NotificationService::class)->sendSuccessNotification('Task deleted successfully');
        } catch (Exception $e) {
            Log::error("Failed to delete task: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.show', $this->projectId);
        }

        return $this->redirectRoute('projects.show',$this->projectId);
    }

    public function render()
    {
        return view('livewire.pages.task.components.task-card');
    }
}
