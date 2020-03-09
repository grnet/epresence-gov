<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateDemoRoomStatisticsMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:demo-statistics-monthly';

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
     *'demo_room_statistics_monthly insert from old db kai !!prosthetoume me to xeri ta kainouria (2 mines)!!'
     * @return mixed
     */
    public function handle()
    {
        Log::info("Migrating demo_room_statistics_monthly from old platform...");
        Log::info("\n");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('demo_room_statistics_monthly')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();
        $monthly_demo_room_statistics  = DB::connection('vidyo')->table('demo_room_statistics_monthly')->orderBy('month','asc')->get();
        foreach($monthly_demo_room_statistics as $statistic){
            Db::connection('result_epresence')->table('demo_room_statistics_monthly')->insert((array)$statistic);
        }

        Log::info("Migrated demo_room_statistics_monthly from old platform!");
        Log::info("\n");
    }
}
