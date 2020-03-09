<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:users';

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
            ->table('users')
            ->truncate();
        DB::connection('result_epresence')
            ->table('role_user')
            ->truncate();
        DB::connection('result_epresence')
            ->table('department_user')
            ->truncate();
        DB::connection('result_epresence')
            ->table('institution_user')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        DB::connection('helper')
            ->table('user_helper')
            ->truncate();



        $old_users = DB::connection('vidyo')
            ->table('users')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('department_user', 'department_user.user_id', '=', 'users.id')
            ->join('institution_user', 'institution_user.user_id', '=', 'users.id')
            ->select(["users.*", "role_user.role_id","institution_user.institution_id","department_user.department_id"])
            ->where("deleted", false)
            ->where("confirmed",true)
            ->get();

        Log::info("Checking users from old (vidyo) platform...");
        Log::info("\n");

        foreach ($old_users as $user) {

//            Log::info("Checking user from old platform:");
//            Log::info(json_encode($user));

            if ($user->state === "sso") {
                $new_user = DB::connection('zoom')->table('users')->where("state", "sso")->where("persistent_id", $user->persistent_id)->where('deleted',false)->first();
            } else {
                $new_user = DB::connection('zoom')->table('users')->where("state", "local")->where("email", $user->email)->where('deleted',false)->first();
            }

            if (!isset($new_user->id)) {

                $check_for_the_email = DB::connection('zoom')->table('users')->where("email", $user->email)->first();

                if (!isset($check_for_the_email->id)) {
                    $old_user_id = $user->id;
                    unset($user->id);
                    unset($user->vidyoID);
                    $role_id = $user->role_id;
                    $department_id = $user->department_id;
                    $institution_id = $user->institution_id;
                    unset($user->role_id);
                    unset($user->department_id);
                    unset($user->institution_id);
                    if (empty($user->confirmation_state)) {
                        $user->confirmation_state = $user->state === "sso" ? "shibboleth" : "local";
                    }
                    $final_user_id = DB::connection('result_epresence')->table('users')->insertGetId((array)$user);
                    DB::connection('helper')->table('user_helper')->insert(["old_user_id" => $old_user_id, "new_user_id" => null, "final_user_id" => $final_user_id]);
                    DB::connection('result_epresence')->table('role_user')->insert(['role_id'=>$role_id,'user_id'=>$final_user_id]);
                    DB::connection('result_epresence')->table('department_user')->insert(['department_id'=>$department_id,'user_id'=>$final_user_id]);
                    DB::connection('result_epresence')->table('institution_user')->insert(['institution_id'=>$institution_id,'user_id'=>$final_user_id]);
                } else {
                    Log::info("***Matched user from old db(vidyo) with the email***");
                    Log::info(json_encode($user));
                    Log::info("State was : " . $user->state);
                    Log::info("Persistent id: " . $user->persistent_id);
                    Log::info("Email:  " . $user->email);
                    Log::info("Confirmed:  " . $user->confirmed);

                    Log::info(json_encode($check_for_the_email));
                    Log::info("And became : " . $check_for_the_email->state);
                    Log::info("Persistent id: " . $check_for_the_email->persistent_id);
                    Log::info("Email:  " . $check_for_the_email->email);
                    Log::info("Confirmed:  " . $check_for_the_email->confirmed);
                    Log::info("\n");
                }
            } else {
//                Log::info("Found the same user in the new platform:");
//                Log::info(json_encode($new_user));
                $old_user_id = $user->id;
                unset($user->id);
                unset($user->vidyoID);
                $role_id = $user->role_id;
                $department_id = $user->department_id;
                $institution_id = $user->institution_id;
                unset($user->role_id);
                unset($user->institution_id);
                unset($user->department_id);
                if (empty($user->confirmation_state)) {
                    $user->confirmation_state = $user->state === "sso" ? "shibboleth" : "local";
                }

                $final_user_id = DB::connection('result_epresence')->table('users')->insertGetId((array)$user);
                DB::connection('helper')->table('user_helper')->insert(["old_user_id" => $old_user_id, "new_user_id" => $new_user->id, "final_user_id" => $final_user_id]);
                DB::connection('result_epresence')->table('role_user')->insert(['role_id'=>$role_id,'user_id'=>$final_user_id]);
                DB::connection('result_epresence')->table('department_user')->insert(['department_id'=>$department_id,'user_id'=>$final_user_id]);
                DB::connection('result_epresence')->table('institution_user')->insert(['institution_id'=>$institution_id,'user_id'=>$final_user_id]);
            }
        }

        Log::info("Checking users from new (zoom) platform...");
        Log::info("\n");

        //Iterate over user of the zoom platform here

        $new_users = DB::connection('zoom')
            ->table('users')
            ->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftjoin('department_user', 'department_user.user_id', '=', 'users.id')
            ->leftjoin('institution_user', 'institution_user.user_id', '=', 'users.id')
            ->select(["users.*", "role_user.role_id","institution_user.institution_id","department_user.department_id"])
            ->where("deleted", false)
            ->get();

        foreach ($new_users as $user) {
            if(!DB::connection('helper')->table('user_helper')->where("new_user_id",$user->id)->exists()){
                $check_for_the_email = DB::connection('result_epresence')->table('users')->where("email", $user->email)->first();
                if (!isset($check_for_the_email->id)) {
                    $role_id = $user->role_id;
                    $department_id = $user->department_id;
                    $institution_id = $user->institution_id;
                    $user_id = $user->id;
                    unset($user->role_id);
                    unset($user->institution_id);
                    unset($user->department_id);
                    unset($user->id);
                    $final_user_id = DB::connection('result_epresence')->table('users')->insertGetId((array)$user);
                    DB::connection('helper')->table('user_helper')->insert(["old_user_id" => null, "new_user_id" => $user_id, "final_user_id" => $final_user_id]);

                    if(!empty($role_id))
                    DB::connection('result_epresence')->table('role_user')->insert(['role_id'=>$role_id,'user_id'=>$final_user_id]);

                    if(!empty($department_id))
                    DB::connection('result_epresence')->table('department_user')->insert(['department_id'=>$department_id,'user_id'=>$final_user_id]);

                    if(!empty($institution_id))
                    DB::connection('result_epresence')->table('institution_user')->insert(['institution_id'=>$institution_id,'user_id'=>$final_user_id]);

                }else{
                    Log::info("***Matched user from new db(zoom) with the email***");
                    Log::info(json_encode($user));
                    Log::info("State was : " . $user->state);
                    Log::info("Persistent id: " . $user->persistent_id);
                    Log::info("Email:  " . $user->email);
                    Log::info("Confirmed:  " . $user->confirmed);

                    Log::info(json_encode($check_for_the_email));
                    Log::info("And became : " . $check_for_the_email->state);
                    Log::info("Persistent id: " . $check_for_the_email->persistent_id);
                    Log::info("Email:  " . $check_for_the_email->email);
                    Log::info("Confirmed:  " . $check_for_the_email->confirmed);
                    Log::info("\n");
                }
            }
        }
    }


}
