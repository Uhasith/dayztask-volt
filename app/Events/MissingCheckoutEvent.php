<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MissingCheckoutEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $activity;

    /**
     * Create a new event instance.
     */
    public function __construct($user, $activity)
    {
        $this->user = $user;
        $this->activity = $activity;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return ['missing-checkout-channel'];
        // return [
        //     new PrivateChannel('channel-name'),
        // ];
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->user->id,
            'checkin' => $this->activity->properties['checkin'],
            'location' => $this->activity->properties['location'],
        ];
    }
}
