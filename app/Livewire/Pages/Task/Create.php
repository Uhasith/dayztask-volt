<?php

namespace App\Livewire\Pages\Task;

use App\Models\Project;
use App\Services\Notifications\NotificationService;
use App\Services\Task\TaskService;
use App\Services\Team\TeamService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    #[Validate]
    public $project;

    public $teamMembers = [];

    public $name;

    public $assigned_users = [];

    public $attachments = [];

    public $subtasks = [];

    public $description;

    public $priority = 'medium';

    public $range = 'day';

    public $estimate_time = 1;

    public $deadline;

    public $needToCheck = false;

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
            $project = Project::where('uuid', $uuid)->first();
            if (! $project) {
                app(NotificationService::class)->sendExeptionNotification();

                return $this->redirectRoute('projects.index');
            }
            $this->teamMembers = app(TeamService::class)->getTeamMembers();
            $this->project = $project;

            $this->needToCheck = true;
            $this->check_by_user_id = (string) Auth::id();
        } catch (Exception $e) {
            Log::error("Failed to find project: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.index');
        }
    }

    public function createTask()
    {
        $validatedData = $this->validate();
        $validatedData['user_id'] = Auth::id();
        $validatedData['project_id'] = $this->project->id;
        $validatedData['created_by'] = $this->project->id;
        $uuid = $this->project->uuid;

        if (! empty($validatedData['estimate_time'])) {
            $validatedData['estimate_time'] = $validatedData['estimate_time'] . ' ' . $this->range;
        }

        try {
            $taskService = app(TaskService::class);
            $taskService->createTask($validatedData, $uuid);
            $this->reset();
            app(NotificationService::class)->sendSuccessNotification('Task created successfully');

            return $this->redirectRoute('projects.show', $uuid);
        } catch (Exception $e) {
            Log::error("Failed to create task: {$e->getMessage()}");

            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.show', $uuid);
        }
    }

    public function render()
    {
        return view('livewire.pages.task.create');
    }
}
