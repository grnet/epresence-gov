<?php

namespace App\Events;


use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ConferenceCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $conference;


    //If conference is active or future possible parameter value: future/active

    public $type;


    public function __construct($conference,$type)
    {
        $this->conference = $conference;
        $this->type = $type;
    }

    public function broadcastAs()
    {
        return 'conference-created';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $admins_ids = collect($this->conference->getAdminsIds());

        $admin_channels = $admins_ids->map(function($value){
            return new PrivateChannel('conference-user-'.$value);
        })->toArray();

        return $admin_channels;
    }
}
