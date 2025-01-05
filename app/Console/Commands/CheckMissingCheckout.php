<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\MissingCheckoutNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;

class CheckMissingCheckout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:check-missing-checkout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for users who didn\'n checkout, and notify them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Find activities without a 'checkout' for the current day
        $missingCheckouts = Activity::where('event', 'checkin')
            ->whereNull('properties->checkout')
            ->get();

        if ($missingCheckouts->isEmpty()) {
            $this->info('No missing checkouts found.');
            return 0;
        }

        foreach ($missingCheckouts as $activity) {
            $user = $activity->causer;

            if ($user) {
                Notification::send($user, new MissingCheckoutNotification($activity));
                // Broadcast event
                broadcast(new \App\Events\MissingCheckoutEvent($user, $activity));

                $checkin_data = $activity->properties->toArray();
                $checkin_data['checkout'] = now();
                $checkin_data['update'] = 'Did not checked out for the day! This is an automated checkout by the system. HR must take action to fix the actual worked hours.';
                $activity->properties = $checkin_data;
                $activity->save();

                Cache::forget('checkin'.$user->id);
            }
        }

        $this->info('Notifications sent for missing checkouts.');
        return 0;
    }
}
