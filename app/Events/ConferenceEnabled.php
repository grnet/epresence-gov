<?php

namespace App\Events;


use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ConferenceEnabled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $conference;

    public function __construct($conference)
    {
     $this->conference = $conference;
    }

    public function broadcastAs()
    {
        return 'conference-enabled';
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


        $participant_ids = collect($this->conference->participants()->pluck('id')->toArray());

        $participant_channels = $participant_ids->map(function($value){
            return new PrivateChannel('conference-user-'.$value);
        })->toArray();

        $all_channels = collect(array_merge($participant_channels,$admin_channels))->unique()->toArray();

        return $all_channels;
    }
}
