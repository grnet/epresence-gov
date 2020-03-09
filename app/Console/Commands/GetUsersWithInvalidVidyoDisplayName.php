<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


use App\User;



class GetUsersWithInvalidVidyoDisplayName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:users_with_invalid_vidyoName';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::whereHas('roles',function($query){
            $query->whereIn('name',['InstitutionAdministrator','DepartmentAdministrator','SuperAdmin']);
        })->get();
        $result_arr = [];
        foreach($users as $user){
            $displayName = $user->firstname . ' ' . $user->lastname;
            if(mb_strlen($displayName)>40)
                $result_arr[] = $user->id;


        }
      $this->info(json_encode($result_arr));
    }
}
