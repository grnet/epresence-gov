<?php

namespace App\Console\Commands\Installation\Models;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:service_usage';

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
        Db::table('service_usage')->truncate();
        Db::table('service_usage')->insert(['option'=>'now','total_conferences'=>0,'desktop_mobile'=>0,'h323'=>0,'average_participants'=>0,'euro_saved'=>0,'updated_at'=>Carbon::now()]);
        Db::table('service_usage')->insert(['option'=>'today','total_conferences'=>0,'desktop_mobile'=>0,'h323'=>0,'average_participants'=>0,'euro_saved'=>0,'updated_at'=>Carbon::now()]);
        Db::table('service_usage')->insert(['option'=>'total','total_conferences'=>0,'desktop_mobile'=>0,'h323'=>0,'average_participants'=>0,'euro_saved'=>0,'updated_at'=>Carbon::now()]);
        Schema::enableForeignKeyConstraints();
    }
}
