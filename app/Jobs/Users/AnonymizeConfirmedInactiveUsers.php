<?php

namespace App\Jobs\Users;

use App\Email;
use App\ExtraEmail;
use App\Jobs\Emails\SendAnonymizeEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\User;
use App\Conference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AnonymizeConfirmedInactiveUsers implements ShouldQueue
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

        $beforeFourteenMonths = Carbon::now('Europe/Athens')->subMonths(14)->startOfMonth();

        $confirmedInactiveUsers = User::whereHas(
            'roles', function ($query) {
            $query->where('name','!=','SuperAdmin');
        })->where('confirmed', true)->where('deleted',false)->where('created_at','<',$beforeFourteenMonths)->where(function($query) use ($beforeFourteenMonths){
            $query->whereDoesntHave('conferences', function ($inner_query) use ($beforeFourteenMonths) {
                $inner_query->where('start','>=',$beforeFourteenMonths)->where('conference_user.joined_once', 1);
            })->whereDoesntHave('conferenceAdmin', function($inner_query_2) use ($beforeFourteenMonths){
                $inner_query_2->where('start','>=',$beforeFourteenMonths);
            });
        })->get();

        foreach($confirmedInactiveUsers as $user){

            $active_or_future_conferences = $user->activeFutureConferences();
            foreach($active_or_future_conferences as $conference){

                if ($conference->room_enabled == 1) {

                    $conference->endConference();

                } elseif ($conference->room_enabled == 0 && $conference->start > Carbon::now()) {
                    $conference->cancelConferenceEmail();
                }

                $conference->delete();
            }

            foreach ($user->futureConferences() as $conf) {

                $coordinator = User::find($conf->user_id);
                $parameters['conference'] = $conf;
                $parameters['deleted_user'] = $user;

                $email = Email::where('name', 'participantDeletedCoordinatorsInactivity')->first();

                if($coordinator->status == 1){

                    Mail::send('emails.conference_participantDeletedCoordinatorsInactivity', $parameters, function ($message) use ($coordinator, $email) {
                        $message->from($email->sender_email, config('mail.from.name'))
                            ->to($coordinator->email)
                            ->replyTo(env('SUPPORT_MAIL'), config('mail.from.name'))
                            ->returnPath(env('RETURN_PATH_MAIL'))
                            ->subject($email->title);
                    });
                }

                $conf->detachParticipant($user->id);
            }

                SendAnonymizeEmail::dispatch($user);

                ExtraEmail::where("user_id",$user->id)->delete();

        }
    }
}
