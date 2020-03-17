<?php

namespace App\Console\Commands;

use App\NamedUser;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use Illuminate\Console\Command;

class UpdateNamedUsersSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:named_user_settings';

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
        $named_users = NamedUser::all();

        foreach($named_users as $named_user){

            $zoom_client = new ZoomClient();
            $parameters = [
                "in_meeting"=>[
                    "e2e_encryption"=>false
                ]
            ];

            $zoom_client->update_user_settings($parameters,$named_user->zoom_id);
        }
    }
}
