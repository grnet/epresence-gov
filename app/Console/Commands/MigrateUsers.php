<?php

namespace App\Console\Commands;

use App\Department;
use App\ExtraEmail;
use App\Institution;
use App\Role;
use App\User;
use Carbon\Carbon;
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
    protected $signature = 'migrate:users_old';

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

        DB::table('users')->truncate();
        DB::table('role_user')->truncate();
        DB::table('department_user')->truncate();
        DB::table('institution_user')->truncate();
        DB::table('users_extra_emails')->truncate();

        Schema::enableForeignKeyConstraints();

        $user_rows = Db::table('users_old')->get();

        foreach($user_rows as $row){

            $user_data = collect($row);

            if(empty($user_data['confirmation_state'])){
                $user_data['confirmation_state'] = $user_data['state'] === "local" ? "local" : "shibboleth";
            }

            $user_data = $user_data->except(['created_at','updated_at','vidyoID']);
            $user_data['created_at'] = Carbon::now();
            $user_data['updated_at'] = Carbon::now();

            Db::table('users')->insert($user_data->toArray());
        }

        $this->info("Users imported!");

        $role_user_rows = Db::table('role_user_old')->get();

        foreach($role_user_rows as $row){
            $user = User::find($row->user_id);
            $role = Role::find($row->role_id);
            if(isset($user->id) && isset($role->id)){
                $user->roles()->attach($row->role_id);
            }
        }

        $this->info("Role connections imported!");

        $inst_user_rows = Db::table('institution_user_old')->get();
        foreach($inst_user_rows as $row){
            $user = User::find($row->user_id);
            $inst = Institution::find($row->institution_id);
            if(isset($user->id) && isset($inst->id)){
                $user->institutions()->attach($row->institution_id);
            }
        }

        $this->info("Institution connections imported!");

        $dep_user_rows = Db::table('department_user_old')->get();
        foreach($dep_user_rows as $row){
            $user = User::find($row->user_id);
            $dep = Department::find($row->department_id);
            if(isset($user->id) && isset($dep->id)){
                $user->departments()->attach($row->department_id);
            }
        }

        $this->info("Department connections imported!");

        $email_user_rows = Db::table('users_extra_emails_old')->get();

        foreach($email_user_rows as $row){
            $user = User::find($row->user_id);

            if(isset($user->id)){
                ExtraEmail::create((array) $row);
            }
        }

        $this->info("Extra emails imported!");
        $this->info("Done migrating users from vidyo-epresence to zoom-epresence!");

    }
}
