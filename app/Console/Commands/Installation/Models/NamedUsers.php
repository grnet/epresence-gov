<?php

namespace App\Console\Commands\Installation\Models;

use App\NamedUser;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NamedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:named_users {number} {offset}';

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
        Schema::disableForeignKeyConstraints();
        Db::table('named_users')->truncate();
        $zoom_client = new ZoomClient();
        $named_users_to_create = $this->argument('number');
        $offset_number = $this->argument('offset');
        $upperLimit = $named_users_to_create+$offset_number;
        for ($i = $offset_number; $i <= $upperLimit; $i++) {
            $this->info("Creating named user: ".$i);
            $parameters = [
                "action" => "custCreate",
                "user_info" => [
                    "email" => "NamedUserGov" . $i . "@zoom.epresence.grnet.gr",
                    "type" => 2,
                    "first_name" => "NamedUser" . $i,
                    "last_name" => "NamedUser" . $i,
                ]
            ];
            $response = $zoom_client->create_user($parameters);
            if (isset($response->id)) {
                NamedUser::create(["email" => $parameters['user_info']['email'], "latest_used" => 0, "zoom_id" => $response->id, "type" => "conferences"]);
                $add_user_to_group_params = [
                    "members" => [
                        [
                            "id" => $response->id
                        ]
                    ]
                ];
                $zoom_client->add_user_to_group($add_user_to_group_params, config('services.zoom.h323_disabled_group_id'));
            }
        }

        $DemoUserParameters = [
            "action" => "custCreate",
            "user_info" => [
                "email" => "NamedUserDemoRoomGov@zoom.epresence.grnet.gr",
                "type" => 2,
                "first_name" => "NamedUserDemoRoomGov",
                "last_name" => "NamedUserDemoRoomGov",
            ]
        ];
        $response = $zoom_client->create_user($DemoUserParameters);
        if (isset($response->id)) {
            NamedUser::create(["email" => $DemoUserParameters['user_info']['email'], "latest_used" => 0, "zoom_id" => $response->id, "type" => "demo_room"]);
            $add_user_to_group_params = [
                "members" => [
                    [
                        "id" => $response->id
                    ]
                ]
            ];
            $zoom_client->add_user_to_group($add_user_to_group_params, config('services.zoom.h323_disabled_group_id'));
        }
        Schema::enableForeignKeyConstraints();
    }
}
