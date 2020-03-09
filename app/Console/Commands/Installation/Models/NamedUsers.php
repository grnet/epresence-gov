<?php

namespace App\Console\Commands\Installation\Models;

use App\NamedUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NamedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:named_users';

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
        Db::table('named_users')->truncate();
        NamedUser::create(['email'=>'nameduserdev7@zoom.epresence.grnet.gr','zoom_id'=>'dVuKQXi_SJ2j_5Y5-nl6fQ','type'=>'conferences']);
        NamedUser::create(['email'=>'nameduserdev8@zoom.epresence.grnet.gr','zoom_id'=>'SJQggoArQSmfPeAcwKrTlg','type'=>'conferences']);
        NamedUser::create(['email'=>'nameduserdev9@zoom.epresence.grnet.gr','zoom_id'=>'mNzyOb4RTheBP1Yg9F08Rg','type'=>'conferences']);
        Schema::enableForeignKeyConstraints();

    }
}
