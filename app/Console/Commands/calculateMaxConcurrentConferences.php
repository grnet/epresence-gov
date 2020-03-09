<?php

namespace App\Console\Commands;

use App\Conference;
use App\Http\Controllers\StatisticsController;
use App\Settings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class calculateMaxConcurrentConferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:max_concurrent_conferences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $utilization_statistics = DB::table('utilization_statistics')->get();

        foreach($utilization_statistics as $statistic){

            $start_date_range = Carbon::parse($statistic->month)->startOfMonth()->startOfDay();
            $end_date_range = Carbon::parse($statistic->month)->endOfDay();
            $this->info("Calculating max concurrent conferences from: ".$start_date_range->toDateTimeString()." to: ".$end_date_range);
            $conferences = Conference::where('start', '>=', $start_date_range)->where('start', '<', $end_date_range)->whereNotNull('end')->where('end', '>=', $start_date_range)->where('end', '<', $end_date_range)->get();
            $conferences_sorted_events_array = StatisticsController::create_interval_events_array_sorted('start','end',$conferences,false);
            $max_conferences_concurrent_results = StatisticsController::get_max_date_value_from_sorted_events($conferences_sorted_events_array);
            $max_conferences_data = Conference::where('conferences.start', '>=', $start_date_range)->where('conferences.start', '<', $end_date_range)
                ->selectRaw('COUNT(conferences.id) as total_conferences_per_day,DATE(conferences.start) as day')
                ->groupBy(DB::raw('DATE(conferences.start)'))
                ->orderBy('total_conferences_per_day', 'desc')
                ->first();

            $max_conferences_value = isset($max_conferences_data->total_conferences_per_day) ? $max_conferences_data->total_conferences_per_day : 0;
            $max_conferences_day = isset($max_conferences_data->day) ? $max_conferences_data->day : null;

            DB::table('utilization_statistics')
                ->where('month', $statistic->month)
                ->update([
                    'max_concurrent_conferences'=>$max_conferences_concurrent_results['value'],
                    'max_concurrent_conferences_day'=>$max_conferences_concurrent_results['date'],
                    'max_conferences'=>$max_conferences_value,
                    'max_conferences_day'=>$max_conferences_day,
                ]);
        }
    }
}
