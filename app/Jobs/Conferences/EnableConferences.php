<?php

namespace App\Jobs\Conferences;

use App\Events\ConferenceEnabled;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Conference;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class EnableConferences implements ShouldQueue
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

        $start = Conference::timeFromNow(Carbon::now('Europe/Athens'), 0, 'add');
        $conferencesToOpen = Conference::where('room_enabled', 0)->where('start', $start)->get();

        foreach ($conferencesToOpen as $conference) {

            Log::info("Opening conference: ".$conference->id);

            $results = $conference->startConference();

            if($results == true){

                if (!$conference->is_test()){
                    DB::table('service_usage')->where('option', 'total')->increment('total_conferences');
                    $avg_old = DB::table('service_usage')->where('option', 'total')->value('average_participants');
                    DB::table('service_usage')->where('option', 'total')->increment('euro_saved',round($avg_old/2 * config('conferences.euro_saved')));
                }

                event(new ConferenceEnabled($conference));

                Log::info("Opened conference: ".$conference->id);
            } else{
                Log::error("Failed to open conference: ".$conference->id);
            }
        }
    }
}

