<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateUserTeamAndWorkspace($uuid)
    {
        // Retrieve the task
        $task = Task::with('project.workspace')->where('uuid', $uuid)->firstOrFail();

        // Get the project and workspace
        $project = $task->project;
        $workspace = $project->workspace;

        // Update the authenticated user's current team and workspace
        /** @var \App\Models\User */
        $user = Auth::user();
        $user->current_team_id = $workspace->team_id;
        $user->current_workspace_id = $workspace->id;
        $user->save();

        // Redirect to the task update route
        return redirect()->route('projects.tasks.update', $task->uuid);
    }
}
