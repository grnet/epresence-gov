<?php

use App\Download;
use Illuminate\Database\Seeder;



class ZoomDownloadsSeeder extends Seeder
{
    public function run()
    {





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
    }
}
