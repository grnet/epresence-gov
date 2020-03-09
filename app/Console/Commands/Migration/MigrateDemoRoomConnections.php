<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateDemoRoomConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:demo-connections';

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
     *'demo_room_connections (user_id)',  make a sum of total connections from two platforms and keep last_month_connections from new platform
     * @return mixed
     */
    public function handle()
    {
        Log::info("Migrating demo_room_connections...");
        Log::info("\n");
        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('demo_room_connections')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $old_demo_connections = DB::connection('vidyo')->table('demo_room_connections')->get();
        foreach ($old_demo_connections as $connection) {
            $user_relation = DB::connection('helper')->table('user_helper')->where("old_user_id", $connection->user_id)->first();
            if (isset($user_relation->id)) {
                $connection->user_id = $user_relation->final_user_id;
                DB::connection('result_epresence')->table('demo_room_connections')->insert((array)$connection);
            }else{
                    $old_user = DB::connection('vidyo')->table('users')->where("id",$connection->user_id)->first();
                    if($old_user->confirmed == 1 && $old_user->deleted == 0){
                        Log::info("Could not find a relation for old user id :".$connection->user_id);
                    }
            }
        }

        $new_demo_connections = DB::connection('zoom')->table('demo_room_connections')->get();
        foreach ($new_demo_connections as $connection) {
            $user_relation = DB::connection('helper')->table('user_helper')->where("new_user_id", $connection->user_id)->first();
            if (isset($user_relation->id)) {
                $connection->user_id = $user_relation->final_user_id;
                $already_there =  DB::connection('result_epresence')->table('demo_room_connections')->where("user_id",$user_relation->final_user_id)->first();
                if(isset($already_there->user_id)){
                    DB::connection('result_epresence')
                        ->table('demo_room_connections')
                        ->where("user_id",$user_relation->final_user_id)
                        ->update([
                            "total_connections"=>$already_there->total_connections + $connection->total_connections,
                            "last_month_connections"=>$connection->last_month_connections
                        ]);
                }else{
                     DB::connection('result_epresence')->table('demo_room_connections')->insert((array)$connection);
                }
            }else{
                $new_user = DB::connection('zoom')->table('users')->where("id",$connection->user_id)->first();
                if($new_user->confirmed == 1 && $new_user->deleted == 0){
                    Log::info("Could not find a relation for new user id :".$connection->user_id);
                }
            }
        }
        Log::info("Done Migrating demo_room_connections!");
        Log::info("\n");
    }
}
