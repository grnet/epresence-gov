<?php

namespace App\Jobs\Emails;

use App\Email;
use App\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAnonymizeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;
        $email_to = $user->email;
        $user_id = $user->id;

        $email = Email::where('name', 'anonymizedAccount')->first();

        $parameters = array('body' => $email->body, 'user_email' => $email_to, 'user_id' => $user_id);

        Mail::send('emails.anonymized_account', $parameters, function ($message) use ($email_to, $email) {
            $message->from($email->sender_email, config('mail.from.name'))
                ->to($email_to)
                ->replyTo(env('SUPPORT_MAIL'), config('mail.from.name'))
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        Log::info("Anonymization after 14 months email send to: " . $email_to . " user_id: " . $user_id);

        $user->name = "Deleted";
        $user->email = "Deleted-" . $user->id . "@example.org";
        $user->password = str_random(15);
        $user->firstname = "Deleted";
        $user->lastname = "Deleted";
        $user->telephone = null;
        $user->status = 0;
        $user->thumbnail = null;
        $user->persistent_id = null;
        $user->activation_token = null;
        $user->confirmation_code = null;
        $user->remember_token = null;
        $user->deleted = true;
        $user->update();

        //Remove current role & attach end user role

        $current_role = $user->roles()->first();

        $end_user_role = Role::where('name', 'EndUser')->first();

        $user->roles()->detach($current_role->id);
        $user->roles()->attach($end_user_role->id);

    }
}
