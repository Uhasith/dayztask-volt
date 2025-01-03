<?php

use Livewire\Volt\Component;
use App\Models\TaskTracking;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use WireUi\Traits\WireUiActions;
use App\Services\Task\TaskService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;
use App\Services\Notifications\NotificationService;

new class extends Component {
    public $user;
    public $task;
    public $show = false;
    public $taskId;

    public function mount()
    {
        $this->getTrackingDetails();
    }

    public function getTrackingDetails()
    {
        $this->user = auth()->user();
        $userTrackingTask = TaskTracking::where('user_id', $this->user->id)
            ->whereNull('end_time')
            ->where('enable_tracking', true)
            ->first();

        if ($userTrackingTask) {
            $this->taskId = $userTrackingTask->task_id;
            $this->task = $userTrackingTask->task;
            $this->user->trackedTime = $this->calculateTotalTrackedTimeOnTask($this->taskId, $this->user->id);
            $this->show = true;
        } else {
            // Retrieve the last tracked task
            $lastTrackedTask = TaskTracking::where('user_id', $this->user->id)
                ->latest()
                ->first();

            if ($lastTrackedTask) {
                $this->taskId = $lastTrackedTask->task_id;
                $this->task = $lastTrackedTask->task;
                $this->user->trackedTime = $this->calculateTotalTrackedTimeOnTask($this->taskId, $this->user->id);
                $this->show = true;
            } else {
                // No tracked tasks found
                $this->taskId = null;
                $this->task = null;
                $this->user->trackedTime = 0;
                $this->show = false;
            }
        }

        $this->user->userAlreadyTrackingThisTask = $userTrackingTask ? true : false;
        $this->user->timerRunning = $userTrackingTask ? true : false;
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

            if (!$todayCheckin) {
                app(NotificationService::class)->sendExeptionNotification('Opsie', __('It seems that you missed to checkin today, please checkin first'));
                return;
            }

            $taskService = app(TaskService::class);
            $data = $taskService->startTracking($uuid);

            if ($data['taskId']) {
                $this->user->userAlreadyTrackingThisTask = true;
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

    #[On('start-tracking')]
    public function listenStartTracking($id)
    {
        $this->mount();
    }

    #[On('end-tracking')]
    public function listenEndTracking($id)
    {
         $this->mount();
    }

    public function calculateTotalTrackedTimeOnTask($taskId, $userId)
    {
        $taskService = app(TaskService::class);
        $time = $taskService->calculateTotalTrackedTime($taskId, $userId);

        return $time;
    }
}; ?>

<div>
    @if ($show)
        <div class="flex items-center justify-between bg-white border-teal-300 border rounded-lg px-3 py-2" wire:key="userWorkingTimer-{{ $user['uuid'] }}">
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.tasks.update', $task['uuid']) }}" class="text-sm font-semibold"
                    wire:navigate>
                    {{ $task['name'] }}
                </a>
                @if ($user->userAlreadyTrackingThisTask)
                    <x-mary-icon name="m-pause" xs class=" text-blue-400 hover:text-blue-600 cursor-pointer w-5 h-5"
                            x-tooltip.placement.top.raw="Stop Tracking"
                            wire:click="stopTracking('{{ $task['uuid'] }}')" />
                @else
                    <x-mary-icon name="m-play" class=" text-blue-400 hover:text-blue-600 cursor-pointer w-5 h-5"
                            x-tooltip.placement.top.raw="Start Tracking"
                            wire:click="startTracking('{{ $task['uuid'] }}')" />
                @endif
            </div>
            <div class="ml-3 h-full">
                <livewire:global.timer class="" :key="'userGlobalTimer-' . $taskId" :trackedTime="$user['trackedTime']" :timerRunning="$user['timerRunning']" :taskId="$taskId" />
            </div>
        </div>
    @endif
</div>
