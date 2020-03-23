<?php

namespace App\Jobs\Conferences;

use App\Conference;
use App\NamedUser;
use Asikamiotis\ZoomApiWrapper\JiraClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

class EndH323IpRetrievalMeeting implements ShouldQueue
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

        $redis = Redis::connection();
        $key = 'h323_ip_retrieval:meeting_id';

        $zoom_meeting_id =  $redis->get($key);

        if(!empty($zoom_meeting_id)){

            Log::info("Ending h323 ip retrieval meeting with id :".$zoom_meeting_id);

            $zoom_client = new JiraClient();

            //End current meeting

            $parameters = [
                "action" => "end"
            ];

            $zoom_client->update_meeting_status($parameters,$zoom_meeting_id);

            $redis = Redis::connection();
            $redis->set($key, null);

            $user_id_key = 'h323_ip_retrieval:user_id';
            $redis->set($user_id_key, null);

            $conference_id_key = 'h323_ip_retrieval:conference_id';
            $redis->set($conference_id_key, null);

            $address_key = 'h323_ip_retrieval:address';
            $redis->set($address_key, null);

            $h323_time_key = 'h323_ip_retrieval:time';
            $redis->set($h323_time_key, null);

            NamedUser::where('type','h323_ip_detection')->where('latest_used',true)->update(['latest_used'=>false]);

            //Close firewall for all ip addresses

            if(config('firewall.protection')  == "on") {

                $key = new RSA();
                $key->loadKey(file_get_contents(config('firewall.ssh_key')));
                $ssh = new SSH2(config('firewall.host'));

                if (!$ssh->login(config('firewall.username'), $key)) {
                    Log::error("Firewall ssh2 connection: Public Key Authentication Failed!");

                }else{

                    Log::info("Firewall ssh2 connection: Public key auth successful!");

                    $delete_exec_1 = "sudo /sbin/iptables -D FORWARD -p tcp -d  " . config('services.zoom.h323_sensor_ip_address')  . " --dport 1720 -j ACCEPT";
                    $delete_exec_2 = "sudo /sbin/iptables -D FORWARD -p tcp -d " . config('services.zoom.h323_sensor_ip_address')  . "  --dport 5060 -j ACCEPT";

                    Log::info("Executing: ".$delete_exec_1);
                    $response = $ssh->exec($delete_exec_1);

                    if(empty($response)){
                        Log::info("Exec is Successful!");
                    }else{
                        Log::error("Exec error: ".$response);
                    }

                    Log::info("Executing: ".$delete_exec_2);
                    $response = $ssh->exec($delete_exec_2);

                    if(empty($response)){
                        Log::info("Exec is Successful!");
                    }else{
                        Log::error("Exec error: ".$response);
                    }
                }
            }
        }
    }
}
