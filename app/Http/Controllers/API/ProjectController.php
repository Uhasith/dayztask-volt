<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    function getProjects(Request $request) : JsonResponse {
        $projects = $request->user()->userProjects()->get();
        return response()->json($projects);
    }
}
