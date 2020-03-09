<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateDemoRoomJoinUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:demo-join-urls';

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
     *'demo_room_join_urls (user_id)'
     * @return mixed
     */
    public function handle()
    {
        Log::info("Migrating demo_room_join_urls from new (zoom) platform...");
        Log::info("\n");
        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('demo_room_join_urls')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();
        $demo_join_urls  = DB::connection('zoom')->table('demo_room_join_urls')->get();
        foreach ($demo_join_urls as $url) {
            unset($url->id);
            $user_relation = DB::connection('helper')->table('user_helper')->where("new_user_id", $url->user_id)->first();

            if (isset($user_relation->id)) {
                $url->user_id = $user_relation->final_user_id;
                DB::connection('result_epresence')->table('demo_room_join_urls')->insert((array)$url);
            }else{
                if(!isset($user_relation->id)){
                    Log::info("Could not find a relation for user id :".$url->user_id);
                }
            }
        }
        Log::info("Done Migrating demo_room_join_urls from new (zoom) platform!");
        Log::info("\n");
    }
}
