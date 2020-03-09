<?php

namespace App\Jobs\Conferences;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Conference;
use Carbon\Carbon;

class RemindUsersForClosingConferences implements ShouldQueue
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
        $conferencesUsersToNotifyClose = Conference::where('room_enabled', 1)->where('end', $fifteenMinsFromNow)->get();

        foreach ($conferencesUsersToNotifyClose as $conference) {
            $conference->conferencesUsersToNotifyClose();
        }
    }
}
