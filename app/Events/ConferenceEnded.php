<?php

namespace App\Events;

use App\Conference;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ConferenceEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $conference_id;
    public $reason;
    public $participants_ids;
    public $admins_ids;
    public $type;

    //Values active/future

    public function __construct($conference_id,$reason,$type = null,$participants_ids = null,$admins_ids = null)
    {

        $this->reason = $reason;
        $this->type = $type;
        $this->conference_id = $conference_id;
        $this->participants_ids = $participants_ids;
        $this->admins_ids = $admins_ids;

    }

    public function broadcastAs()
    {
        return 'conference-ended';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {


        $participant_ids = collect($this->participants_ids);

        $participant_channels = $participant_ids->map(function($value){
            return new PrivateChannel('conference-user-'.$value);
        })->toArray();

        $admins_ids = collect($this->admins_ids);

        $admin_channels = $admins_ids->map(function($value){
            return new PrivateChannel('conference-user-'.$value);
        })->toArray();


        $all_channels = collect(array_merge($participant_channels,$admin_channels))->unique()->toArray();

        $manage_channel= new PrivateChannel( 'manage-conference-'.$this->conference_id);

        $all_channels[] = $manage_channel;


        return $all_channels;
    }
}
