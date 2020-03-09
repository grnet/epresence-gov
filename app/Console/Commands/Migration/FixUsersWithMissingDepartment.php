<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FixUsersWithMissingDepartment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fix-user-department';

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
        Log::info("Fixing users with no department...");

        $users = DB::connection('vidyo')->table('users')
            ->selectRaw('users.id, institution_user.institution_id, COUNT(department_user.user_id) as attached_departments')
            ->join('institution_user','institution_user.user_id','=','users.id')
            ->leftjoin('department_user','department_user.user_id','=','users.id')
            ->where('users.confirmed',1)
            ->where('users.deleted',0)
            ->groupBy('users.id')
            ->havingRaw('attached_departments < 1')
            ->get();


        foreach($users as $user){
            $admin_department = DB::connection('vidyo')
                ->table('departments')
                ->where('slug','admin')
                ->where('institution_id',$user->institution_id)
                ->first();

            if(isset($admin_department->id)){
                Log::info("Fixed user:".$user->id);
                DB::connection('vidyo')->table('department_user')->insert(['department_id'=>$admin_department->id,'user_id'=>$user->id]);
            }else{
                Log::error("Admin department not found for institution: ".$user->institution_id);
            }
        }
    }

}
