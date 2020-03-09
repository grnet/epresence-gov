<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ParticipantDeviceChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */


    public $conference,$conference_id,$device,$user_id,$type;

    public function __construct($conference,$device,$user_id)
    {
        $this->user_id = $user_id;
        $this->conference = $conference;
        $this->conference_id = $conference->id;
        $this->device = $device;
        $this->type = $conference->room_enabled ? 'active' : 'future';
    }

    public function broadcastAs()
    {
        return 'participant-device-changed';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $conference_id = $this->conference->id;

        return [new PrivateChannel('conference-user-'.$this->user_id),new PrivateChannel('manage-conference-'.$conference_id)];
    }
}
