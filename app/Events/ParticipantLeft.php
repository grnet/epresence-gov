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

class ParticipantLeft implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $conference_id,$user_id;

    public function __construct($conference_id,$user_id)
    {
        $this->conference_id = $conference_id;
        $this->user_id = $user_id;
    }

    public function broadcastAs()
    {
        return 'participant-left-conference';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $conference = Conference::findOrFail($this->conference_id);
        $participant_ids = collect($conference->participants()->pluck("id")->toArray());

        $participant_channels = $participant_ids->map(function($value){
            return new PrivateChannel('conference-user-'.$value);
        })->toArray();

        $manage_channel = new PrivateChannel('manage-conference-' . $this->conference_id);
        $participant_channels[] = $manage_channel;

        return $participant_channels;
    }
}
