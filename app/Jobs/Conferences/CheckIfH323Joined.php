<?php

namespace App\Jobs\Conferences;

use App\Cdr;
use App\Events\ParticipantJoined;
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

class CheckIfH323Joined implements ShouldQueue
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

//            Log::info("Check if H323 joined:");
//            Log::info(json_encode($get_participants_response->participants,JSON_PRETTY_PRINT));

            if ($get_participants_response !== false && intval($get_participants_response->total_records) > 0) {

                    $h323_participants = $conference->participants()->where('conference_user.device','H323')->get();

                    foreach($h323_participants as $h323_participant){

                        foreach ($get_participants_response->participants as $participant) {

                            if($participant->device == "H.323/SIP" && ($h323_participant->pivot->identifier != null && $h323_participant->pivot->identifier == $participant->ip_address)){

                                //Check if user is in meeting in every loop to avoid creating a false cdr record

                                $participant_row = DB::table('conference_user')->where('conference_id', $conference->id)->where('user_id', $h323_participant->id)->first();

                                if(isset($participant_row) && !$participant_row->in_meeting){

                                    DB::table('conference_user')
                                        ->where('conference_id', $conference->id)
                                        ->where('user_id', $h323_participant->id)
                                        ->update(['in_meeting' => true, 'joined_once' => true,'address'=>$participant->ip_address]);

                                    Cdr::create(["device" => "H323", "join_time" => Carbon::now(), "user_id" => $h323_participant->id, "conference_id" => $conference->id]);

                                    event(new ParticipantJoined($conference->id, $h323_participant->id));

                                }
                            }
                        }
                    }

            } else {

                // throw new EmptyParticipantsTableException('Job failed because participant list was empty!');

                Log::error("Failed to fetch participant list or list was empty!");
            }

        }, function () {

            // Could not obtain lock...

            Log::error("Failed to check if h323/sip device user joined the conference. Reason: zoom api rate limiting");

            $this->release(1);
        });

    }
}
