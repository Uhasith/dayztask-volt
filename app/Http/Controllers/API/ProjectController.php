<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    function getProjects(Request $request) : JsonResponse {
        $projects = $request->user()->userProjects()->get();
        return response()->json($projects);
    }

    function webGetProjects() : mixed {
        $projects = Project::where('workspace_id', auth()->user()->current_workspace_id)->get();
        return $projects;
    }
}
