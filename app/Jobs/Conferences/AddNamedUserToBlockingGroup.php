<?php

namespace App\Jobs\Conferences;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AddNamedUserToBlockingGroup implements ShouldQueue
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
        $conference = $this->conference;
        $named_user = $conference->named_user;
        $redis = Redis::connection();
        $key = "last_job_id_".$named_user->id;

        $latestJobId = $redis->get($key);
        $thisJobId = $this->job->getJobId();

        //Check if this is the last job about this named user

        if(empty($latestJobId) || $latestJobId == $thisJobId){

           // Log::info("Adding named user with id: ".$named_user->id." back to the blocking group... job id: ".$thisJobId);

            $conference->disableH323Connections();
            $redis->set($key,null);

        }else{

         //   Log::info("Not Adding named user with id: ".$named_user->id." to the blocking group since its not the last job pushed for this user... job id: ".$thisJobId." latest job id: ".$latestJobId);
        }
    }
}
