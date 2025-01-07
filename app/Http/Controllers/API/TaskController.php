<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\Task\TaskService;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Database\Eloquent\Collection;

class TaskController extends Controller
{
    function getProjectTasks(Request $request, $project_id) {
        $tasks = Task::where('project_id', $project_id)->whereHas('users', function (Builder $query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->get();
        return response()->json($tasks);
    }

    function trackTask(Request $request, $task_id) : JsonResponse {
        $task = Task::find($task_id);
        $taskService = app(TaskService::class);
        if($request->get('type') === 'stop')
            return response()->json($taskService->stopTracking($task->uuid));
        else
        return response()->json($taskService->startTracking($task->uuid));
    }

    function uploadScreenshot(Request $request, $task_id) : void {
        $screenshot = $request->file('screenshot');
        Log::info($request->get('test'));
        $filename = "task-$task_id-" . Carbon::now()->format('Y-m-d, H:i:s') . '.' . $screenshot->getClientOriginalExtension();
        $stored_path = $screenshot->storeAs('uploads', $filename, 'public');

        $task = Task::find($task_id);
        $task->addMedia(\Storage::disk('public')->path($stored_path))->withCustomProperties(['user_id' => $request->user()->id, 'display_index' => $request->get('display_index'), 'display_count' => $request->get('display_count')])->toMediaCollection('screenshot');
    }

    function getTeamTasks(Request $request) : Collection {
        return Task::whereHas('project', function (Builder $query) use ($request) {
            $query->where('workspace_id', $request->user()->current_workspace_id);
        })->get();
    }
}
