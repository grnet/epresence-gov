<?php

namespace App\Console\Commands\Migration;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateConferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:conferences';

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
        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('conferences')
        ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        DB::connection('helper')
            ->table('conference_helper')
            ->truncate();

        $old_conferences = DB::connection('vidyo')
            ->table('conferences')
            ->where('end','<','2019-09-30 14:30:00')
            ->get();

        Log::info("Checking conferences from old (vidyo) platform...");
        Log::info("\n");

        $conference_owner_transformations = [];

        foreach ($old_conferences as $conference) {
            $conference_id = $conference->id;
            unset($conference->id);
            unset($conference->max_users);
            unset($conference->max_h323);
            unset($conference->max_vidyo_room);
            unset($conference->vRoomID);
            unset($conference->room_url);
            unset($conference->extension);
            unset($conference->moderator_pin);
            unset($conference->moderator_url);
            unset($conference->users_no);
            unset($conference->users_h323);
            unset($conference->users_vidyo_room);

            $owner = DB::connection('vidyo')
                ->table('users')
                ->where("id", $conference->user_id)->first();

            if (isset($owner->id)) {
                if ($owner->deleted === 1) {
                    $new_owner = DB::connection('result_epresence')->table('users')
                        ->join('role_user', 'users.id', '=', 'role_user.user_id')
                        ->join('institution_user', 'users.id', '=', 'institution_user.user_id')
                        ->where('institution_user.institution_id', $conference->institution_id)
                        ->where('role_user.role_id', 2)
                        ->select(["users.id"])
                        ->first();

                    if (isset($new_owner->id)) {
                        $conference->user_id = $new_owner->id;
                        //Check if user id exists
                        if (DB::connection('result_epresence')->table('users')->where("id", $conference->user_id)->exists() && DB::connection('result_epresence')->table('departments')->where("id", $conference->department_id)->exists()) {
                            $final_conference_id = DB::connection('result_epresence')->table('conferences')->insertGetId((array)$conference);
                            DB::connection('helper')->table('conference_helper')->insert(["old_conference_id" => $conference_id, "new_conference_id" => null, "final_conference_id" => $final_conference_id]);
                            $conference_owner_transformations[$new_owner->id][] = $final_conference_id;
                        } else {
                            Log::info("^^Tried to insert a conference with wrong user id or department id!^^");
                            Log::info("Conference id: " . $conference_id);
                            Log::info("User id: " . $conference->user_id);
                            Log::info("Department id: " . $conference->department_id);
                        }
                    } else {
//                        Log::info("Owner has a deleted account... attaching the first institution admin of the institutions as owner!");
//                        Log::info(json_encode($owner));
//                        Log::info(json_encode($conference));
//                        Log::info("||Haven't found a suitable institution administrator!||");
//                        Log::info("Institution id:".$conference->institution_id);
                    }
                } else {
                    $final_user_relation = DB::connection('helper')->table('user_helper')->where("old_user_id", $owner->id)->first();
                    if (isset($final_user_relation->id)) {
                        $conference->user_id = $final_user_relation->final_user_id;
                        if (DB::connection('result_epresence')->table('users')->where("id", $conference->user_id)->exists() && DB::connection('result_epresence')->table('departments')->where("id", $conference->department_id)->exists()) {
                            $final_conference_id = DB::connection('result_epresence')->table('conferences')->insertGetId((array)$conference);
                            DB::connection('helper')->table('conference_helper')->insert(["old_conference_id" => $conference_id, "new_conference_id" => null, "final_conference_id" => $final_conference_id]);
                        } else {
                            Log::info("^^Tried to insert a conference with wrong user id or department id!^^");
                            Log::info("Conference id: " . $conference_id);
                            Log::info("User id: " . $conference->user_id);
                            Log::info("Department id: " . $conference->department_id);
                        }
                    } else {
//                        Log::info("Haven't found a relation for the conference owner!");
//                        Log::info(json_encode($owner));
//                        Log::info(json_encode($conference));
//                        Log::info("**Haven't found a suitable institution administrator!**");
//                        Log::info("Institution id:".$conference->institution_id);

                    }
                }
            } else {
                Log::info("Owner wasn't found!");
            }
        }

        $new_conferences = DB::connection('zoom')
            ->table('conferences')
            ->get();

        Log::info("Checking conferences from new (zoom) platform...");
        Log::info("\n");

        foreach ($new_conferences as $conference) {
            $conference_id = $conference->id;
            unset($conference->id);

            $owner = DB::connection('zoom')
                ->table('users')
                ->where("id", $conference->user_id)->first();

            if (isset($owner->id)) {
                if ($owner->deleted === 1) {
                    $new_owner = DB::connection('result_epresence')->table('users')
                        ->join('role_user', 'users.id', '=', 'role_user.user_id')
                        ->join('institution_user', 'users.id', '=', 'institution_user.user_id')
                        ->where('institution_user.institution_id', $conference->institution_id)
                        ->where('role_user.role_id', 2)
                        ->select(["users.id"])
                        ->first();

                    if (isset($new_owner->id)) {
                        $conference->user_id = $new_owner->id;
                        //Check if user id exists
                        if (DB::connection('result_epresence')->table('users')->where("id", $conference->user_id)->exists()
                            && DB::connection('result_epresence')->table('departments')->where("id", $conference->department_id)->exists()
                        ) {
                            $final_conference_id = DB::connection('result_epresence')->table('conferences')->insertGetId((array)$conference);
                            DB::connection('helper')->table('conference_helper')->insert(["old_conference_id" => null, "new_conference_id" => $conference_id, "final_conference_id" => $final_conference_id]);
                            $conference_owner_transformations[$new_owner->id][] = $final_conference_id;
                        } else {
                            Log::info("^^Tried to insert a conference with wrong user id or department id!^^");
                            Log::info("Conference id: " . $conference_id);
                            Log::info("User id: " . $conference->user_id);
                            Log::info("Department id: " . $conference->department_id);
                        }
                    } else {
//                        Log::info("Owner has a deleted account... attaching the first institution admin of the institutions as owner!");
//                        Log::info(json_encode($owner));
//                        Log::info(json_encode($conference));
//                        Log::info("||Haven't found a suitable institution administrator!||");
//                        Log::info("Institution id:".$conference->institution_id);
                    }
                } else {
                    $final_user_relation = DB::connection('helper')->table('user_helper')->where("new_user_id", $owner->id)->first();
                    if (isset($final_user_relation->id)) {
                        $conference->user_id = $final_user_relation->final_user_id;
                        if (DB::connection('result_epresence')->table('users')->where("id", $conference->user_id)->exists()
                            && DB::connection('result_epresence')->table('departments')->where("id", $conference->department_id)->exists()
                        ) {
                            $final_conference_id = DB::connection('result_epresence')->table('conferences')->insertGetId((array)$conference);
                            DB::connection('helper')->table('conference_helper')->insert(["old_conference_id" => null, "new_conference_id" => $conference_id, "final_conference_id" => $final_conference_id]);
                        } else {
                            Log::info("^^Tried to insert a conference with wrong user id or department id!^^");
                            Log::info("Conference id: " . $conference_id);
                            Log::info("User id: " . $conference->user_id);
                            Log::info("Department id: " . $conference->department_id);
                        }
                    } else {
//                        Log::info("Haven't found a relation for the conference owner!");
//                        Log::info(json_encode($owner));
//                        Log::info(json_encode($conference));
//                        Log::info("**Haven't found a suitable institution administrator!**");
//                        Log::info("Institution id:".$conference->institution_id);

                    }
                }
            } else {
                Log::info("Owner wasn't found!");
            }
        }

        Log::info("Done migrating conferences. Conference transformation results:");

//        foreach($conference_owner_transformations as $new_user_id => $transformation){
//            Log::info("User with final id: ".$new_user_id);
//            foreach($transformation as $conference_id){
//                Log::info("Is now owner of conference with final id: ".$conference_id);
//            }
//        }
        Log::info(json_encode($conference_owner_transformations));
    }
}
