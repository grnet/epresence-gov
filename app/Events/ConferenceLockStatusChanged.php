<?php

namespace App\Events;

use App\Conference;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class ConferenceLockStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $conference_id;
    public $status;

    public function __construct($conference_id,$status)
    {
        $this->status = $status;
        $this->conference_id = $conference_id;
    }

    public function broadcastAs()
    {
        return 'conference-lock-status-changed';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {

        //Send to participants of conference in conferences page & admins in manage conference page
        //Admins are excluded from conferences page since this has no effect on them is they not participants on that conference too

        $conference = Conference::findOrFail($this->conference_id);

        $participant_ids = collect($conference->participants()->pluck("id")->toArray());

        $participant_channels = $participant_ids->map(function($value){
            return new PrivateChannel('conference-user-'.$value);
        })->toArray();

        $all_channels = $participant_channels;

        $manage_channel= new PrivateChannel( 'manage-conference-'.$this->conference_id);

        $all_channels[] = $manage_channel;

        return $all_channels;
    }
}

