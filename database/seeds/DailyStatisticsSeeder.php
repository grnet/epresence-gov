<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DailyStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('statistics_daily')->truncate();

        for($i = 1; $i < 289; $i++){
            DB::table('statistics_daily')->insert(['created_at'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        }
    }
}
