<?php

namespace App\Console\Commands\Installation\Models;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Roles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:roles';

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
        //Roles seeder from sql file
        Schema::disableForeignKeyConstraints();
        Db::table("roles")->truncate();
        DB::unprepared(file_get_contents(base_path('database/seeds/sql_files/roles.sql')));
        Schema::enableForeignKeyConstraints();
    }
}
