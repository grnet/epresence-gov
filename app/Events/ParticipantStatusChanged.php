<?php

namespace App\Events;


use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;


class ParticipantStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $conference_id;
    public $status;
    public $user_id;


    public function __construct($conference_id,$status,$user_id)
    {
        $this->user_id = $user_id;
        $this->conference_id = $conference_id;
        $this->status = $status;
    }

    public function broadcastAs()
    {
        return 'participant-status-changed';
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
