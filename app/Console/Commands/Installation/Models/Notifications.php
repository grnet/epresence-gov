<?php

namespace App\Console\Commands\Installation\Models;

use App\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Notifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:notifications';

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
        Db::table('notifications')->truncate();
        Notification::create(
            [
                'name'=>'zoom_client',
                'en_title'=>'Zoom Client for Meetings',
                'el_title'=>'Zoom Client for Meetings',
                'enabled'=>true,
                'el_message'=>'Για να συμμετέχετε σε τηλεδιασκέψεις του e:Presence είναι απαραίτητo να έχετε εγκαταστήσει το <a href=":download_url">Zoom Client for Meetings</a>',
                'en_message'=>'In order to participate in e:Presence conference calls, you need to have already installed the <a href=":download_url">Zoom Client for Meetings</a>',
                'type'=>'global',
            ]);
        Schema::enableForeignKeyConstraints();

    }
}
