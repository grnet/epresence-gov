<?php

namespace App\Jobs\Conferences;

use App\Conference;
use App\DemoRoomCdr;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TrackClientParticipantAddress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $zoom_meeting_id,$type;

    public function __construct($zoom_meeting_id,$type)
    {
        $this->zoom_meeting_id = $zoom_meeting_id;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {

        Redis::throttle('zoom_metrics')->allow(1)->every(1)->then(function () {

            $zoom_client = new ZoomClient();
            $get_participants_response = $zoom_client->get_participants($this->zoom_meeting_id);

            if ($get_participants_response !== false && intval($get_participants_response->total_records) > 0) {

                //Normal conference

                if($this->type == "conference"){

                    $conference = Conference::where('zoom_meeting_id',$this->zoom_meeting_id)->first();

                    foreach($get_participants_response->participants as $participant) {

                        $user_name = $participant->user_name;
                        $exploded_user_name = explode("|", $user_name);

                        if (strpos($user_name, "|") !== false && count($exploded_user_name) == 2 && is_numeric($exploded_user_name[1])) {

                            $user_id = $exploded_user_name[1];

                            DB::table('conference_user')->where('conference_id', $conference->id)
                                ->where('user_id', $user_id)
                                ->update(['address' => $participant->ip_address]);
                        }

                    }

                //Demo room

                }else{

                    $redis = Redis::connection();

                    $demo_room_key = 'demo_room_active';
                    $current_demo_room_zoom_id = $redis->get($demo_room_key);

                    if($current_demo_room_zoom_id == $this->zoom_meeting_id){

                        foreach($get_participants_response->participants as $participant) {
                            if(isset($participant->id)){

                                $user_name = $participant->user_name;
                                $exploded_user_name = explode("|", $user_name);

                                //Determine if this is a valid user

                                if (strpos($user_name, "|") !== false && count($exploded_user_name) == 2 && is_numeric($exploded_user_name[1])) {
                                    $user_id = $exploded_user_name[1];

                                    DemoRoomCdr::where('zoom_meeting_id',$current_demo_room_zoom_id)->whereNull('leave_time')->where('user_id',$user_id)
                                        ->update(['address'=>$participant->ip_address,'device'=>$participant->device]);

                                }
                            }
                        }
                    }
                }

            } else {

                //            throw new EmptyParticipantsTableException('Job failed because participant list was empty!');

                Log::error("Failed to fetch participant list or list was empty!");
            }

        }, function () {

            // Could not obtain lock...

            Log::error("Failed track client participant address. Reason: zoom api rate limiting");

            $this->release(1);
        });

    }
}
