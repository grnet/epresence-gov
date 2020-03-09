<?php

namespace App\Console\Commands\Installation\Models;

use App\Institution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Institutions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:institutions';

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
        //Seed initial institutions
        Schema::disableForeignKeyConstraints();
        Db::table('institutions')->truncate();
        Institution::create(["title" => "Δημόσια Διοίκηση"]);
        Schema::enableForeignKeyConstraints();
    }
}
