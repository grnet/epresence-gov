<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ParticipantAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $conference_id;
    public $user_id;


    //If conference is active or future possible parameter value: future/active

    public $type;



    public function __construct($conference_id,$user_id,$type)
    {
        $this->conference_id = $conference_id;
        $this->user_id = $user_id;
        $this->type = $type;
    }

    public function broadcastAs()
    {
        return 'participant-added';
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [new PrivateChannel('conference-user-'.$this->user_id),new PrivateChannel('manage-conference-'.$this->conference_id)];
    }
}
