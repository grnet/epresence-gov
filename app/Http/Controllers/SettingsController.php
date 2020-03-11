<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|Response|View
     */
    public function index()
    {
        // Application settings
        $settings = Settings::where('category', 'application')->get();

        return view('settings.index', compact('settings'));
    }

    /**
     * @return Factory|View
     */
    public function conferences_settings()
    {
        // Application settings

        $settings = Settings::where('category', 'conference')
            ->orWhere('category', 'application')
            ->pluck('option', 'title')
            ->toArray();


        if ($settings['maintenance_end'] < Carbon::now()->timestamp) {
            $settings['maintenance_start_date'] = null;
            $settings['maintenance_start_time'] = null;
            $settings['maintenance_end_date'] = null;
            $settings['maintenance_end_time'] = null;
            $settings['send_email_btn'] = 'off';
        } else {
            $settings['maintenance_start_date'] = Settings::getDate($settings['maintenance_start']);
            $settings['maintenance_start_time'] = Settings::getTime($settings['maintenance_start']);
            $settings['maintenance_end_date'] = Settings::getDate($settings['maintenance_end']);
            $settings['maintenance_end_time'] = Settings::getTime($settings['maintenance_end']);
            $settings['send_email_btn'] = 'on';
        }

        $now_hour = intval(Carbon::now('Europe/Athens')->format('H'));
        $now_min = intval(Carbon::now('Europe/Athens')->format('i'));
        $start_minute = '';
        $end_minute = '';

        if ($now_min >= 0 && $now_min < 15) {
            $start_minute = '00';
            $end_minute = '15';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 15 && $now_min < 30) {
            $start_minute = '15';
            $end_minute = '30';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 30 && $now_min < 45) {
            $start_minute = '30';
            $end_minute = '45';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 45 && $now_min <= 59) {
            $start_minute = '45';
            $end_minute = '00';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour + 1, 2, '0', STR_PAD_LEFT);
        }

        $settings['default_start_hour'] = $start_hour;
        $settings['default_start_min'] = $start_minute;
        $settings['default_end_hour'] = $end_hour;
        $settings['default_end_min'] = $end_minute;

        return view('conferences.settings', compact('settings'));
    }


    public function update_conferences_settings(Request $request)
    {
        $allSettings = $request->all();

        $h323_ip_detection_value = isset($allSettings['conference_EnabledH323IpDetection']) ? 1 : 0 ;
        Settings::where('title', 'conference_EnabledH323IpDetection')->update(['option' => $h323_ip_detection_value]);


        $settings = array_except($allSettings, ['emailToParticipants','conference_EnabledH323IpDetection']);

        foreach ($settings as $key => $value) {

            if (strstr($key, '_', true) == 'conference') {
                Settings::where('title', $key)->update(['option' => $value]);
            }

            Settings::update_application_settings($settings);
        }

        if (isset($allSettings['emailToParticipants'])) {
            Settings::notifyParticipants();
        }

        return redirect('/conferences/settings')->with('storesSuccessfully', trans('controllers.settingsSaved'));
    }

}
