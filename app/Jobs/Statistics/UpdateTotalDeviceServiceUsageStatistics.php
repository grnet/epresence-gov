<?php

namespace App\Jobs\Statistics;

use App\Conference;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class UpdateTotalDeviceServiceUsageStatistics implements ShouldQueue
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
        $total_desk_mobile_users = DB::table('conference_user')->where('device', 'Desktop-Mobile')->groupBy('user_id')->get()->count();
        DB::table('service_usage')->where('option', 'total')->update(['desktop_mobile'=>$total_desk_mobile_users+5500]);

        $h323 =  DB::table('conference_user')->where('device', 'H323')->groupBy('user_id')->get()->count();
        DB::table('service_usage')->where('option', 'total')->update(['h323'=>$h323+300]);
    }
}
