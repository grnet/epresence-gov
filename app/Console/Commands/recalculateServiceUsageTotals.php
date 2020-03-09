<?php

namespace App\Console\Commands;

use App\Conference;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class recalculateServiceUsageTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculate:service_usage';

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
        //We are starting with 15000 from eP (2011-2016)
        $total_conferences = 15000;
        $total_conferences += Conference::where('end', '<=', Carbon::now())->nontest()->count();
        $average_participants = DB::table('service_usage')->where('option', 'total')->value('average_participants');
        DB::table('service_usage')->where('option', 'total')
            ->update(
                ['euro_saved'=>round($total_conferences * ($average_participants/2) * config('conferences.euro_saved')),
                "total_conferences"=>$total_conferences]);
        $this->info("Service usage total conferences and euro saved recalculated!");
    }
}
