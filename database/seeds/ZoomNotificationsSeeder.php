<?php

use App\Notification;
use Illuminate\Database\Seeder;


class ZoomNotificationsSeeder extends Seeder
{
    public function run()
    {
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
    }
}
