<?php

namespace App\Console\Commands;

use Asikamiotis\ZoomApiWrapper\ZoomClient;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteAllZoomUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:all_zoom_users';

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
        $zoom_client = new ZoomClient();
        $zoom_users = $zoom_client->get_users(['page_size'=>300]);
        $parameters = [
            "action"=>"delete"
        ];

        foreach($zoom_users->users as $zoom_user){
            if(strpos($zoom_user->email,"zoom.epresence.grnet.gr") !== false){

                //Delete user from zoom
                $zoom_client->delete_user($parameters,$zoom_user->id);
            }
        }
    }
}
