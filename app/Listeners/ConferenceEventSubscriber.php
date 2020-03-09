<?php

namespace App\Listeners;



use App\Conference;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ConferenceEventSubscriber
{

    /**
     * Handle participant join events
     * @param $event
     */
    public function onParticipantJoined($event) {

        $user_id = $event->user_id;
        $conference_id = $event->conference_id;
        $user = User::find($user_id);
        $conference = Conference::find($conference_id);

        if(isset($conference->id) && isset($user->id)){

           $current_intervals =  $user->participantValues($conference->id)->intervals;
           $intervals = is_null($current_intervals) ? [] : json_decode($current_intervals);
           $intervals[] = ["join_time"=>Carbon::now()->toDateTimeString(),"leave_time"=>null];

            DB::table('conference_user')
                ->where('conference_id',$conference->id)
                ->where('user_id',$user->id)
                ->update(['intervals'=>json_encode($intervals)]);

        }
    }

    /**
     * Handle participant left events
     * @param $event
     */
    public function onParticipantLeft($event) {
        $user_id = $event->user_id;
        $conference_id = $event->conference_id;
        $user = User::find($user_id);
        $conference = Conference::find($conference_id);
        $current_intervals =  $user->participantValues($conference->id)->intervals;

        if(!empty($current_intervals)){

            $current_intervals = json_decode($current_intervals);
            $total_intervals = count($current_intervals);

            if(empty($current_intervals[$total_intervals-1]->leave_time)){

                $current_intervals[$total_intervals-1]->leave_time = Carbon::now()->toDateTimeString();

                $current_duration = empty($user->participantValues($conference->id)->duration) ? 0 : $user->participantValues($conference->id)->duration;
                $current_duration += Carbon::parse($current_intervals[$total_intervals-1]->join_time)->diffInSeconds(Carbon::parse($current_intervals[$total_intervals-1]->leave_time));

                DB::table('conference_user')
                    ->where('conference_id',$conference->id)
                    ->where('user_id',$user->id)
                    ->update(['intervals'=>json_encode($current_intervals),'duration'=>$current_duration]);
            }
        }
    }


    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\ParticipantJoined',
            'App\Listeners\ConferenceEventSubscriber@onParticipantJoined'
        );

        $events->listen(
            'App\Events\ParticipantLeft',
            'App\Listeners\ConferenceEventSubscriber@onParticipantLeft'
        );

    }

}