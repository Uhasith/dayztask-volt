<?php

namespace App\Services\Team;

use App\Models\Task;
use App\Models\Project;
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
