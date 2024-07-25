<?php

namespace App\Livewire\Pages\Task\Components;

use Exception;
use App\Models\Task;
use App\Models\User;
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
        $this->task = Task::where('id', $this->taskId)->with('users', 'subtasks')->first();
        $this->taskStatus = $this->task->status;
        $this->project = $this->task->project;
        $this->projectId = $this->project->uuid;
        $this->trackedTime = $this->calculateTotalTrackedTimeOnTask($this->taskId, Auth::user()->id);
        $this->totalTrackedTime = $this->calculateAllUsersTotalTrackedTimeOnTask($this->taskId);

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

    public function markAsDone()
    {
        try {
            $taskService = app(TaskService::class);
            $updatedTask = $taskService->markAsDone($this->taskId);
            $this->taskStatus = $updatedTask->status;
            app(NotificationService::class)->sendSuccessNotification('Task marked as done successfully');
        } catch (Exception $e) {
            Log::error("Failed to mark task as done: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();
        }
    }

    public function revertToTodo()
    {
        try {
            $taskService = app(TaskService::class);
            $updatedTask = $taskService->revertToTodo($this->taskId);
            $this->taskStatus = $updatedTask->status;
            app(NotificationService::class)->sendSuccessNotification('Task marked as todo successfully');
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

    public function updateStatus($status)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $team = $user->currentTeam;
        $roleName = $user->teamRole($team)->key;

        $this->task->status = $status;
        $this->task->save();

        if ($this->task->status == 'todo' && $this->task->check_by_user_id) {
            $this->task->is_checked = null;
            $this->task->is_confirmed = null;
            $this->task->is_mark_as_done = false;
            $this->task->is_archived = false;
            $this->task->save();
        }

        if ($this->task->status == 'todo' && $this->task->follow_up_user_id) {

            $followUpTask = Task::where('name', 'like', '%' . $this->task->follow_up_message . '%')->first();

            if ($followUpTask) {
                $followUpTask->delete();
            }
        }

        if ($this->task->check_by_user_id &&  $this->task->status == 'done' && $roleName != 'owner') {

            $this->task->is_checked = null;
            $this->task->is_confirmed = null;
            $this->task->is_mark_as_done = true;
            $this->task->save();

            $title =  'Please check your checklist ';
            $body = 'Fantastic news! ' . $user->name . ' has successfully completed ' . $this->task->name . '. Please review it on your checklist to appreciate the achievement.';

            $checkByUser = User::where('id', $this->task->check_by_user_id)->first();

            app(NotificationService::class)->sendUserTaskDBNotification($checkByUser, $title, $body, $this->task->id);

            // $mailData = [
            //     'email' => $task->check_by->email,
            //     'email_subject' => 'Task Completed.',
            //     'email_body' => 'Fantastic news! ' . $request->user()->name . ' has successfully completed ' . $task->name . '. Please review it on your checklist to appreciate the achievement.',
            //     'task' => $task,
            //     'user' => $task->check_by,
            //     'caused_by' => $request->user()
            // ];
        } else {

            $this->task->is_checked = true;
            $this->task->is_confirmed = true;
            $this->task->is_mark_as_done = true;
            $this->task->save();
        }

        if ($this->task->follow_up_user_id && $this->task->status == 'done') {

            $lastTask = Task::where('project_id', $this->projectId)->orderBy('id', 'desc')->first();

            if ($lastTask) {
                $taskOrder = $lastTask->page_order + 1;
            } else {
                $taskOrder = 0;
            }

            $followUpTask = Task::create([
                'name' => $this->task->follow_up_message ? $this->task->follow_up_message : $this->task->name . ' Follow Up',
                'user_id' => $user->id, 'order' => $taskOrder, 'project_id' => $this->projectId, 'priority' => 'high', 'status' => 'todo',
            ]);

            // Notification::create([
            //     'workspace_id' => $project->workspace_id,
            //     'project_id' => $project->id,
            //     'task_id' => $followUpTask->id,
            //     'content' => 'You are assigned to a Follow Up Task named ' . $followUpTask->name,
            //     'type' => 'assigned',
            //     'user_id' => $task->follow_up_user,
            //     'caused_by' =>   $request->user()->id,
            // ]);

            // $mailData = [
            //     'email' => $task->follow_up_by->email,
            //     'email_subject' => 'Assigned to a Follow up Task.',
            //     'email_body' => 'Your next mission awaits: ' . $followUpTask->name . '. You\'re now responsible for a Follow-Up Task with this intriguing name.',
            //     'task' => $task,
            //     'user' => $task->follow_up_by
            // ];
        }

        // $checklist_count = $request->user()->tasks()
        //     ->where('mark_as_done', true)
        //     ->where(function ($query) use ($request) {
        //         $query->where('check_user', $request->user()->id)
        //             ->orWhere('confirm_user', $request->user()->id);
        //     })
        //     ->whereNull('checked')
        //     ->whereNull('confirmed')
        //     ->count();

        // return response()->json(['checklist_count' => $checklist_count]);
    }

    public function render()
    {
        return view('livewire.pages.task.components.task-card');
    }
}
