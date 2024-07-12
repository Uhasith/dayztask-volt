<?php

namespace App\Livewire\Pages\Task;

use Exception;
use App\Models\Task;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Services\Task\TaskService;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Notifications\NotificationService;
use Livewire\Attributes\Locked;

class Update extends Component
{
    use WithFileUploads;

    #[Validate]
    public $task;
    public $project;

    #[Locked]
    public $taskId;

    public $teamMembers = [];

    public $name;

    public $assigned_users = [];

    public $old_attachments = [];

    public $attachments = [];

    public $subtasks = [];

    public $description;

    public $priority;

    public $range;

    public $estimate_time;

    public $deadline;

    public $needToCheck = true;

    public $needToConfirm = false;

    public $needProof = false;

    public $needFollowUp = false;

    public $isBillable = false;

    public $check_by_user_id;

    public $confirm_by_user_id;

    public $follow_up_user_id;

    public $follow_up_message;

    public $invoice_reference;

    public $recurring_period;

    public $proof_method;

    public $oldRemovedSubTasks = [];
    public $oldRemovedAttachments = [];

    public function rules()
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'follow_up_message' => 'nullable|string',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
            'check_by_user_id' => 'nullable|exists:users,id',
            'confirm_by_user_id' => 'nullable|exists:users,id',
            'follow_up_user_id' => 'nullable|exists:users,id',
            'proof_method' => 'nullable|string',
            'invoice_reference' => 'nullable|string',
            'estimate_time' => 'nullable|numeric',
            'deadline' => 'nullable|date',
            'attachments' => 'max:102400',
            'recurring_period' => 'nullable|numeric',
            'subtasks' => 'nullable|array',
        ];
    }

    public function mount($uuid)
    {
        try {
            $task = Task::with('project')->where('uuid', $uuid)->first();
            if (! $task) {
                app(NotificationService::class)->sendExeptionNotification();

                return $this->redirectRoute('projects.index');
            }
            $this->teamMembers = app(TeamService::class)->getTeamMembers();
            $this->task = $task;
            $this->project = $task->project;
            $this->taskId = $task->id;

            // Set default values from the task
            $this->name = $task->name;
            $this->description = $task->description;
            $this->priority = $task->priority;
            $this->follow_up_message = $task->follow_up_message;

            $this->assigned_users = $task->users->pluck('pivot.user_id')->map(function ($user_id) {
                return (string) $user_id;
            })->toArray();

            $this->check_by_user_id = $task->check_by_user_id;
            if (! empty($this->check_by_user_id)) {
                $this->needToCheck = true;
            }

            $this->confirm_by_user_id = $task->confirm_by_user_id;
            if (! empty($this->confirm_by_user_id)) {
                $this->needToConfirm = true;
            }

            $this->follow_up_user_id = $task->follow_up_user_id;
            if (! empty($this->confirm_by_user_id)) {
                $this->needFollowUp = true;
            }

            $this->proof_method = $task->proof_method;
            if (!empty($this->proof_method)) {
                $this->needProof = true;
            }

            $this->invoice_reference = $task->invoice_reference;
            if (! empty($this->invoice_reference)) {
                $this->isBillable = true;
            }

            $this->deadline = $task->deadline;
            // $this->recurring_period = $task->recurring_period;

            $this->old_attachments = $task->getMedia('attachments');

            if (! empty($task->estimate_time)) {

                // Assuming $task->estimate_time contains the time estimate as a string
                $estimateTime = $task->estimate_time;

                // Regular expression to match the number and the unit
                if (preg_match('/(\d+)\s*(\w+)/', $estimateTime, $matches)) {
                    $this->estimate_time = (int) $matches[1]; // The numerical part
                    $this->range = $matches[2];      // The unit part (day, hour, minute etc.)
                } else {
                    // Handle the case where the string does not match the expected format
                    $this->estimate_time = 0; // Default or error value
                    $this->range = ''; // Default or error value
                }
            }

            $subtasks = $task->subTasks->map(function ($subtask) {

                return [
                    'id' => $subtask->id,
                    'subTask' => $subtask->name,
                    'is_completed' => $subtask->is_completed,
                    'old' => true
                ];
            });

            $this->subtasks = $subtasks;
 
           
        } catch (Exception $e) {
            Log::error("Failed to find task: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.index');
        }
    }

    #[On('remove-upload')]
    public function removeUploads($params)
    {
        $this->oldRemovedAttachments[] = $params['id'];
    }

    public function updateTask()
    {
        $validatedData = $this->validate();
        $validatedData['oldRemovedSubTasks'] = $this->oldRemovedSubTasks;
        $validatedData['oldRemovedAttachments'] = $this->oldRemovedAttachments;
        $validatedData['task_id'] = $this->taskId;
        $uuid = $this->project->uuid;

        if (!empty($validatedData['estimate_time'])) {
            $validatedData['estimate_time'] = $validatedData['estimate_time'] . ' ' . $this->range;
        }

        try {
            $taskService = app(TaskService::class);
            $taskService->updateTask($validatedData);
            $this->reset();
            app(NotificationService::class)->sendSuccessNotification('Task updated successfully');

            return $this->redirectRoute('projects.show', $uuid);
        } catch (Exception $e) {
            Log::error("Failed to update task: {$e->getMessage()}");

            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.show', $uuid);
        }
    }

    public function render()
    {
        return view('livewire.pages.task.update');
    }
}
