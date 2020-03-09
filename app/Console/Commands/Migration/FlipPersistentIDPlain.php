<?php

namespace App\Console\Commands\Migration;


use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Traits\parsesCsv;

class FlipPersistentIDPlain extends Command
{
    use parsesCsv;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flip:persistent-id-plain {file_name}';

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
        $this->info("Flipping persistent ids...");
        $file_path = storage_path('app/persistent_ids/'.$file_name);
        $iterator = $this->readFile($file_path);
        $counter = 0;

        foreach ($iterator as $iteration) {
            if ($counter > 0 && !empty($iteration)) {
                $this->info("Loading row: " . $counter);
                $row = str_getcsv($iteration, ",", '"');
                $old_persistent_id = $row[2];
                $new_persistent_id = $row[3];
                $matched_users = User::where("persistent_id", $old_persistent_id)->count();
                if ($matched_users === 1) {
                    $user_with_old_persistent_id = User::where("persistent_id", $old_persistent_id)->first();
                    $user_with_new_persistent_id = User::where("persistent_id", $new_persistent_id)->first();
                    if (!isset($user_with_new_persistent_id->id)) {
                        $user_with_old_persistent_id->update(['persistent_id'=>$new_persistent_id]);
                    } else {
                        Log::error("Found a user using the new persistent id: " . $user_with_new_persistent_id);
                        Log::error("User using the new persistent id: ".$user_with_new_persistent_id->id);
                        Log::error("User using the old persistent id: ".$user_with_old_persistent_id->id);
                        Log::error("Old persistent id: ".$old_persistent_id);
                        Log::error("New persistent id: ".$new_persistent_id);
                    }
                } else {

                    if($matched_users == 0)
                        Log::error("Could not find user with old persistent id :" . $old_persistent_id);
                    else
                        Log::error("Found more than one user with old persistent id :" . $old_persistent_id);
                }
            }
            $counter++;
        }

        $this->info("Flipping persistent ids finished!");
    }
}
