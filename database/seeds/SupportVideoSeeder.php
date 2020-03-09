<?php

use Illuminate\Database\Seeder;
use App\Video;


class SupportVideoSeeder extends Seeder
{
    public function run()
    {
         $new_video = new Video;

        $new_video->title_en = "Institution Moderator application form (Greek Audio)";
        $new_video->title_el = "Αίτηση εκχώρησης δικαιωμάτων Συντονιστή Οργανισμού";
        $new_video->youtube_video_id = "KO0jR7ii8LQ";


        $new_video->save();

        //


        $new_video = new Video;

        $new_video->title_en = "Institution Moderator account activation (Greek Audio)";
        $new_video->title_el = "Ενεργοποίηση λογαριασμού Συντονιστή Οργανισμού";
        $new_video->youtube_video_id = "IZwP1wcjMH4";


        $new_video->save();

        //


        $new_video = new Video;

        $new_video->title_en = "Demo Room usage (Greek Audio)";
        $new_video->title_el = "Χρήση Demo Room";
        $new_video->youtube_video_id = "35QyeRmlmjw";


        $new_video->save();

        //

        $new_video = new Video;

        $new_video->title_en = "User account activation (Greek Audio)";
        $new_video->title_el = "Ενεργοποίηση λογαριασμού Χρήστη";
        $new_video->youtube_video_id = "RrOd3TBElTI";


        $new_video->save();

        //

        $new_video = new Video;

        $new_video->title_en = "How to create a videoconference (Greek Audio)";
        $new_video->title_el = "Δημιουργία Τηλεδιάσκεψης";
        $new_video->youtube_video_id = "Qh46vcaWGwY";


        $new_video->save();

        //

        $new_video = new Video;

        $new_video->title_en = "Join a videoconference – Windows Firefox (Greek Audio)";
        $new_video->title_el = "Συμμετοχή σε Τηλεδιάσκεψη - Windows Firefox";
        $new_video->youtube_video_id = "Dv4euuJm_AU";


        $new_video->save();


        //

        $new_video = new Video;

        $new_video->title_en = "Join a videoconference – Chrome (Greek Audio)";
        $new_video->title_el = "JΣυμμετοχή σε Τηλεδιάσκεψη - Chrome";
        $new_video->youtube_video_id = "ZarDH3coY6g";


        $new_video->save();


        //

        $new_video = new Video;

        $new_video->title_en = "Join a videoconference – Η323 SIP VidyoRoom (Greek Audio)";
        $new_video->title_el = "Συμμετοχή σε Τηλεδιάσκεψη - Η323 SIP VidyoRoom";
        $new_video->youtube_video_id = "e1PnpSi1veI";


        $new_video->save();


        //

        $new_video = new Video;

        $new_video->title_en = "Join a videoconference from a mobile device (Greek Audio)";
        $new_video->title_el = "Συμμετοχή σε Τηλεδιάσκεψη από κινητό";
        $new_video->youtube_video_id = "cioVjjay5v0";


        $new_video->save();


        //

        $new_video = new Video;

        $new_video->title_en = "Using Vidyo Desktop Client (Greek Audio)";
        $new_video->title_el = "Χρήση Vidyo Desktop Client";
        $new_video->youtube_video_id = "s7N22UQOEIk";


        $new_video->save();

    }
}
