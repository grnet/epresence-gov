<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Department;
class UpdateUsersWithNoDepartment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:users_with_no_department';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for test purposes';

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
        $user_with_no_department = User::whereDoesntHave('departments')->where('state','sso')->where('confirmed',true)->get();

        foreach($user_with_no_department as $user){

          $other_department = $user->institutions()->first()->departments()->where('departments.slug', 'other')->first();

          if(is_null($user->custom_values)) {
              $user->departments()->attach($other_department->id);
          }else{

              $customValues = $user->customValues();

              if(!empty($customValues['department'])) {
                  $new_department = Department::create(['title' => $customValues['department'], 'slug' => 'noID', 'institution_id' => $user->institutions()->first()->id]);
                  $user->departments()->attach($new_department->id);
              }
              else{
                  $user->departments()->attach($other_department->id);
              }
          }
        }
    }
}
