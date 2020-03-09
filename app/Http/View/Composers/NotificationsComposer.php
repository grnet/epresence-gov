<?php

namespace App\Http\View\Composers;


use App\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;


class NotificationsComposer
{

    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data['has_active_notifications'] = false;
        $data['notification'] = null;

        if (Auth::check()) {

            $data['notification'] = request()->is('conferences') || request()->is('demo-room') ?
                Notification::where('enabled', 1)->whereIn('type', ["client", "global"])->first() :
                Notification::where('enabled', 1)->where('type', "global")->first();

            if (isset($data['notification']->id) && !Cookie::get('dont_show_notification_' . $data['notification']->id)) {
                $data['has_active_notifications'] = true;
            }
        }

        //Get os of the user

        if ($data['has_active_notifications'] == true && $data['notification']->name == "zoom_client") {

            $agent = new Agent();

            switch ($agent->platform()) {
                case "Windows":
                    $params= ["download_url"=>'https://zoom.us/client/latest/ZoomInstaller.exe'];
                    break;
                case "OS X";
                    $params = ["download_url"=>'https://zoom.us/client/latest/Zoom.pkg'] ;
                    break;
                case "AndroidOS":
                    $params= ["download_url"=>'market://details?id=us.zoom.videomeetings'] ;
                    break;
                default:
                    $params= ["download_url"=>'https://zoom.us/download'] ;
                    break;
            }

            $data['notification']->{session()->get('locale').'_message'} = replace_body_parameters($data['notification']->{session()->get('locale').'_message'} ,$params);
        }

        $view->with($data);
    }
}