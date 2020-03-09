<?php

namespace App\Console\Commands\Installation\Models;

use App\Download;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Downloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:downloads';

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
        Db::table('downloads')->truncate();

        Download::create(
            [
                'order'=>1,
                'title_el'=>'Zoom client για Windows',
                'title_en'=>'Zoom client for Windows',
                'description_el'=>'Zoom client για Windows',
                'description_en'=>'Zoom client for Windows',
                'file_path'=>'https://zoom.us/client/latest/ZoomInstaller.exe'
            ]);

        Download::create(
            [
                'order'=>2,
                'title_el'=>'Zoom client για OS X',
                'title_en'=>'Zoom client for OS X',
                'description_el'=>'Zoom client για OS X',
                'description_en'=>'Zoom client for OS X',
                'file_path'=>'https://zoom.us/client/latest/Zoom.pkg'
            ]);

        Download::create(
            [
                'order'=>3,
                'title_el'=>'Zoom client για AndroidOS',
                'title_en'=>'Zoom client for AndroidOS',
                'description_el'=>'Zoom client για AndroidOS',
                'description_en'=>'Zoom client for AndroidOS',
                'file_path'=>'market://details?id=us.zoom.videomeetings'
            ]);

        Download::create(
            [
                'order'=>4,
                'title_el'=>'Zoom client για iOS',
                'title_en'=>'Zoom client for iOS',
                'description_el'=>'Zoom client για iOS',
                'description_en'=>'Zoom client for iOS',
                'file_path'=>'https://zoom.us/download'
            ]);

        Download::create(
            [
                'order'=>5,
                'title_el'=>'Zoom client για Linux',
                'title_en'=>'Zoom client for Linux',
                'description_el'=>'Zoom client για Linux',
                'description_en'=>'Zoom client for Linux',
                'file_path'=>'https://zoom.us/download'
            ]);

        Schema::enableForeignKeyConstraints();

    }
}
