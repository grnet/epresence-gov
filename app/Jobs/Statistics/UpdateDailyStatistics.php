<?php

namespace App\Jobs\Statistics;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Conference;
use Carbon\Carbon;
use App\Statistics;
use Illuminate\Support\Facades\DB;

class UpdateDailyStatistics implements ShouldQueue
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
        $start = Conference::timeFromNow(Carbon::now('Europe/Athens'), 5, 'sub');
        $now = Conference::timeFromNow(Carbon::now('Europe/Athens'), 0, 'sub');

        $activeParticipantsForLastFiveMins = Conference::where('start', '<=', $start)->where('end', '>=', $now)->get();

        // Set all fields to 0

        $current_five_minute_id = Statistics::current_five_minute_id(Carbon::today(), $now);

        DB::table('statistics_daily')->where('id', $current_five_minute_id)->update(
            [
                'users_no_desktop' => 0,
                'distinct_users_no_desktop' => 0,
                'users_no_h323' => 0,
                'distinct_users_no_h323' => 0,
                'updated_at' => $now,
                'conferences_no'=>count($activeParticipantsForLastFiveMins)
            ]);

        foreach ($activeParticipantsForLastFiveMins as $conference) {
            $conference->activeParticipantsForLastFiveMins($start, $now, $current_five_minute_id);
        }

        // Update max users 

        $first_day_of_month = Carbon::now('Europe/Athens')->startOfMonth()->format('Y-m-d');

        // Update max users

        $current_five_minute_distinct_users = DB::table('statistics_daily')->where('id', $current_five_minute_id)->first();
        $month_max_values = DB::table('statistics_monthly')->where('month', $first_day_of_month)->count();

        if ($month_max_values > 0) {
            $month_max = DB::table('statistics_monthly')->where('month', $first_day_of_month)->first();
            // Desktop-Mobile

            if (isset($current_five_minute_distinct_users->id) && $month_max->max_desktop < $current_five_minute_distinct_users->distinct_users_no_desktop) {
                $month_max_desktop = $current_five_minute_distinct_users->distinct_users_no_desktop;
            } else {
                $month_max_desktop = $month_max->max_desktop;
            }

            // H323

            if (isset($current_five_minute_distinct_users->id) && $month_max->max_h323 < $current_five_minute_distinct_users->distinct_users_no_h323) {
                $month_max_h323 = $current_five_minute_distinct_users->distinct_users_no_h323;
            } else {
                $month_max_h323 = $month_max->max_h323;
            }


            DB::table('statistics_monthly')->where('id', $month_max->id)->update(
                [
                'max_desktop' => $month_max_desktop,
                'max_h323' => $month_max_h323,
                'updated_at'=>$now
                ]
            );

        } else {

            DB::table('statistics_monthly')->insert(
                [
                    'month' => $first_day_of_month,
                    'max_desktop' => isset($current_five_minute_distinct_users->id) ? $current_five_minute_distinct_users->distinct_users_no_desktop: 0,
                    'max_h323' => isset($current_five_minute_distinct_users->id) ? $current_five_minute_distinct_users->distinct_users_no_h323 : 0,
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ]);

        }

    }
}
