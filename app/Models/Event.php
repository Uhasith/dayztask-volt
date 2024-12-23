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
        $event = CalendarEvent::make($this)
            ->title($title)
            ->start($this->start)
            ->end($this->end ?? $this->start)->backgroundColor(str_contains(strtolower($this->description), 'mercantile') ? "#ff5959" : "#e8bc82")->allDay($this->is_full_day ?? false);
        return $event;
    }

    function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }
}
