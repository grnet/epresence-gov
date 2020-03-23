<?php

namespace App\Console\Commands\TestRateLimit;

use App\Conference;
use App\Jobs\Conferences\TrackClientParticipantAddress;
use Asikamiotis\ZoomApiWrapper\JiraClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test_rate_limits:get_participants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        for($i=0;$i<=20;$i++){
            Log::info("Testing ".$i."request:");
            TrackClientParticipantAddress::dispatch("#meeting-id","conference")->onQueue("low");
          //  $this->info(json_encode($get_participants_response));
        }
    }
}
