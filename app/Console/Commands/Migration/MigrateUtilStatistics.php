<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateUtilStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:util-statistics';

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
     *'former_utilization_statistics bring from old db',
     * @return mixed
     */
    public function handle()
    {
        Log::info("Migrating former_utilization_statistics &  utilization_statistics...");
        Log::info("\n");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('former_utilization_statistics')
            ->truncate();
        DB::connection('result_epresence')
            ->table('utilization_statistics')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $f_util_stats = DB::connection('vidyo')
            ->table('utilization_statistics')
            ->get();

        foreach($f_util_stats as $stat){
            DB::connection('result_epresence')
                ->table('former_utilization_statistics')
                ->insert((array)$stat);
        }

        $util_stats = DB::connection('zoom')
            ->table('utilization_statistics')
            ->get();

        foreach($util_stats as $stat){
            DB::connection('result_epresence')
                ->table('utilization_statistics')
                ->insert((array)$stat);
        }

        Log::info("Migrated former_utilization_statistics &  utilization_statistics!");
        Log::info("\n");
    }
}
