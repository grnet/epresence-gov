<?php

namespace App\Console\Commands\Installation\Models;

use App\NamedUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NamedUsersDev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:named_users_dev';

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
        NamedUser::create(['email'=>'nameduserdev1@zoom.epresence.grnet.gr','zoom_id'=>'kB3n7B_ZQXK4Aq-gVZg-eQ','type'=>'conferences']);
        NamedUser::create(['email'=>'nameduserdev2@zoom.epresence.grnet.gr','zoom_id'=>'JM3G8uAkTF2Rpb40anJnRw','type'=>'conferences']);
        NamedUser::create(['email'=>'nameduserdev3@zoom.epresence.grnet.gr','zoom_id'=>'FWbGdEayRD2xmeifPN5i3w','type'=>'conferences']);
        Schema::enableForeignKeyConstraints();

    }
}
