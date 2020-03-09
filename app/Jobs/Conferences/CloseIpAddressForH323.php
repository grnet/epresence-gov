<?php

namespace App\Jobs\Conferences;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CloseIpAddressForH323 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $conference,$ip;

    public function __construct($conference,$ip)
    {
      $this->conference = $conference;
      $this->ip = $ip;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $conference = $this->conference;

        $ip_with_dashes = str_replace(".","-",$this->ip);

        $redis = Redis::connection();
        $key = "last_job_id_".$ip_with_dashes;

        $latestJobId = $redis->get($key);

        Log::info("Latest job id: ".$latestJobId);

        $thisJobId = $this->job->getJobId();

        Log::info("thisJobId: ".$latestJobId);

        //Check if this is the last job about this named user

        if(empty($latestJobId) || $latestJobId == $thisJobId){

            Log::info("Closed IP ADDRESS");

            $conference->CloseIpAddressForH323($this->ip);

            $redis->set($key,null);

        }else{

            Log::info("DID NOT Closed IP ADDRESS");
        }
    }
}
