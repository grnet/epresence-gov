<?php

use App\NamedUser;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use Illuminate\Database\Seeder;

class zoomNamedUsersSeeder extends Seeder
{
    public function run()
    {
        $zoom_client = new ZoomClient();
        //Delete current named user from zoom
        $named_users = NamedUser::all();
        foreach($named_users as $named_user){
            $parameters = [
                "action"=>"delete"
            ];

            //Delete user from zoom
            $zoom_client->delete_user($parameters,$named_user->zoom_id);

            //Delete user from our db
            $named_user->delete();
        }

        if(config('app.env') === "production" || config('app.env') === "prod"){
            for($i=1; $i<=50; $i++){
                $parameters = [
                    "action"=>"custCreate",
                    "user_info"=>[
                        "email"=>"NamedUser".$i."@zoom.epresence.grnet.gr",
                        "type"=>2,
                        "first_name"=>"NamedUser".$i,
                        "last_name"=>"NamedUser".$i,
                    ]
                ];

                $response = $zoom_client->create_user($parameters);
                if(isset($response->id)){
                    NamedUser::create(["email"=>$parameters['user_info']['email'],"latest_used"=>0,"zoom_id"=>$response->id,"type"=>"conferences"]);
                    $add_user_to_group_params = [
                        "members"=>[
                            [
                                "id"=>$response->id
                            ]
                        ]
                    ];
                    $zoom_client->add_user_to_group($add_user_to_group_params,config('services.zoom.h323_disabled_group_id'));
                }
            }

            $parameters = [
                "action"=>"custCreate",
                "user_info"=>[
                    "email"=>"NamedUserDemoRoom@zoom.epresence.grnet.gr",
                    "type"=>2,
                    "first_name"=>"NamedUserDemoRoom",
                    "last_name"=>"NamedUserDemoRoom",
                ]
            ];

            $response = $zoom_client->create_user($parameters);

            if(isset($response->id)){
                NamedUser::create(["email"=>$parameters['user_info']['email'],"latest_used"=>0,"zoom_id"=>$response->id,"type"=>"demo_room"]);

                $add_user_to_group_params = [
                    "members"=>[
                        [
                            "id"=>$response->id
                        ]
                    ]
                ];

                $zoom_client->add_user_to_group($add_user_to_group_params,config('services.zoom.h323_disabled_group_id'));
            }

            $parameters = [
                "action"=>"custCreate",
                "user_info"=>[
                    "email"=>"NamedUserH323Detection@zoom.epresence.grnet.gr",
                    "type"=>2,
                    "first_name"=>"NamedUserH323",
                    "last_name"=>"NamedUserH323",
                ]
            ];

            $response = $zoom_client->create_user($parameters);

            if(isset($response->id))
                NamedUser::create(["email"=>$parameters['user_info']['email'],"latest_used"=>0,"zoom_id"=>$response->id,"type"=>"h323_ip_detection"]);

        }else{

            for($i=1; $i<=10; $i++){

                $parameters = [
                    "action"=>"custCreate",
                    "user_info"=>[
                        "email"=>"NamedUserDev".$i."@zoom.epresence.grnet.gr",
                        "type"=>2,
                        "first_name"=>"NamedUser0".$i,
                        "last_name"=>"NamedUser0".$i,
                    ]
                ];

                $response = $zoom_client->create_user($parameters);

                if(isset($response->id)){

                    NamedUser::create(["email"=>$parameters['user_info']['email'],"latest_used"=>0,"zoom_id"=>$response->id,"type"=>"conferences"]);


                    $add_user_to_group_params = [
                        "members"=>[
                            [
                                "id"=>$response->id
                            ]
                        ]
                    ];

                    $zoom_client->add_user_to_group($add_user_to_group_params,config('services.zoom.h323_disabled_group_id'));
                }

            }

            $parameters = [
                "action"=>"custCreate",
                "user_info"=>[
                    "email"=>"NamedUserDevDemoRoom@zoom.epresence.grnet.gr",
                    "type"=>2,
                    "first_name"=>"NamedUserDemoRoom",
                    "last_name"=>"NamedUserDemoRoom",
                ]
            ];

            $response = $zoom_client->create_user($parameters);

            if(isset($response->id))
                NamedUser::create(["email"=>$parameters['user_info']['email'],"latest_used"=>0,"zoom_id"=>$response->id,"type"=>"demo_room"]);

            $parameters = [
                "action"=>"custCreate",
                "user_info"=>[
                    "email"=>"NamedUserDevH323Detection@zoom.epresence.grnet.gr",
                    "type"=>2,
                    "first_name"=>"NamedUserH323",
                    "last_name"=>"NamedUserH323",
                ]
            ];

            $response = $zoom_client->create_user($parameters);

            if(isset($response->id)){

                NamedUser::create(["email"=>$parameters['user_info']['email'],"latest_used"=>0,"zoom_id"=>$response->id,"type"=>"h323_ip_detection"]);

                $add_user_to_group_params = [
                    "members"=>[
                        [
                            "id"=>$response->id
                        ]
                    ]
                ];

                $zoom_client->add_user_to_group($add_user_to_group_params,config('services.zoom.h323_disabled_group_id'));
            }


        }
    }
}
