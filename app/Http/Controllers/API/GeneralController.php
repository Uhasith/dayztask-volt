<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TaskTracking;
use App\Services\User\CheckInOutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class GeneralController extends Controller
{
    function fetchCurrentState(Request $request) : JsonResponse {
        $checkIn = app(CheckInOutService::class)->fetchTodaysCheckin($request->user());
        $current_tracking_task = TaskTracking::where('user_id', $request->user()->id)->where('enable_tracking', 1)->with('project')->first();
        $data = array(
            'current_checkin' => $checkIn,
            'current_tracking' => $current_tracking_task
        );
        return response()->json($data);
    }
}
