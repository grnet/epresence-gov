<?php

namespace App\Console\Commands\Migration;

use App\Conference;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInvitedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:invited_users';

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
        $this->info("Fixing registrants started...");
        $zoom_client = new ZoomClient();
        $conferences = Conference::where("start", ">=", Carbon::now()->addMinutes(5))->get();
        foreach ($conferences as $conference) {
            $this->info("Fixing registrants for conference: " . $conference->id);
            $conference_user_rows = Db::table('conference_user')->where("conference_id", $conference->id)->get();
            foreach ($conference_user_rows as $conference_user_row) {
                $user_relation_row = Db::table("user_helper")->where("final_user_id", $conference_user_row->user_id)->first();
                $user_id_to_check = isset($user_relation_row) && !empty($user_relation_row->new_user_id) ? $user_relation_row->new_user_id : $conference_user_row->user_id;
                $cancel_registrant_parameters = [
                    "action" => "cancel",
                    "registrants" => [
                        [
                            "id" => $conference_user_row->registrant_id,
                            "email" => "user" . $user_id_to_check . "@" . env("APP_ALIAS")
                        ]
                    ]
                ];
                $zoom_client->update_participant_status($cancel_registrant_parameters, $conference->zoom_meeting_id);
                $add_participant_response = $conference->assignParticipant($conference_user_row->user_id);
                $this->info("Assign participant api response:");
                $this->info(json_encode($add_participant_response));
                $join_url = isset($add_participant_response->join_url) ? $add_participant_response->join_url : null;
                $registrant_id = isset($add_participant_response->registrant_id) ? $add_participant_response->registrant_id : null;
                Db::table("conference_user")
                    ->where("user_id", $conference_user_row->user_id)
                    ->where("conference_id", $conference->id)
                    ->update(["registrant_id" => $registrant_id, "join_url" => $join_url]);

                $approve_registrant_parameters = [
                    "action" => "approve",
                    "registrants" => [
                        [
                            "id" => $registrant_id,
                            "email" => "user" . $conference_user_row->user_id . "@" . env("APP_ALIAS")
                        ]
                    ]
                ];

                $zoom_client->update_participant_status($approve_registrant_parameters, $conference->zoom_meeting_id);
            }
        }

        $this->info("Fixing registrants completed!");
    }
}
