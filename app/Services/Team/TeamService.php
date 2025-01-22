<?php

namespace App\Services\Team;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\TaskTracking;
use App\Services\Task\TaskService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TeamService
{
    public function getGuestUsers(): mixed
    {
        $user = Auth::user();
        if (! $user || ! $user->currentTeam) {
            return [];
        }

        return $user->currentTeam->allUsers()->pluck('name', 'id');
    }

    public function getTeamMembers(): mixed
    {
        $user = Auth::user();
        if (! $user || ! $user->currentTeam) {
            return [];
        }

        return $user->currentTeam->allUsers();
    }

    public function getTeamMembersIds(): mixed
    {
        $user = Auth::user();
        if (! $user || ! $user->currentTeam) {
            return [];
        }

        return $user->currentTeam->allUsers()->pluck('id');
    }

    public function getTeamMembersData(array | Collection $teamMembers = []): mixed
    {
        $users = User::whereIn('id', $teamMembers)
            ->withCount([
                'tasks as open_task_count' => function ($query) {
                    $query->where('status', 'todo');
                },
                'tasks as missed_deadline_count' => function ($query) {
                    $query->where('status', '!=', 'done')->whereNotNull('deadline')
                        ->where('deadline', '<', now());
                }
            ])
            ->get();

        return $users->map(function ($user) {
            // Check if the user is currently tracking a task
            $userTrackingTask = TaskTracking::where('user_id', $user->id)
                ->whereNull('end_time')
                ->where('enable_tracking', true)
                ->first();

            $noTaskFound = false;

            if ($userTrackingTask) {
                $taskId = $userTrackingTask->task_id;
                $task = $userTrackingTask->task;
                $trackedTime = $this->calculateTotalTrackedTimeOnTask($taskId, $user->id);
                $timerRunning = true;
            } else {
                // If not currently tracking, retrieve the last tracked task
                $lastTrackedTask = TaskTracking::where('user_id', $user->id)
                    ->latest()
                    ->first();

                $timerRunning = false;

                if ($lastTrackedTask) {
                    $taskId = $lastTrackedTask->task_id;
                    $task = $lastTrackedTask->task;
                    $trackedTime = $this->calculateTotalTrackedTimeOnTask($taskId, $user->id);
                } else {
                    // No tracked tasks found
                    $taskId = null;
                    $task = null;
                    $trackedTime = 0;
                    $noTaskFound = true;
                }
            }

            return [
                ...$user->toArray(),
                'open_task_count' => $user->open_task_count,
                'missed_deadline_count' => $user->missed_deadline_count,
                'tracking_task_id' => $taskId,
                'tracking_task' => $task,
                'tracked_time' => $trackedTime,
                'timer_running' => $timerRunning,
                'no_task_found' => $noTaskFound
            ];
        });
    }

    public function calculateTotalTrackedTimeOnTask($taskId, $userId)
    {
        $taskService = app(TaskService::class);
        $time = $taskService->calculateTotalTrackedTime($taskId, $userId);

        return $time;
    }


    public function getCheckList(): array|Collection
    {
        $user = Auth::user();
        if (! $user || ! $user->currentTeam) {
            return [];
        }

        $projectIds = Project::where('workspace_id', Auth::user()->current_workspace_id)->pluck('id');
        $checkList = Task::with(['project', 'users'])
            ->whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->where(function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('check_by_user_id', $user->id)
                        ->where('is_checked', false);
                })->orWhere(function ($subQuery) use ($user) {
                    $subQuery->where('confirm_by_user_id', $user->id)
                        ->where('is_confirmed', false);
                });
            })->get();

        return $checkList;
    }

    public function getCheckListCount(): int
    {
        $user = Auth::user();
        if (! $user || ! $user->currentTeam) {
            return 0;
        }

        $projectIds = $user->currentTeam->owner->projects->pluck('id');
        $checkListCount = Task::with(['project', 'users'])
            ->whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->where(function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('check_by_user_id', $user->id)
                        ->where('is_checked', false);
                })->orWhere(function ($subQuery) use ($user) {
                    $subQuery->where('confirm_by_user_id', $user->id)
                        ->where('is_confirmed', false);
                });
            })->count();

        return $checkListCount;
    }
}
