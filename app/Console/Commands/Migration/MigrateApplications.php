<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrateApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:applications';

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
     * 'applications (user_id) bring new only and update user_id'
     * @return mixed
     */
    public function handle()
    {
        Log::info("Migrating applications from new (zoom) platform...");
        Log::info("\n");

        Schema::connection('result_epresence')->disableForeignKeyConstraints();
        DB::connection('result_epresence')
            ->table('applications')
            ->truncate();
        Schema::connection('result_epresence')->enableForeignKeyConstraints();

        $applications = DB::connection('zoom')->table('applications')->get();
        foreach ($applications as $application) {
            unset($application->id);
            if (!empty($application->user_id)) {
                $new_user_id = DB::connection('helper')->table('user_helper')->where("new_user_id", $application->user_id)->first();
                if (isset($new_user_id->id)) {
                    $application->user_id = $new_user_id->final_user_id;
                    DB::connection('result_epresence')->table('applications')->insert((array)$application);
                }
            }else{
                DB::connection('result_epresence')->table('applications')->insert((array)$application);
            }
        }

        Log::info("Done Migrating applications from new (zoom) platform!");
        Log::info("\n");

    }
}
