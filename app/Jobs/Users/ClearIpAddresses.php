<?php

namespace App\Jobs\Users;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Conference;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClearIpAddresses implements ShouldQueue
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

        $end = Carbon::now('Europe/Athens')->subMonths(14)->startOfMonth();

        $conferences_before_fourteen_months = Conference::where('start','<=',$end)->pluck("id")->toArray();

        DB::table('conference_user')->whereIn('conference_id',$conferences_before_fourteen_months)->where('joined_once',1)->update(["address"=>"Not Available"]);
    }
}
