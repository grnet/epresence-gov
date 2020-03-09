<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateDemoRoomCdrs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:demo-cdrs';

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
     * 'demo_room_cdrs (user_id) bring from new with new user_id',
     * @return mixed
     */
    public function handle()
    {

        Log::info("Migrating demo-cdrs from new (zoom) platform...");
        Log::info("\n");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('demo_room_cdrs')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $demo_cdrs = DB::connection('zoom')->table('demo_room_cdrs')->get();
        foreach ($demo_cdrs as $cdr) {
            unset($cdr->id);
            $user_relation = DB::connection('helper')->table('user_helper')->where("new_user_id", $cdr->user_id)->first();
            if (isset($user_relation->id)) {
                $cdr->user_id = $user_relation->final_user_id;
                DB::connection('result_epresence')->table('demo_room_cdrs')->insert((array)$cdr);
            } else {
                if (!isset($user_relation->id)) {
                    Log::info("Could not find a relation for user id :" . $cdr->user_id);
                }
            }
        }

        Log::info("Done Migrating demo-cdrs from new (zoom) platform!");
        Log::info("\n");
    }
}
