<?php

namespace App\Models;

use Carbon\Carbon;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\Event as CalendarEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Event extends Model implements Eventable
{
    protected $guarded = ['id'];

    function toEvent(): array|CalendarEvent
    {
        $title = $this->user?->name . ' - ' .  $this->description;
        $color = str_contains(strtolower($this->description), 'mercantile') ? "#ff5959" : "#e8bc82";
        if(!$this->is_approved){
            $color = "#cccccc";
            $title .= ' (' . __('Pending') . ')';
        }
        if($this->is_approved && !empty($this->user_id)){
            $color = "#f28650";
        }

        $event = CalendarEvent::make($this)
            ->title($title)
            ->start($this->start)
            ->end($this->end ?? $this->start)->backgroundColor($color)->allDay($this->is_full_day ?? false)->action('edit');
        return $event;
    }

    function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }
}
