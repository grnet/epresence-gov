<?php

namespace App\Jobs\Users;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Email;


class DeleteInactiveUnconfirmedUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $beforeThreeMonths = Carbon::now('Europe/Athens')->subMonths(4);
        $now = Carbon::now();

        $unconfirmedUsers = User::whereHas(
            'roles', function ($query) {
            $query->where('name','!=','SuperAdmin');
        })->where('confirmed', 0)->where('created_at', '<=', $beforeThreeMonths)
            ->whereDoesntHave('conferences', function ($query) {
                $query->where('conference_user.joined_once', 1);
            })->get();


        foreach ($unconfirmedUsers as $unconfirmedUser) {

            $users_future_conferences = $unconfirmedUser->conferences()->where('start', '>=', $now)->get();

            foreach ($users_future_conferences as $conf) {

                $coordinator = User::find($conf->user_id);
                $parameters['conference'] = $conf;
                $parameters['deleted_user'] = $unconfirmedUser;

                $email = Email::where('name', 'participantDeletedCoordinators')->first();

                if($coordinator->status == 1){
                    Mail::send('emails.conference_participantDeletedCoordinators', $parameters, function ($message) use ($coordinator, $email) {
                        $message->from($email->sender_email, config('mail.from.name'))
                            ->to($coordinator->email)
                            ->replyTo(env('SUPPORT_MAIL'), config('mail.from.name'))
                            ->returnPath(env('RETURN_PATH_MAIL'))
                            ->subject($email->title);
                    });
                }

            }


            $unconfirmedUser->deleteUnconfirmedUser();
        }


        if(count($unconfirmedUsers) > 0){
            // Notify admins
            $email = Email::where('name', 'deleteUnconfirmedUser')->first();
            $parameters = array('body' => $email->body, 'now' => Carbon::now('Europe/Athens'), 'unconfirmedUsers' => $unconfirmedUsers);
            Mail::send('emails.deleteUnconfirmedUser', $parameters, function ($message) use ($email) {
                $message->from($email->sender_email,config('mail.from.name'))
                    ->to(env('SUPPORT_MAIL'))
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });
        }
    }
}
