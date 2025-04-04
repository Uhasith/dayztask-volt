<?php

namespace App\Models;

use Carbon\Carbon;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\Event as CalendarEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Event extends Model 
{
    protected $guarded = ['id'];

    // function toEvent(): array|CalendarEvent
    // {
    //     $title = $this->description;
    //     if(!empty($this->user_id)){
    //         $title = $this->user?->name . ' - ' .  $this->description;
    //     }
    //     $color = str_contains(strtolower($this->description), 'mercantile') ? "#ff5959" : "#e8bc82";
    //     if(!$this->is_approved){
    //         $color = "#cccccc";
    //         $title .= ' (' . __('Pending') . ')';
    //     }
    //     if($this->is_approved && !empty($this->user_id)){
    //         $color = "#f28650";
    //     }

    //     $eventEnd = !empty($this->end) ? Carbon::parse($this->end) : ($this->is_full_day ? Carbon::parse($this->start)->endOfDay()  : Carbon::parse($this->start)->midDay());
    //     if(!empty($this->user_id) && $this->user && $this->user->timezone !== auth()->user()->timezone){
    //         $start = Carbon::parse($this->start)->timezone($this->user->timezone)->setTimezone(auth()->user()->timezone)->startOfDay();
    //         $eventEnd = $eventEnd->timezone($this->user->timezone)->setTimezone(auth()->user()->timezone);
    //     }else{
    //         $start = Carbon::parse($this->start)->startOfDay();
    //     }
    //     $event = CalendarEvent::make($this)
    //         ->title($title)
    //         ->start($start)
    //         ->end($eventEnd)->backgroundColor($color)->allDay($this->is_full_day ?? false)->action('edit');
    //     return $event;
    // }

    function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }
}
