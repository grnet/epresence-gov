<?php

namespace App\Listeners;

use App\Events\ParticipantStatusChanged;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class ParticipantStatusChangedListener
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
     * @param  ParticipantStatusChanged  $event
     * @return void
     */
    public function handle(ParticipantStatusChanged $event)
    {


    }
}
