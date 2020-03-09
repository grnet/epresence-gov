<?php

namespace App\Console\Commands\Installation\Models;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Statistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:statistics';

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
        Schema::disableForeignKeyConstraints();
        Db::table('statistics_daily')->truncate();
        Db::table('demo_room_statistics_hourly')->truncate();
        DB::table('statistics_daily')->truncate();
        for($i = 1; $i < 289; $i++){
            DB::table('statistics_daily')->insert(['created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        }

        DB::table('demo_room_statistics_hourly')->insert([
            ['hour' => '00:00:00', 'connections' => 0],
            ['hour' => '01:00:00', 'connections' => 0],
            ['hour' => '02:00:00', 'connections' => 0],
            ['hour' => '03:00:00', 'connections' => 0],
            ['hour' => '04:00:00', 'connections' => 0],
            ['hour' => '05:00:00', 'connections' => 0],
            ['hour' => '06:00:00', 'connections' => 0],
            ['hour' => '07:00:00', 'connections' => 0],
            ['hour' => '08:00:00', 'connections' => 0],
            ['hour' => '09:00:00', 'connections' => 0],
            ['hour' => '10:00:00', 'connections' => 0],
            ['hour' => '11:00:00', 'connections' => 0],
            ['hour' => '12:00:00', 'connections' => 0],
            ['hour' => '13:00:00', 'connections' => 0],
            ['hour' => '14:00:00', 'connections' => 0],
            ['hour' => '15:00:00', 'connections' => 0],
            ['hour' => '16:00:00', 'connections' => 0],
            ['hour' => '17:00:00', 'connections' => 0],
            ['hour' => '18:00:00', 'connections' => 0],
            ['hour' => '19:00:00', 'connections' => 0],
            ['hour' => '20:00:00', 'connections' => 0],
            ['hour' => '21:00:00', 'connections' => 0],
            ['hour' => '22:00:00', 'connections' => 0],
            ['hour' => '23:00:00', 'connections' => 0],
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
