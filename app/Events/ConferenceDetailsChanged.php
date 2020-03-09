<?php

namespace App\Events;

use App\Conference;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ConferenceDetailsChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $conference;
    public $fields_updated;
    public $type;

    public function __construct($conference,$fields_updated)
    {
        $this->conference = $conference;
        $this->fields_updated = $fields_updated;
        $this->type = $conference->room_enabled ? 'active' : 'future';
    }

    public function broadcastAs()
    {
        return 'conference-details-changed';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //Send to participants of conference in conferences page & admins in manage conference page

        $conference = $this->conference;

        $participant_ids = collect($conference->participants()->pluck("id")->toArray());

        $participant_channels = $participant_ids->map(function($value){
            return new PrivateChannel('conference-user-'.$value);
        })->toArray();

        $admins_ids = collect($this->conference->getAdminsIds());

        $admin_channels = $admins_ids->map(function($value){
            return new PrivateChannel('conference-user-'.$value);
        })->toArray();


        $all_channels = collect(array_merge($participant_channels,$admin_channels))->unique()->toArray();

        $manage_channel= new PrivateChannel( 'manage-conference-'.$conference->id);

        $all_channels[] = $manage_channel;


        return $all_channels;

    }
}
