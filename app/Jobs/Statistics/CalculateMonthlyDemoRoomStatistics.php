<?php

namespace App\Jobs\Statistics;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DemoRoomController;
use App\User;
use App\ExtraEmail;
use App\Http\Controllers\StatisticsController;

class CalculateMonthlyDemoRoomStatistics implements ShouldQueue
{

    //Calculates Monthly demo room statistics


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
        //Calculate hourly demo room statistics of the last month

        $last_month_start = Carbon::today()->subMonth()->startOfMonth();
        $hour_intervals = DB::table('demo_room_statistics_hourly')->get();

        foreach ($hour_intervals as $hour_interval) {

            $date_obj = Carbon::parse($hour_interval->hour);

            $hour_connections = DB::table('demo_room_cdrs')
                ->whereNotNull('join_time')
                ->whereNotNull('leave_time')
                ->where('join_time', '>=', $last_month_start)
                ->whereRaw('Hour(join_time)=' . $date_obj->hour)
                ->count();

            DB::table('demo_room_statistics_hourly')
                ->where('hour', $hour_interval->hour)
                ->increment('connections',$hour_connections);

        }

        //Calculate monthly demo room statistics of the last month

        $month_connections =  DB::table('demo_room_cdrs')
            ->whereNotNull('join_time')
            ->whereNotNull('leave_time')
            ->where('join_time', '>=', $last_month_start)
            ->count();

        DB::table('demo_room_statistics_monthly')->insert(
            ['month' => $last_month_start->format('Y-m-d'), 'connections' => $month_connections]
        );

        //Nullify monthly demo room connection statistics

        DB::table('demo_room_connections')->update(['last_month_connections'=>0]);


        $user_connections = DB::table('demo_room_cdrs')
            ->whereNotNull('join_time')
            ->whereNotNull('leave_time')
            ->where('join_time', '>=', $last_month_start)
            ->get();


        foreach($user_connections as $connection){

            if(isset($connection->user_id)){

                $statistics_entry = DB::table('demo_room_connections')->where('user_id',$connection->user_id)->first();

                if($statistics_entry) {
                    DB::table('demo_room_connections')->where('user_id', $connection->user_id)->increment('total_connections');
                    DB::table('demo_room_connections')->where('user_id', $connection->user_id)->increment('last_month_connections');
                }
                else {
                    DB::table('demo_room_connections')->insert(
                        ['user_id' => $connection->user_id, 'total_connections' => 1,'last_month_connections' => 1]
                    );
                }
            }
        }
    }
}
