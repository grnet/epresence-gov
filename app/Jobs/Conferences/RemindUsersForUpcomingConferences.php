<?php

namespace App\Jobs\Conferences;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Conference;
use Carbon\Carbon;

class RemindUsersForUpcomingConferences implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fifteenMinsFromNow = Conference::timeFromNow(Carbon::now('Europe/Athens'), 15, 'add');
        $conferencesUsersToRemind = Conference::where('room_enabled', 0)->where('start', $fifteenMinsFromNow)->get();

        foreach ($conferencesUsersToRemind as $conference) {
            $conference->sendStartConferenceReminders();
        }
    }
}
