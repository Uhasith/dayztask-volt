<?php

namespace App\Livewire\Pages\Task;

use App\Models\Project;
use App\Services\Notifications\NotificationService;
use App\Services\Team\TeamService;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $project;

    public $teamMembers = [];

    public $name;

    public $assignTo = [];

    public $attachments = [];

    public $newSubTasks = [];

    public $description;

    public $priority = 'medium';

    public $range = 'day';

    public $time = 1;

    public $deadline;
    public $needToCheck = false;
    public $needProof = false;
    public $check_user;
    public $proof_method;

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
        } catch (Exception $e) {
            Log::error("Failed to find project: {$e->getMessage()}");
            app(NotificationService::class)->sendExeptionNotification();

            return $this->redirectRoute('projects.index');
        }
    }

    public function addSubTask()
    {
        $tempCollect = collect($this->newSubTasks)->push([
            'subTask' => '',
        ]);

        $this->newSubTasks = $tempCollect->toArray();
    }

    public function removeSubTask($key)
    {
        unset($this->newSubTasks[$key]);
    }

    public function createTask()
    {
        info($this->newSubTasks);
    }

    public function render()
    {
        return view('livewire.pages.task.create');
    }
}
