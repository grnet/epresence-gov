<?php

namespace App\Http\Controllers;

use App\Cdr;
use App\Conference;
use App\Events\ParticipantJoined;
use App\Events\ParticipantLeft;
use App\Jobs\Conferences\CheckH323IpRetrieval;
use App\Jobs\Conferences\CheckIfH323Joined;
use App\Jobs\Conferences\CheckIfH323Left;
use App\Jobs\Conferences\TrackClientParticipantAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ZoomHooksController extends Controller
{
    public function listen(Request $request)
    {
        $event_object = $request->all();
        $event_type = $event_object['event'];

        Log::info("Zoom web-hook: " . json_encode($event_object));

        if (in_array($event_type, ['meeting.participant_joined', 'meeting.participant_left'])) {

            $zoom_meeting_id = $event_object['payload']['object']['id'];
            $user_name = $event_object['payload']['object']['participant']['user_name'];
            $exploded_user_name = explode("|", $user_name);

            //Determine if this is a user joined from zoom-client or from a H323/SIP device

            if (strpos($user_name, "|") !== false && count($exploded_user_name) == 2 && is_numeric($exploded_user_name[1])) {
                $user_id = $exploded_user_name[1];
                $isH323Sip = false;
            } else {
                $user_id = false;
                $isH323Sip = true;
            }

            $conference = Conference::where('zoom_meeting_id', $zoom_meeting_id)->first();
            $meeting_type = null;
            $current_demo_room_zoom_id = null;


            if(!isset($conference->id)){
                Log::info("Not matched with conference");

                //Check if this zoom meeting id is the demo-room

                $redis = Redis::connection();

                $demo_room_key = 'demo_room_active';
                $current_demo_room_zoom_id = $redis->get($demo_room_key);

                Log::info("Current demo room id: ".$current_demo_room_zoom_id);

                if($current_demo_room_zoom_id == $zoom_meeting_id){
                    $meeting_type = "demo_room";
                }else {

                    $h323_ip_retrieval_room_key = 'h323_ip_retrieval:meeting_id';
                    $current_h323_ip_retrieval_room_key = $redis->get($h323_ip_retrieval_room_key);

                    if ($current_h323_ip_retrieval_room_key == $zoom_meeting_id) {
                        $meeting_type = "h323_ip_retrieval";
                    }

                }

            }else{
                $meeting_type = "conference";
            }

            Log::info("Meeting type resolved: ".$meeting_type);

            if(!is_null($meeting_type)){

                switch ($event_type) {
                    case "meeting.participant_joined":

                        //This is a user using zoom-client

                        if (!$isH323Sip) {

                            //If there is an active conference with this id

                            if ( $meeting_type == "conference") {

                                $participant = DB::table('conference_user')
                                    ->where('conference_id', $conference->id)
                                    ->where('user_id', $user_id)
                                    ->first();

                                //If participant is set

                                if (isset($participant->user_id) && !$participant->in_meeting) {

                                    DB::table('conference_user')
                                        ->where('conference_id', $conference->id)
                                        ->where('user_id', $user_id)
                                        ->update(['in_meeting' => true, 'joined_once' => true]);

                                    Cdr::create(["device" => "Desktop-Mobile", "join_time" => Carbon::now(), "user_id" => $user_id, "conference_id" => $conference->id]);

                                    event(new ParticipantJoined($conference->id, $participant->user_id));
                                    TrackClientParticipantAddress::dispatch($zoom_meeting_id,'conference')->delay(now()->addSeconds(30));
                                }

                            } else {

                                //The event is about the demo room

                                DB::table('demo_room_cdrs')->insert(
                                    [
                                        'join_time' => Carbon::now(),
                                        "user_id" => $user_id,
                                        "zoom_meeting_id" => $current_demo_room_zoom_id
                                    ]);

                                TrackClientParticipantAddress::dispatch($current_demo_room_zoom_id,'demo-room')->delay(now()->addSeconds(30));

                            }

                        } else {

                            //This a h323/sip user

//                            $redis = Redis::connection();
//                            $key = "total_online_h323";
//
//                            $currently_total_online_h323 = $redis->get($key);
//                            $currently_total_online_h323 = empty($currently_total_online_h323) || is_null($currently_total_online_h323) || !is_numeric($currently_total_online_h323) ? 0 : $currently_total_online_h323;
//                            $currently_total_online_h323++;
//                            $redis->set($key,$currently_total_online_h323);

                            if ($meeting_type == "conference"){

                                CheckIfH323Joined::dispatch($conference)->delay(now()->addSeconds(30));

                            }elseif($meeting_type == "h323_ip_retrieval"){

                                $redis = Redis::connection();

                                $meeting_id_key = 'h323_ip_retrieval:meeting_id';
                                $meeting_id = $redis->get($meeting_id_key);

                                $user_id_key = 'h323_ip_retrieval:user_id';
                                $user_id = $redis->get($user_id_key);

                                $conference_id_key = 'h323_ip_retrieval:conference_id';
                                $conference_id = $redis->get($conference_id_key);

                                $address_key = 'h323_ip_retrieval:address';
                                $address = $redis->get($address_key);

                                if(empty($address))
                                CheckH323IpRetrieval::dispatch($user_id,$meeting_id,$conference_id)->delay(now()->addSeconds(10));

                            }


                        }

                        break;

                    case "meeting.participant_left":

                        //This is a user using zoom-client
                        if (!$isH323Sip) {

                            if ($meeting_type == "conference") {

                                $participant = DB::table('conference_user')
                                    ->where('conference_id', $conference->id)
                                    ->where('user_id', $user_id)
                                    ->first();

                                //If participant is set

                                if (isset($participant->user_id) && $participant->in_meeting) {

                                    DB::table('conference_user')
                                        ->where('conference_id', $conference->id)
                                        ->where('user_id', $user_id)
                                        ->update(['in_meeting' => false]);


                                    $cdr = Cdr::where("conference_id", $conference->id)
                                        ->where("user_id", $user_id)
                                        ->whereNull("leave_time")
                                        ->first();

                                    if (isset($cdr->id)) {
                                        $cdr->update(["leave_time" => Carbon::now()]);
                                    }

                                    event(new ParticipantLeft($conference->id, $participant->user_id));
                                }

                            } else{

                                //The event is about the demo room

                                $on_going_cdr = DB::table('demo_room_cdrs')->where("user_id", $user_id)
                                    ->where("zoom_meeting_id", $current_demo_room_zoom_id)
                                    ->whereNull("leave_time")->first();

                                if (isset($on_going_cdr->id)) {
                                    DB::table('demo_room_cdrs')->where("id", $on_going_cdr->id)->update(["leave_time" => Carbon::now()]);
                                }
                            }
                        } else {

                            //This a h323/sip user

//                            $redis = Redis::connection();
//                            $key = "total_online_h323";
//
//                            $currently_total_online_h323 = $redis->get($key);
//                            $currently_total_online_h323--;
//                            $redis->set($key,$currently_total_online_h323);

                            if($meeting_type == "conference"){
                                CheckIfH323Left::dispatch($conference)->delay(now()->addSeconds(5));
                            }

                        }

                        break;
                }
            }
        }
    }
}
