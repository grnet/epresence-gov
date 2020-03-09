<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:statistics';

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
     *'statistics (conference_id) (department_id) merge the stats using conference relation table, add vidyo-room to h323 from old db and insert as is from new db'
     * @return mixed
     */
    public function handle()
    {
        Log::info("Migrating statistics...");
        Log::info("\n");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('statistics')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $old_statistics = DB::connection('vidyo')
            ->table('statistics')
            ->get();

        foreach($old_statistics as $statistic){
            unset($statistic->id);
            $conference_relation = DB::connection('helper')->table('conference_helper')->where("old_conference_id",$statistic->conference_id)->first();
            if(isset($conference_relation->id)){
                $total_vidyo_room = $statistic->users_no_v_room;
                $total_h323 =  $statistic->users_no_h323;
                unset($statistic->users_no_v_room);
                unset($statistic->conference_id);
                unset($statistic->users_no_h323);
                $statistic->users_no_h323 = $total_h323+$total_vidyo_room;
                $statistic->conference_id = $conference_relation->final_conference_id;
                DB::connection('result_epresence')->table('statistics')->insert((array)$statistic);
            }
        }

        $new_statistics = DB::connection('zoom')
            ->table('statistics')
            ->get();

        foreach($new_statistics as $statistic){
            unset($statistic->id);
            $conference_relation = DB::connection('helper')->table('conference_helper')->where("new_conference_id",$statistic->conference_id)->first();
            if(isset($conference_relation->id)){
//                $new_conference_id_zoom =  $statistic->conference_id;
                $statistic->conference_id = $conference_relation->final_conference_id;
                if(!DB::connection('result_epresence')->table('statistics')->where("conference_id",$conference_relation->final_conference_id)->exists()){
                    DB::connection('result_epresence')->table('statistics')->insert((array)$statistic);
                }else{
                    Log::info("Already found a statistics record for this conference new (zoom) conference_id: ".$conference_relation->final_conference_id);
                }
            }
        }
        Log::info("Migrated statistics!");
        Log::info("\n");
    }
}
