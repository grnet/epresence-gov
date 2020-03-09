<?php

namespace App\Console\Commands\Migration;

use App\ExtraEmail;
use App\User;
use Illuminate\Console\Command;
use App\Traits\parsesCsv;
use Illuminate\Support\Facades\Log;


class FlipPersistentID extends Command
{

    use parsesCsv;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flip:persistent-id {file_name} {newInstitutionId}';

    /**
     * The console command description.

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
        $newInstitutionId = $this->argument('newInstitutionId');
        $file_name = $this->argument('file_name');
        $this->info("Flipping persistent ids...");
        $file_path = storage_path('app/persistent_ids/'.$file_name);
        $iterator = $this->readFile($file_path);
        $counter = 0;

        foreach ($iterator as $iteration) {
            if ($counter > 0 && !empty($iteration)) {
                $this->info("Loading row: " . $counter);
                $row = str_getcsv($iteration, ",", '"');
                $user_id = $row[0];
                $old_email = $row[1];
                $old_persistent_id = $row[2];
                $new_email = $row[4];
                $new_persistent_id = $row[5];
                $new_department_id = isset($row[7]) && !empty($row[7]) ? $row[7] : null;
                $matched_users = User::where("persistent_id", $old_persistent_id)->count();
                if ($matched_users === 1) {
                    $user_with_old_persistent_id = User::where("persistent_id", $old_persistent_id)->first();
                    $user_with_new_persistent_id = User::where("persistent_id", $new_persistent_id)->first();
                    if (!isset($user_with_new_persistent_id->id)) {
                        if (!User::where("email", $new_email)->exists()) {
                            if (!ExtraEmail::where("email", $new_email)->exists()) {
                                if (!empty($new_department_id)) {
                                    $user_with_old_persistent_id->departments()->sync([$new_department_id]);
                                    $user_with_old_persistent_id->institutions()->sync([$newInstitutionId]);
                                    $user_with_old_persistent_id->update(["persistent_id" => $new_persistent_id, "email" => $new_email]);
                                } else {

                                    //Make user unconfirmed and send him an email

                                    // $user_with_old_persistent_id->update(["persistent_id" => $new_persistent_id, "email" => $new_email, "confirmed" => false]);

                                    //Example parameters

//                                        $parameters = ['contact_url' => URL::to("contact")];
//
//                                        Mail::send('emails.account_disabled', $parameters, function ($message) use ($user_with_old_persistent_id) {
//                                            $message->from("Enter from email here", 'e:Presence')
//                                                ->to($user_with_old_persistent_id->email)
//                                                ->cc(env('SUPPORT_MAIL'), 'e:Presence')
//                                                ->replyTo(env('SUPPORT_MAIL'), 'e:Presence')
//                                                ->returnPath(env('RETURN_PATH_MAIL'))
//                                                ->subject("Enter subject here");
//                                        });

                                }

                                if (!User::where("email", $old_email)->exists()) {
                                    if (!ExtraEmail::where("email", $old_email)->exists()) {
                                        $new_extra_email = new ExtraEmail;
                                        $new_extra_email->user_id = $user_with_old_persistent_id->id;
                                        $new_extra_email->email = $old_email;
                                        $new_extra_email->type = 'custom';
                                        $new_extra_email->confirmed = true;
                                        $new_extra_email->save();
                                    } else {
                                        Log::info("Tried to create old email as extra email but we found an extra email using this email address: " . $old_email);
                                    }
                                } else {
                                    Log::info("Tried to create old email as extra email but we found a user using this email address: " . $old_email);
                                }
                            } else {
                                Log::info("Tried to update the user with the new email but it was found as extra email: " . $new_email);
                            }
                        } else {
                            Log::info("Tried to update the user with the new email but we found a user already using this email address: " . $new_email);
                        }
                    } else {
                        Log::info("On row: ".$counter);
                        Log::info("Found a user already using the new persistent id :" . $new_persistent_id. " user_id: ".$user_id);
                        Log::info("Merging user ".$user_with_old_persistent_id->id." into user: ".$user_with_new_persistent_id->id );

                        ExtraEmail::create(["user_id"=>$user_with_old_persistent_id->id,"email"=>$user_with_old_persistent_id->email,"type"=>"custom","confirmed"=>true]);
                        ExtraEmail::where("user_id",$user_with_old_persistent_id->id)->update(["user_id"=>$user_with_new_persistent_id->id]);
                        if(!empty($new_department_id)) {
                            $user_with_new_persistent_id->institutions()->sync([$newInstitutionId]);
                            $user_with_new_persistent_id->departments()->sync([$new_department_id]);
                        }
                        $user_with_new_persistent_id->merge_user($user_with_old_persistent_id->id,false);
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
