<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
use ICal\ICal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-holidays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command sync the merchant holidays to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file_url = storage_path('ics/2024.ics');
        $ical = new ICal($file_url, array('httpUserAgent' => 'A Different User Agent'));

        foreach($ical->events() as $event){
            // Log::info(print_r($event));
            // Log::info(Carbon::parse($event->dtstart_tz)->timestamp);


            $holidayData = array(
                'start' => Carbon::parse($event->dtstart_tz)->format('Y-m-d H:i:s'),
                'end' => Carbon::parse($event->dtstart_tz)->format('Y-m-d H:i:s'),
                'description' => $event->description,
                'is_full_day' => true,
                'is_approved' => true,
            );

            Event::updateOrCreate([
                'start' => Carbon::parse($event->dtstart_tz)->format('Y-m-d H:i:s'),
                'end' => Carbon::parse($event->dtstart_tz)->format('Y-m-d H:i:s'),
                'description' => $event->summary,
            ], $holidayData);

        }

    }
}
