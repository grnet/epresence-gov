<?php

namespace App\Jobs\Conferences;

use App\Events\H323IpNotRetrieved;
use App\Events\H323IpRetrieved;
use Asikamiotis\ZoomApiWrapper\JiraClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

class CheckH323IpRetrieval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $user_id,$conference_id,$meeting_id;

    public function __construct($user_id,$meeting_id,$conference_id)
    {
        $this->user_id = $user_id;
        $this->meeting_id = $meeting_id;
        $this->conference_id = $conference_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Redis::throttle('zoom_metrics')->allow(1)->every(1)->then(function () {

            $zoom_client = new JiraClient();

            $zoom_meeting_id = $this->meeting_id;
            $get_participants_response = $zoom_client->get_participants($zoom_meeting_id);

            $redis = Redis::connection();

            $user_id_key = 'h323_ip_retrieval:user_id';
            $user_id = $redis->get($user_id_key);

           // $redis->set($user_id_key, null);

            $conference_id_key = 'h323_ip_retrieval:conference_id';
            $conference_id = $redis->get($conference_id_key);

            //$redis->set($conference_id_key, null);

            if ($get_participants_response !== false && intval($get_participants_response->total_records) == 1) {

                $ip_address = $get_participants_response->participants[0]->ip_address;

                $address_key = 'h323_ip_retrieval:address';
                $redis->set($address_key, $ip_address);

                //Close firewall for all ip addresses

                if(config('firewall.protection') == "on") {

                    $key = new RSA();
                    $key->loadKey(file_get_contents(config('firewall.ssh_key')));
                    $ssh = new SSH2(config('firewall.host'));

                    if (!$ssh->login(config('firewall.username'), $key)) {
                        Log::error("Firewall ssh2 connection: Public Key Authentication Failed!");

                    }else{

                        Log::info("Firewall ssh2 connection: Public key auth successful!");

                        $delete_exec_1 = "sudo /sbin/iptables -D FORWARD -p tcp -d  " . config('services.zoom.h323_sensor_ip_address') . " --dport 1720 -j ACCEPT";
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

                event(new H323IpRetrieved($user_id, $conference_id,$ip_address));

            } else {

                // throw new EmptyParticipantsTableException('Job failed because participant list was empty!');

                event(new H323IpNotRetrieved($user_id, $conference_id));

                Log::error("Failed to fetch participant list or list was empty!");
            }

        }, function () {

            // Could not obtain lock...

            Log::error("Failed to check if h323/sip ip retrieval. Reason: zoom api rate limiting");

            $this->release(1);
        });
    }
}
