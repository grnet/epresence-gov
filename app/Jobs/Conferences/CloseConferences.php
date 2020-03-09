<?php

namespace App\Jobs\Conferences;

use App\Events\ConferenceEnded;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Conference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CloseConferences implements ShouldQueue
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
        $end = Conference::timeFromNow(Carbon::now('Europe/Athens'), 0, 'add');
        $conferencesToClose = Conference::where('room_enabled', 1)->where('end', '<=', $end)->get();

        foreach ($conferencesToClose as $conference) {

            Log::info("Closing conference: ".$conference->id);

            $results = $conference->endConference();

            if($results == true) {

                $participants_ids = $conference->participants()->pluck("id")->toArray();
                $admin_ids = $conference->getAdminsIds();

                event(new ConferenceEnded($conference->id, "expired", 'active', $participants_ids, $admin_ids));

                Log::info("Closed conference: ".$conference->id);
            }
            else{

                Log::error("Failed to close conference: ".$conference->id);
            }

        }
    }
}
