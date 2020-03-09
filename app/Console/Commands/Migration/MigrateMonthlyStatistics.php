<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateMonthlyStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:monthly-statistics';

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
     *'statistics_monthly add vidyo-room to h323 from old db and insert as is from new db !!and add records from new platform by hand!!'
     * @return mixed
     */
    public function handle()
    {
        Log::info("Migrating monthly statistics...");
        Log::info("\n");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();

        DB::connection('result_epresence')
            ->table('statistics_monthly')
            ->truncate();

        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $old_m_statistics = DB::connection('vidyo')
            ->table('statistics_monthly')
            ->orderBy('month', 'asc')
            ->get();

        foreach ($old_m_statistics as $statistic) {
            unset($statistic->id);
            $max_h323 = $statistic->max_h323;
            $max_v_room = $statistic->max_vidyoRoom;
            unset($statistic->max_h323);
            unset($statistic->max_vidyoRoom);
            unset($statistic->users_no_h323);

            unset($statistic->max_desktop_100);
            unset($statistic->max_desktop_70);
            unset($statistic->max_desktop_50);

            unset($statistic->max_h323_100);
            unset($statistic->max_h323_70);
            unset($statistic->max_h323_50);

            $statistic->max_h323 = $max_h323 + $max_v_room;
            DB::connection('result_epresence')->table('statistics_monthly')->insert((array)$statistic);
        }
    }
}
