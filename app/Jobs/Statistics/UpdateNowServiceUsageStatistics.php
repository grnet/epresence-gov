<?php

namespace App\Jobs\Statistics;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Conference;
use Illuminate\Support\Facades\DB;
use App\User;
use Carbon\Carbon;

class UpdateNowServiceUsageStatistics implements ShouldQueue
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
        $conf_list = Conference::where('start', '>=', Carbon::today())->where('end', '<=', Carbon::tomorrow())->pluck('id')->toArray();                           //TOTAL CONFERENCES TODAY
        $total_conferences_today = count($conf_list);

        $desk_mobile = DB::table('conference_user')->whereIn('conference_id', $conf_list)->where('device', 'Desktop-Mobile')->pluck('user_id')->toArray();
        $total_desk_mobile_users = count(array_unique($desk_mobile));

        $h323 = DB::table('conference_user')->whereIn('conference_id', $conf_list)->where('device','H323')->pluck('user_id')->toArray();           //TOTAL H323 USERS TODAY
        $total_h323 = count(array_unique($h323));

        DB::table('service_usage')->where('option', 'today')->update(['h323' => $total_h323,'updated_at' => Carbon::now(),'desktop_mobile' => $total_desk_mobile_users,'total_conferences' => $total_conferences_today]);

        $total_conferences_now = Conference::where('room_enabled', 1)->count();
        $active_conferences_now = 0;

        if ($total_conferences_now >= 1) {
            $conf_list_now = Conference::where('room_enabled', 1)->get();
            $total_desk_mobile_users_now = 0;
            $total_h323_now = 0;
            foreach ($conf_list_now as $conference) {

                $total_online_participants = DB::table('conference_user')->where('conference_id',$conference->id)->where('in_meeting',true)->count();

                if ($total_online_participants > 0) {
                    $active_conferences_now++;


                    $total_desk_mobile_users_now += DB::table('conference_user')->where('conference_id',$conference->id)->where('in_meeting',true)->where('device','Desktop-Mobile')->count();
                    $total_h323_now += DB::table('conference_user')->where('conference_id',$conference->id)->where('in_meeting',true)->where('device','H323')->count();
                }
            }
            DB::table('service_usage')->where('option', 'now')->update(['total_conferences' => $active_conferences_now]);                // TOTAL ACTIVE CONFERENCES NOW
            DB::table('service_usage')->where('option', 'now')->update(['desktop_mobile' => $total_desk_mobile_users_now]);                // TOTAL DESKTOP USERS NOW
            DB::table('service_usage')->where('option', 'now')->update(['h323' => $total_h323_now]);                                //TOTAL H323 USERS NOW
            DB::table('service_usage')->where('option', 'now')->update(['updated_at' => Carbon::now()]);
        } else {
            DB::table('service_usage')->where('option', 'now')->update(['total_conferences' => 0]);
            DB::table('service_usage')->where('option', 'now')->update(['desktop_mobile' => 0]);
            DB::table('service_usage')->where('option', 'now')->update(['h323' => 0]);
        }
    }
}
