<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class H323IpRetrieved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $user_id,$conference_id,$ip_address;

    public function __construct($user_id,$conference_id,$ip_address)
    {
        $this->conference_id = $conference_id;
        $this->user_id = $user_id;
        $this->ip_address = $ip_address;

    }

    public function broadcastAs()
    {
        return 'h323-address-retrieved';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('conference-user-' . $this->user_id);
    }
}
