<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\Task\TaskService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    function getProjectTasks(Request $request, $project_id) {
        $tasks = Task::where('project_id', $project_id)->whereHas('users', function (Builder $query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->get();
        return response()->json($tasks);
    }

    function trackTask(Request $request, $task_id) : void {
        Log::info($request->all());
        $task = Task::find($task_id);
        $taskService = app(TaskService::class);
        if($request->get('type') === 'stop')
            $data = $taskService->stopTracking($task->uuid);
        else
            $data = $taskService->startTracking($task->uuid);
    }

    function uploadScreenshot(Request $request, $task_id) : void {
        Log::info($request->all());
    }
}
