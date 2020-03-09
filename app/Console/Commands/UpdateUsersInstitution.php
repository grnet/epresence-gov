<?php

namespace App\Console\Commands;

use App\Conference;
use App\Department;
use App\ExtraEmail;
use App\Institution;
use App\User;
use Illuminate\Console\Command;
use App\Traits\parsesCsv;
use Illuminate\Support\Facades\Log;

class UpdateUsersInstitution extends Command
{
    use parsesCsv;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:users_institution {new_institution_id} {file_name}';

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
        $file_name = $this->argument('file_name');
        $new_institution_id = $this->argument('new_institution_id');
        $this->info("Updating users institution and department...");
        $this->info("New institution id: ".$new_institution_id);
        $file_path = storage_path('app/persistent_ids/'.$file_name);
        $iterator = $this->readFile($file_path);
        $counter = 0;
        foreach ($iterator as $iteration) {
            if ($counter > 0 && !empty($iteration)) {
                $this->info("Loading row: " . $counter);
                $row = str_getcsv($iteration, ",", '"');
                $user_id = $row[0];
                $new_department_id = $row[2];
                $new_email = $row[3];
                $new_persistent_id = $row[4];
                $user = User::find($user_id);
                $department = Department::find($new_department_id);
                $institution = Institution::find($new_institution_id);
                if(isset($user->id) && isset($department->id) && isset($institution->id)){

                    $emails_exists_on_user = User::where("email",$new_email)->where("id","!=",$user_id)->first();
                    $emails_exists_on_extra_email = ExtraEmail::where("email",$new_email)->where("user_id","!=",$user_id)->first();
                    $persistent_id_exists = User::where("persistent_id",$new_persistent_id)->where("id","!=",$user_id)->first();

                    if(isset($emails_exists_on_user->id)){
                        $this->error("Row:");
                        $this->error(json_encode($row));
                        $this->error("New email exists on user:");
                        $this->error(json_encode($emails_exists_on_user));
                        break;
                    }

                    if(isset($emails_exists_on_extra_email->id)){
                        $this->error("Row:");
                        $this->error(json_encode($row));
                        $this->error("New email exists on extra email:");
                        $this->error(json_encode($emails_exists_on_extra_email));
                        break;
                    }

                    if(isset($persistent_id_exists->id)){
                        $this->error("Row:");
                        $this->error(json_encode($row));
                        $this->error("New persistent id exists on user:");
                        $this->error(json_encode($persistent_id_exists));
                        break;
                    }

                    Conference::where('user_id', $user_id)->update(['institution_id'=>$new_institution_id,'department_id'=>$new_department_id]);
                    $user->institutions()->sync([$new_institution_id]);
                    $user->departments()->sync([$new_department_id]);
                    $old_email = $user->email;
                    ExtraEmail::create(["user_id"=>$user->id,"email"=>$old_email,"type"=>"custom","confirmed"=>true]);
                    $user->update(['email'=>$new_email,'persistent_id'=>$new_persistent_id]);
                }else{
                    $this->error("Could not find model:");
                    switch (true){
                        case !isset($user->id):
                            $message = "User with id: ".$user_id;
                            break;
                        case !isset($department->id):
                            $message = "Department with id: ".$new_department_id;
                            break;
                        case !isset($institution->id):
                            $message = "Institution with id: ".$new_institution_id;
                            break;
                        default:
                            $message = null;
                            break;
                    }
                    $this->error($message);
                }
            }
            $counter++;
        }
        $this->info("Updating users institution and department finished!");
    }
}
