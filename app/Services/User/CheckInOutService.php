<?php

namespace App\Services\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

/**
 * Class CheckInOutService
 * @package App\Services
 */
class CheckInOutService
{
    function fetchTodaysCheckin($user): Activity|bool
    {
        // Retrieve today's checkin activity or store it in cache
        $todayCheckin = Cache::remember('checkin' . $user->id, 3600 * 24, function () use ($user) {
            $today = Carbon::today()->toDateString(); // Get today's date in 'Y-m-d' format
            return Activity::where('causer_id', $user->id)
                ->where('causer_type', User::class)
                ->where('event', 'checkin')
                ->whereDate('properties->checkin', $today)
                ->whereNull('properties->checkout')
                ->first();
        });

        return $todayCheckin ?? false;
    }

    function setCheckStatus($location, $user): Activity
    {
        $todayCheckin = Cache::remember('checkin' . $user->id, 3600 * 24, function () use ($user, $location) {
            $activity = activity()
                ->causedBy($user)
                ->withProperties(['checkin' => now(), 'location' => $location])
                ->event('checkin')
                ->log('checkin');
            return Activity::find($activity->id);
        });
        return $todayCheckin;
    }

    function updateCheckout($user, $todayCheckin, $data): bool
    {
        // $todayCheckin = Cache::pull('checkin' . $user->id);
        if ($todayCheckin) {
            $checkin_data = $todayCheckin->properties->toArray();
            $checkin_data['checkout'] = now();
            $checkin_data['update'] = $data['day_end_update'];
            $todayCheckin->properties = $checkin_data;
            $todayCheckin->save();
            return true;
        }
        return false;
    }
}
