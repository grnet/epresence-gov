<?php

namespace App\Jobs\Conferences;


use App\Cdr;
use App\Events\ParticipantLeft;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CheckIfH323Left implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $conference;

    public function __construct($conference)
    {
        $this->conference = $conference;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle('zoom_metrics')->allow(1)->every(1)->then(function () {

            $conference = $this->conference;
            $zoom_meeting_id = $conference->zoom_meeting_id;

            $zoom_client = new ZoomClient();
            $get_participants_response = $zoom_client->get_participants($zoom_meeting_id);

//            Log::info("Check if H323 left:");
//            Log::info(json_encode($get_participants_response->participants,JSON_PRETTY_PRINT));


            $online_h323_participants = $conference->participants()->where('conference_user.device','H323')->where('conference_user.in_meeting',true)->get();

            if ($get_participants_response !== false && intval($get_participants_response->total_records) > 0) {

                foreach($online_h323_participants as $h323_participant){

                    $found = false;

                    foreach ($get_participants_response->participants as $participant) {
                        if($h323_participant->pivot->identifier == $participant->ip_address && $participant->device == "H.323/SIP" && !isset($participant->leave_time)){
                            $found = true;
                        }
                    }

                    if(!$found){

                        DB::table('conference_user')
                            ->where('conference_id', $conference->id)
                            ->where('user_id', $h323_participant->id)
                            ->update(['in_meeting' => false]);


                        $cdr = Cdr::where("conference_id", $conference->id)
                            ->where("user_id", $h323_participant->id)
                            ->whereNotNull("join_time")
                            ->whereNull("leave_time")
                            ->first();

                        if (isset($cdr->id)) {
                            DB::table("cdrs")->where("id", $cdr->id)->update(["leave_time" => Carbon::now()]);
                        }

                        event(new ParticipantLeft($conference->id, $h323_participant->id));
                    }
                }

            } else {

                foreach($online_h323_participants as $h323_participant){

                    DB::table('conference_user')
                        ->where('conference_id', $conference->id)
                        ->where('user_id', $h323_participant->id)
                        ->update(['in_meeting' => false]);


                    $cdr = Cdr::where("conference_id", $conference->id)
                        ->where("user_id", $h323_participant->id)
                        ->whereNotNull("join_time")
                        ->whereNull("leave_time")
                        ->first();

                    if (isset($cdr->id)) {
                        $cdr->update(["leave_time" => Carbon::now()]);
                    }

                    event(new ParticipantLeft($conference->id, $h323_participant->id));
                }
            }

        }, function () {

            // Could not obtain lock...

            Log::error("Failed to check if h323/sip device user joined the conference. Reason: zoom api rate limiting");

            $this->release(1);
        });


    }
}
