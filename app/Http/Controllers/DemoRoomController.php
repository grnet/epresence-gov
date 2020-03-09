<?php

namespace App\Http\Controllers;

use App\DemoRoomCdr;
use App\ExtraEmail;
use App\NamedUser;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use SoapClient;
use App\Conference;

class DemoRoomController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $authenticated_user = Auth::user();

        $data['authenticated_user'] = $authenticated_user;
        $data['is_mobile'] = $authenticated_user->is_mobile();

        return view('demo-room.index', $data);
    }


    public function join_demo_room()
    {

        //Check if we already have a join url for this user

        $user = Auth::user();
        $join_information = DB::table('demo_room_join_urls')->where('user_id', $user->id)->where('active', true)->first();
        $join_url = null;

        if (isset($join_information->id)) {
            $join_url = $join_information->join_url;
        }else{

            //If not create personalised url for the demo room for this user

            $zoom_client = new ZoomClient();

            $redis = Redis::connection();
            $key = 'demo_room_active';
            $current_demo_room_zoom_id = $redis->get($key);

            $add_registrant_parameters = [
                "email" => "user".$user->id."@".env("APP_ALIAS"),
                "first_name" => $user->firstname,
                "last_name" => $user->lastname."|".$user->id,
            ];

            $add_participant_response = $zoom_client->add_participant($add_registrant_parameters,$current_demo_room_zoom_id);

            if(isset($add_participant_response->registrant_id)){
                $approve_registrant_parameters = [
                    "action" => "approve",
                    "registrants"=>[
                        [
                            "id"=>$add_participant_response->registrant_id,
                            "email"=>"user".$user->id."@".env("APP_ALIAS")
                        ]
                    ]
                ];

                $update_registrant_status_response =  $zoom_client->update_participant_status($approve_registrant_parameters,$current_demo_room_zoom_id);
                $now = Carbon::now();
                $join_url = isset($add_participant_response->join_url) ? $add_participant_response->join_url : null;
                $registrant_id = isset($add_participant_response->registrant_id) ? $add_participant_response->registrant_id : null;

                //Work around for empty join_url
                if(empty($join_url)){
                    $zoom_client = new ZoomClient();
                    $registrants_response = $zoom_client->get_registrants($current_demo_room_zoom_id);
                    $join_url =  $user->match_with_registrant($registrants_response,$registrant_id);
                }

                DB::table('demo_room_join_urls')->insert(['user_id'=>$user->id,"join_url"=>$join_url,"registrant_id"=>$registrant_id,"created_at"=>$now,"updated_at"=>$now]);

            }else{
                abort(404);
            }

        }

        return redirect($join_url);
    }


    //This end last demo room meeting and creates a new one

    public static function recreate_demo_room()
    {
        $redis = Redis::connection();
        $key = 'demo_room_active';
        $current_demo_room_zoom_id = $redis->get($key);
        $zoom_client = new ZoomClient();

        DB::table('demo_room_join_urls')->where('active',true)->update(["active"=>false]);
        if (!empty($current_demo_room_zoom_id)) {

            //Disable join before host

//            $parameters = [
//                "settings" => [
//                    "join_before_host" => "false",
//                ]
//            ];
//
//            $zoom_client->update_meeting($parameters,$current_demo_room_zoom_id);

            //End current meeting

            $parameters = [
                "action" => "end"
            ];

          $end_demo_room_response =  $zoom_client->update_meeting_status($parameters,$current_demo_room_zoom_id);
          Log::info("end demo room response: ".json_encode($end_demo_room_response));

        }

        //Create new meeting

        $demo_room_named_user_zoom_id = NamedUser::where('type','demo_room')->first()->zoom_id;
        $start_time = Carbon::now()->format("Y-m-d\TH:i:s");

        $parameters = [
            "topic" => "demo-room",
            "type" => "2",
            "start_time" => $start_time,
            "duration" => 30,
            "timezone" => "Europe/Athens",
            "password" => "",
            "agenda" => "",
            "settings" => [
                "host_video" => "true",
                "participant_video" => "true",
                "cn_meeting" => "false",
                "in_meeting" => "false",
                "join_before_host" => "true",
                "mute_upon_entry" => "false",
                "watermark" => "false",
                "use_pmi" => "false",
                //0 -> participants are required to fill a form in order to join them meeting even if they are using their personal link
                "approval_type" => "1",
                "registration_type" => "2",
                "audio" => "voip",
                "auto_recording" => "none",
                "enforce_login" => "false",
                "enforce_login_domains" => "",
                "alternative_hosts" => ""
            ]
        ];

        $response = $zoom_client->create_meeting($parameters,$demo_room_named_user_zoom_id);

        //Update settings of new meeting

//        $update_parameters = $parameters;
//        $update_parameters['settings']['registrants_confirmation_email'] = "false";
//
//        $response = $zoom_client->update_meeting($parameters,$zoom_meeting_id);

        if(isset($response->id)){
            $zoom_meeting_id = $response->id;
            $redis->set($key,$zoom_meeting_id);
        }
    }

    public function manage()
    {
        if ( !Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        } else {

            $redis = Redis::connection();
            $key = 'demo_room_active';
            $current_demo_room_zoom_id = $redis->get($key);

            // Get active participants for conference from CDRs

            $data['participants'] = DemoRoomCdr::with('user')->where('zoom_meeting_id',$current_demo_room_zoom_id)->whereNull('leave_time')->get();
            $data['total_users_in_progress'] = count($data['participants']);

            return view('demo-room.manage',
                [
                    'participant_data'=>$data
                ]
            );
        }
    }


    public function disconnectAll(){

        $response['status'] = 'error';

        if ( !Auth::user()->hasRole('SuperAdmin')) {

            //Do nothing

        } else {

            self::recreate_demo_room();
            $response['status'] = 'success';
        }

        return response()->json($response);
    }

}
