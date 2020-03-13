<?php

namespace App\Imports;

use App\Email;
use App\ExtraEmail;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PersistentIdImport implements ToCollection
{

    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {

            //Form old persistent id

            $persistent_id_to_look_for = $row[2].'!'.$row[1].'!'.$row[4];

            $users_matching_p_id = User::where('persistent_id',$persistent_id_to_look_for)->get();

            //Form new persistent id

            if(count($users_matching_p_id) == 1){

                $user_to_update = $users_matching_p_id[0];

                //Form new persistent id

                $new_persistent_id = $row[3].'!'.$row[1].'!'.$row[5];

                //Found one user with this persistent id
                //do something with this user

                Log::info("Updating user (".$user_to_update->id.") ...");

                //Update persistent id

                $user_to_update->update(['persistent_id'=>$new_persistent_id]);


                //Update institution-departments

                $user_to_update->institutions()->sync([109]);
                $user_to_update->departments()->sync([892]);


                //Form new email

                $new_email = $row[0].'@uniwa.gr';


                $users_using_email = User::where('email',$new_email)->count();
                $extra_emails_with_this_email = ExtraEmail::where('email',$new_email)->count();


                if($users_using_email + $extra_emails_with_this_email == 0){

                    $new_extra_email = new ExtraEmail;
                    $new_extra_email->user_id = $user_to_update->id;
                    $new_extra_email->email = $new_email;
                    $new_extra_email->confirmed = true;
                    $new_extra_email->type = 'sso';
                    $new_extra_email->save();
                }

                $email = Email::where('name', 'updatedInstitution')->first();
                $email_view = 'emails.updated_institution';
                $parameters = array('body' => $email->body);


                Mail::send($email_view, $parameters, function ($message) use ($user_to_update, $email) {
                    $message->from($email->sender_email,config('mail.from.name'))
                        ->to($user_to_update->email)
                        ->replyTo(env('RETURN_PATH_MAIL'))
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });

            }elseif(count($users_matching_p_id) > 1){

                Log::error("Found that there are more than one users having this persistent id: ".$persistent_id_to_look_for);

                //Found more than one users with this persistent id

            }else{

                //Found no users with this persistent id

                Log::error("No users found with this persistent id: ".$persistent_id_to_look_for);
            }
        }
    }
}