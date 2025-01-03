<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class CheckinController extends Controller
{
    function checkin(Request $request) : void {
        Log::info(($request->all()));

        if($request->type == 'checkin'){
            $user = $request->user();
            $location = $request->location;
            if(!Cache::pull('checkin'.$user->id)){
                $todayCheckin = Cache::remember('checkin'.$user->id, 3600*24, function() use ($user, $location){
                    $activity = activity()->causedBy($user)->withProperties(['checkin' => now(), 'location' => $location])->event('checkin')->log('checkin');
                    return  Activity::find($activity->id);
                });
            }
        }elseif($request->type == 'checkout'){
            $user = $request->user();
            $todayCheckin = Cache::pull('checkin'.$user->id);
            $day_end_update = $request->day_end_update;

            if($todayCheckin){
                $checkin_data = $todayCheckin->properties->toArray();
                $checkin_data['checkout'] = now();
                $checkin_data['update'] = $day_end_update;
                $todayCheckin->properties = $checkin_data;
                $todayCheckin->save();
            }else{
                if($this->fetchTodaysCheckin($user)){
                    $this->checkin($request);
                }
            }
        }
    }

    function fetchTodaysCheckin($user) : bool {
        $todayCheckin = Cache::remember('checkin'.$user->id, 3600*24, function() use ($user){
            $today = Carbon::today()->toDateString(); // Get today's date in 'Y-m-d' format
            return Activity::where('causer_id', $user->id)
                ->where('causer_type', User::class)
                ->where('event', 'checkin')
                ->whereDate('properties->checkin', $today)
                ->whereNull('properties->checkout')
                ->first();
        });

        return $todayCheckin ? true : false;
    }
}
