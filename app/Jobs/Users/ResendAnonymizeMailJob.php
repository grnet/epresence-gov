<?php

namespace App\Jobs\Users;

use App\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class ResendAnonymizeMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $user_email,$user_id;

    public function __construct($user_email,$user_id)
    {
        $this->user_id = $user_id;
        $this->user_email = $user_email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user_id =  $this->user_id;
        $email_to = $this->user_email;


        $email = Email::where('name', 'anonymizedAccount')->first();

        $parameters = array('body' => $email->body, 'user_email' => $email_to,'user_id'=>$user_id);

        Mail::send('emails.anonymized_account', $parameters, function ($message) use ($email_to, $email) {
            $message->from($email->sender_email, 'e:Presence')
                ->to($email_to)
                ->replyTo(env('SUPPORT_MAIL'), 'e:Presence')
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });
    }
}
