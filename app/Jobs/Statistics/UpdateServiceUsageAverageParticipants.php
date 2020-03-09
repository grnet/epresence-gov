<?php

namespace App\Jobs\Statistics;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Conference;
use Carbon\Carbon;

class UpdateServiceUsageAverageParticipants implements ShouldQueue
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
        $total_number_conferences = Conference::where('end', '<=', Carbon::yesterday())->nontest()->count();
        $total_participants_joined = Conference::nontest()
            ->join('conference_user','conference_user.conference_id','=','conferences.id')
            ->where('conference_user.joined_once',1)
            ->where('conferences.end','<=',Carbon::yesterday())
            ->count();
        $avg = $total_number_conferences !== 0 ? $total_participants_joined / $total_number_conferences : 0;
        DB::table('service_usage')->where('option', 'total')->update(['average_participants' => $avg]);
    }
}
