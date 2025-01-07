<?php

namespace App\Livewire\Pages\Task\Components;

use Exception;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\TaskTracking;
use Livewire\Attributes\Locked;
use WireUi\Traits\WireUiActions;
use App\Services\Task\TaskService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;
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

    public $taskStatus;

    public $userAlredyTrackingThisTask = false;

    public $trackedTime = '00:00:00';

    public $totalTrackedTime = '00:00:00';

    public $timerRunning = false;

    public $subTasksCount = 0;

    public $completedPrecent = 0;

    public function mount($taskId)
    {
        $this->taskId = $taskId;
        $this->fetchData();
    }

    public function fetchData()
    {
        $this->task = Task::where('id', $this->taskId)->with('users', 'subtasks')->first();
        $this->taskStatus = $this->task->status;
        $this->project = $this->task->project;
        $this->projectId = $this->project->uuid;
        $this->trackedTime = $this->calculateTotalTrackedTimeOnTask($this->taskId, Auth::user()->id);
        $this->totalTrackedTime = $this->calculateAllUsersTotalTrackedTimeOnTask($this->taskId);

        if (! empty($this->task['subtasks'])) {
            $this->subTasksCount = count($this->task['subtasks']);
            if ($this->subTasksCount > 0) {
                $completedCount = $this->task['subtasks']->where('is_completed', true)->count();
                $this->completedPrecent = ($completedCount / $this->subTasksCount) * 100;
            } else {
                $this->completedPrecent = 0; // or handle this case appropriately
            }
        }

        if (! empty($this->task['users'])) {
            foreach ($this->task['users'] as $user) {

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

    public function calculateAllUsersTotalTrackedTimeOnTask($taskId)
    {
        $taskService = app(TaskService::class);
        $time = $taskService->calculateAllUsersTotalTrackedTime($taskId);

        return $time;
    }

    public function calculateTotalTrackedTimeOnTask($taskId, $userId)
    {
        $taskService = app(TaskService::class);
        $time = $taskService->calculateTotalTrackedTime($taskId, $userId);

        return $time;
    }

    public function markAsDoneConfirm()
    {
        $this->dialog()->confirm([
            'title' => 'Mark Task as Done ?',
            'description' => 'Do You want to mark this Task as Done ?',
            'icon' => 'warning',
            'accept' => [
                'label' => 'Yes',
                'method' => 'markAsDone',
            ],
        ]);
    }

    public function revertToTodoConfirm()
    {
        $this->dialog()->confirm([
            'title' => 'Mark Task as Todo ?',
            'description' => 'Do You want to revert this Task ?',
            'icon' => 'warning',
            'accept' => [
                'label' => 'Yes',
                'method' => 'revertToTodo',
            ],
        ]);
    }

    public function markAsDone()
    {
        try {
            $taskService = app(TaskService::class);
            $checkIfProofNeeded = $taskService->checkIfProofNeeded($this->taskId);
            Log::info($checkIfProofNeeded);
            if ($checkIfProofNeeded) {
                $this->dialog()->confirm([
                    'title' => 'Task Proof Needed ?',
                    'description' => 'Do You want to upload proof ?',
                    'icon' => 'warning',
                    'accept' => [
                        'label' => 'Yes, upload proof',
                        'method' => 'openUploadProofModal',
                        'params' => '' . $this->taskId . '',
                    ],
                ]);

                return;
            }
            $updatedTask = $taskService->updateTaskStatus($this->taskId, 'done');
            $this->taskStatus = $updatedTask->status;
            app(NotificationService::class)->sendSuccessNotification('Task marked as done successfully');

            $currentRouteName = URL::livewireCurrentRoute();

            if ($currentRouteName == 'projects.show.all') {
                return $this->redirectRoute('projects.show.all');
            }

             $this->redirectRoute('projects.show', $this->projectId);
        } catch (Exception $e) {
            Log::error("Failed to mark task as done: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();
        }
    }

    public function revertToTodo()
    {
        try {
            $taskService = app(TaskService::class);
            $updatedTask = $taskService->updateTaskStatus($this->taskId, 'todo');
            $this->taskStatus = $updatedTask->status;
            app(NotificationService::class)->sendSuccessNotification('Task marked as todo successfully');

            $currentRouteName = URL::livewireCurrentRoute();

            if ($currentRouteName == 'projects.show.all') {
                return $this->redirectRoute('projects.show.all');
            }

             $this->redirectRoute('projects.show', $this->projectId);
        } catch (Exception $e) {
            Log::error("Failed to revert task to todo: {$e->getMessage()}");
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
            $user = Auth::user();
            $todayCheckin = Cache::remember('checkin' . $user->id, 3600 * 24, function () use ($user) {
                $today = Carbon::today()->toDateString(); // Get today's date in 'Y-m-d' format
                return Activity::where('causer_id', $user->id)
                    ->where('causer_type', User::class)
                    ->where('event', 'checkin')
                    ->whereDate('properties->checkin', $today)
                    ->whereNull('properties->checkout')
                    ->first();
            });

            if (! $todayCheckin) {
                app(NotificationService::class)->sendExeptionNotification("Opsie", __("It seems that you missed to checkin today, please checkin first"));
                return;
            }

            $taskService = app(TaskService::class);
            $data = $taskService->startTracking($uuid);

            if ($data['taskId']) {
                $this->userAlredyTrackingThisTask = true;
                $this->dispatch('start-tracking', ['id' => $data['taskId']]);
            }

            if ($data['alreadyTrackingDifferentTask']) {
                $this->dispatch('end-tracking', ['id' => $data['alreadyTrackingDifferentTask']->task_id]);
            }

            $this->taskStatus = $data['updatedTask']->status;
        } catch (Exception $e) {
            Log::error("Failed to start task tracking: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();
        }
    }

    public function stopTracking($uuid)
    {
        try {
            $taskService = app(TaskService::class);
            $data = $taskService->stopTracking($uuid, false);

            if ($data['taskId']) {
                $this->dispatch('end-tracking', ['id' => $data['taskId']]);
            }

            $this->taskStatus = $data['updatedTask']->status;
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
        DB::beginTransaction();
        try {
            $task = $this->project->tasks()->where('uuid', $uuid)->first();
            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return $this->redirectRoute('projects.show', $this->projectId);
            }
            $task->delete();

            DB::commit();

            app(NotificationService::class)->sendSuccessNotification('Task deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete task: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.show', $this->projectId);
        }

        $currentRouteName = URL::livewireCurrentRoute();

        if ($currentRouteName == 'projects.show.all') {
            return $this->redirectRoute('projects.show.all');
        }

         $this->redirectRoute('projects.show', $this->projectId);
    }

    public function openUploadProofModal()
    {
        $this->dispatch('open-proof-modal', modalId: 'proof-upload-modal', taskId: $this->taskId);
    }

    public function render()
    {
        return view('livewire.pages.task.components.task-card');
    }
}
