<?php

namespace App\Livewire\Pages\Task;

use Exception;
use App\Models\Task;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Log;
use App\Services\Notifications\NotificationService;

class Update extends Component
{
    use WithFileUploads;

    #[Validate]
    public $task;

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
            $task = Task::where('uuid', $uuid)->first();
            if (!$task) {
                app(NotificationService::class)->sendExeptionNotification();

                return $this->redirectRoute('projects.index');
            }
            $this->teamMembers = app(TeamService::class)->getTeamMembers();
            $this->task = $task;

            // Set default values from the task
            $this->name = $task->name;
            $this->description = $task->description;
            $this->priority = $task->priority;
            $this->follow_up_message = $task->follow_up_message;

            $this->assigned_users = $task->users->pluck('pivot.user_id')->map(function ($user_id) {
                return (string) $user_id;
            })->toArray();

            $this->check_by_user_id = $task->check_by_user_id;
            $this->confirm_by_user_id = $task->confirm_by_user_id;
            $this->follow_up_user_id = $task->follow_up_user_id;
            $this->proof_method = $task->proof_method;
            $this->invoice_reference = $task->invoice_reference;
            $this->old_attachments = $task->getMedia("attachments");

            if(!empty($task->estimate_time)) {

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

            $this->deadline = $task->deadline;
            // $this->recurring_period = $task->recurring_period;
            // $this->subtasks = $task->subtasks;
        } catch (Exception $e) {
            Log::error("Failed to find task: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.index');
        }
    }

    #[On('remove-upload')] 
    public function removeUploads($params){
        Log::info($params);
    }

    public function createTask()
    {
       Log::info($this->attachments);
    }

    public function render()
    {
        return view('livewire.pages.task.update');
    }
}
