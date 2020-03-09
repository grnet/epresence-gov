<?php

namespace App\Listeners;

use App\Events\MobileConnectConference;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Conference;

class LockConferenceRoom
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MobileConnectConference  $event
     * @return void
     */
    public function handle(MobileConnectConference $event)
    {
        // Lock room after 60 secs
		sleep(60);
		
		$conference = Conference::findOrFail($event->id);
		
		$conference->lock_room();
		
    }
}
