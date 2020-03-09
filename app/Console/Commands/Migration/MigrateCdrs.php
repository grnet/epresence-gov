<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateCdrs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:cdrs';

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
     * 'cdrs (user_id,conference_id) bring from new with new conference_id & user_id',
     * @return mixed
     */
    public function handle()
    {
        Log::info("Migrating cdrs from new (zoom) platform...");
        Log::info("\n");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('cdrs')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $cdrs = DB::connection('zoom')->table('cdrs')->get();
        foreach ($cdrs as $cdr) {
            unset($cdr->id);
            $user_relation = DB::connection('helper')->table('user_helper')->where("new_user_id", $cdr->user_id)->first();
            $conference_relation = DB::connection('helper')->table('conference_helper')->where("new_conference_id", $cdr->conference_id)->first();
            if (isset($user_relation->id) && isset($conference_relation->id)) {
                $cdr->user_id = $user_relation->final_user_id;
                $cdr->conference_id = $conference_relation->final_conference_id;
                DB::connection('result_epresence')->table('cdrs')->insert((array)$cdr);
            } else {
                if (!isset($user_relation->id)) {
                    Log::info("Could not find a relation for user id :" . $cdr->user_id);
                }
                if (!isset($conference_relation->id)) {
                    Log::info("Could not find a relation for conference id :" . $cdr->conference_id);
                }
            }
        }

        Log::info("Done Migrating cdrs from new (zoom) platform!");
        Log::info("\n");
    }
}
