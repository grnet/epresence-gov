<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Institution;
use App\Department;
use App\Conference;
use Illuminate\Support\Facades\Auth;
use DateTime;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Statistics extends Model
{
    protected $fillable = [
        'conference_id',
        'duration',
        'users_no_desktop',
        'users_no_h323',
        'institution_id',
        'department_id',
        'active'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conference()
    {
        return $this->belongsTo('App\Conference', 'conference_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo('App\Institution', 'institution_id');
    }

    /**
     * @param $dates
     * @param $group
     * @return array
     */
    public static function participant_per_conference($dates, $group)
    {
        $periods = array();
        $users_no_desktop = array();
        $i = 0;
        foreach ($dates as $date) {
            $periods[$i] = $date;
            if (array_has($group, $date)) {
                $details = $group[$date];
                $desktop = 0;
                foreach ($details as $users) {
                    $desktop += intval($users['users_no_desktop']);
                }
                $users_no_desktop[$i] = $desktop;
            } else {
                $users_no_desktop[$i] = 0;
            }
            $i++;
        }

        $json = ['periods' => $periods, 'users_no_desktop' => $users_no_desktop];
        return $json;
    }

    /**
     * @param $dates
     * @param $group
     * @return array
     */
    public static function duration_per_conference($dates, $group)
    {
        $periods = array();
        $conferences_no = array();
        $conferences_duration = array();
        $i = 0;

        foreach ($dates as $date) {

            $periods[$i] = $date;

            if (array_has($group, $date)) {
                $details = $group[$date];
                $total_conferences_no = 0;
                $total_conferences_duration = 0;

                foreach ($details as $conference) {
                    $total_conferences_no += 1;
                    $total_conferences_duration += $conference['duration'];
                }

                $conferences_no[$i] = $total_conferences_no;
                $conferences_duration[$i] = $total_conferences_duration;

            } else {
                $conferences_no[$i] = 0;
                $conferences_duration[$i] = 0;
            }

            $i++;
        }

        $json = ['periods' => $periods, 'conferences_no' => $conferences_no, 'conferences_duration' => $conferences_duration];
        return $json;
    }

    /**
     * @param $dates
     * @param $group
     * @return array
     */
    public static function conferences_duration($dates, $group)
    {
        $periods = array();
        $i = 0;

        foreach ($dates as $date) {

            $periods[$i] = $date;

            if (array_has($group, $date)) {
                $details = $group[$date];
                $one = 0;
                $two = 0;
                $three = 0;
                $four = 0;
                $conferences_duration = 0;

                foreach ($details as $conference) {
                    if ($conference['duration'] < 30) {
                        $one++;
                    } elseif ($conference['duration'] >= 30 && $conference['duration'] < 60) {
                        $two++;
                    } elseif ($conference['duration'] >= 60 && $conference['duration'] < 120) {
                        $three++;
                    } elseif ($conference['duration'] > 120) {
                        $four++;
                    }
                }
                $conferences_one[$i] = $one;
                $conferences_two[$i] = $two;
                $conferences_three[$i] = $three;
                $conferences_four[$i] = $four;
            } else {
                $conferences_one[$i] = 0;
                $conferences_two[$i] = 0;
                $conferences_three[$i] = 0;
                $conferences_four[$i] = 0;
            }

            $i++;
        }

        $conferences_one['title'] = 'mins < 30';
        $conferences_two['title'] = '30 =< mins < 60';
        $conferences_three['title'] = '60 =< mins < 120';
        $conferences_four['title'] = 'mins > 120';

        $json = ['periods' => $periods, 'conferences_one' => $conferences_one, 'conferences_two' => $conferences_two, 'conferences_three' => $conferences_three, 'conferences_four' => $conferences_four, 'graph_title' => trans('statistics.confByDuration')];
        return $json;
    }

    /**
     * @param $dates
     * @param $group
     * @return array
     */
    public static function conference_participants($dates, $group)
    {
        $periods = array();
        $conferences = array();
        $i = 0;
        foreach ($dates as $date) {
            $periods[$i] = $date;
            if (array_has($group, $date)) {
                $details = $group[$date];
                $one = 0;
                $two = 0;
                $three = 0;
                $four = 0;
                foreach ($details as $users) {
                    $conference_total_users = $users['users_no_desktop'] + $users['users_no_h323'];
                    if ($conference_total_users < 3) {
                        $one++;
                    } elseif ($conference_total_users >= 3 && $conference_total_users < 5) {
                        $two++;
                    } elseif ($conference_total_users >= 5 && $conference_total_users < 10) {
                        $three++;
                    } elseif ($conference_total_users > 10) {
                        $four++;
                    }
                }

                $conferences_one[$i] = $one;
                $conferences_two[$i] = $two;
                $conferences_three[$i] = $three;
                $conferences_four[$i] = $four;
            } else {
                $conferences_one[$i] = 0;
                $conferences_two[$i] = 0;
                $conferences_three[$i] = 0;
                $conferences_four[$i] = 0;
            }

            $i++;
        }

        $conferences_one['title'] = 'συμ < 3';
        $conferences_two['title'] = '3 =< συμ < 5';
        $conferences_three['title'] = '5 =< συμ < 10';
        $conferences_four['title'] = 'συμ > 10';

        $json = ['periods' => $periods, 'conferences_one' => $conferences_one, 'conferences_two' => $conferences_two, 'conferences_three' => $conferences_three, 'conferences_four' => $conferences_four, 'graph_title' => trans('statistics.confByParticipants')];
        return $json;
    }

    /**
     * @param $statistics
     * @return array
     */
    public static function conferences_per_institution($statistics)
    {
        $institutions = $statistics->groupBy('institution_id')->toArray();
        $total_conferences = $statistics->count();
        $j = 0;

        if ($statistics->count() == 0) {
            $institution_details = [];
            $institutions_departments = [];
        } else {

            foreach ($institutions as $institution => $conferences) {
                $institution_total_conferences = collect($conferences)->count();
                $institution_title = str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", Institution::find($conferences[0]['institution_id'])->title));

                $institution_percentage = number_format((($institution_total_conferences / $total_conferences) * 100), 2);

                $institution_details [$j] = "name: '" . $institution_title . "', y: " . $institution_percentage . ", conf: '(" . $institution_total_conferences . " " . trans('statistics.confs') . ")', drilldown: '" . $institution_title . "'";

                $departments = collect($conferences)->groupBy('department_id')->toArray();
                foreach ($departments as $department => $dep_conferences) {
                    $department_total_conferences = collect($dep_conferences)->count();
                    $department_title = str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", Department::find($dep_conferences[0]['department_id'])->title));

                    $department_percentage = number_format((($department_total_conferences / $institution_total_conferences) * 100), 2);

                    $department_details [$j][] = "'" . $department_title . "', " . $department_percentage;
                }

                $institutions_departments[$j] = "name: '" . $institution_title . "', id: '" . $institution_title . "', data: [[" . implode('], [', $department_details[$j]) . "]]";
                $j++;
            }
        }

        $json = ['institutions' => $institution_details, 'institutions_departments' => $institutions_departments];

        return $json;
    }

    /**
     * @param $statistics
     * @return array
     */
    public static function conference_duration_per_institution($statistics)
    {
        $institutions = $statistics->groupBy('institution_id')->toArray();
        $total_duration = $statistics->sum('duration');
        $j = 0;

        $institution_details = [];
        $institutions_departments = [];

        if ($statistics->count() == 0) {
            //

        } else {
            foreach ($institutions as $institution => $conferences) {
                $institution_total_duration = collect($conferences)->sum('duration');
                $institution_title = str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", Institution::find($conferences[0]['institution_id'])->title));

                if ($institution_total_duration > 0) {
                    $institution_percentage = number_format((($institution_total_duration / $total_duration) * 100), 2);
                } else {
                    $institution_percentage = 0;
                }

                $institution_details [$j] = "name: '" . $institution_title . "', y: " . $institution_percentage . ", conf: '(" . $institution_total_duration . " mins)', drilldown: '" . $institution_title . "'";

                $departments = collect($conferences)->groupBy('department_id')->toArray();
                foreach ($departments as $department => $dep_conferences) {
                    $department_total_duration = collect($dep_conferences)->sum('duration');
                    $department_title = str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", Department::find($dep_conferences[0]['department_id'])->title));

                    if ($department_total_duration > 0) {
                        $department_percentage = number_format((($department_total_duration / $institution_total_duration) * 100), 2);
                    } else {
                        $department_percentage = 0;
                    }

                    $department_details [$j][] = "'" . $department_title . "', " . $department_percentage;
                }

                $institutions_departments[$j] = "name: '" . $institution_title . "', id: '" . $institution_title . "', data: [[" . implode('], [', $department_details[$j]) . "]]";
                $j++;
            }
        }
        $json = ['institutions' => $institution_details, 'institutions_departments' => $institutions_departments];

        return $json;
    }

    /**
     * @return array
     */
    public static function realtime_daily()
    {
        $today = Carbon::today();
        $get_max_id = Statistics::current_five_minute_id($today, Carbon::now('Europe/Athens'));
        $statistics = DB::table('statistics_daily')
            ->where('id', '<=', $get_max_id)
            ->orderBy('id', 'asc')
            ->get();

        $conferences_no = array();
        $users_no_desktop = array();
        $distinct_users_no_desktop = array();
        $users_no_h323 = array();
        $distinct_users_no_h323 = array();
        $i = 0;

        foreach ($statistics as $statistic) {
            $users_no_desktop[$i] = $statistic->users_no_desktop;
            $distinct_users_no_desktop[$i] = $statistic->distinct_users_no_desktop;
            $users_no_h323[$i] = $statistic->users_no_h323;
            $distinct_users_no_h323[$i] = $statistic->distinct_users_no_h323;
            $conferences_no[$i] = $statistic->conferences_no;
            $i++;
        }

        //Default graph returns all the users until current time

        if (Auth::user()->hasRole('SuperAdmin')) {

            $json = array('year' => $today->format('Y'),
                'month' => $today->format('m') - 1,
                'day' => $today->format('d'),
                'users_no_desktop' => $users_no_desktop,
                'users_no_h323' => $users_no_h323,
                'distinct_users_no_desktop' => $distinct_users_no_desktop,
                'distinct_users_no_h323' => $distinct_users_no_h323,
                'conferences_no' => $conferences_no,
               );

        } else {

            $json = array('year' => $today->format('Y'),
                'month' => $today->format('m') - 1,
                'day' => $today->format('d'),
                'users_no_desktop' => $users_no_desktop,
                'users_no_h323' => $users_no_h323,
                'conferences_no' => $conferences_no,
               );
        }

        return $json;
    }

    /**
     * @param $today
     * @param $now
     * @return float|int
     */
    public static function current_five_minute_id($today, $now)
    {
        $now_hour = intval(Carbon::parse($now)->format('H'));
        $now_min = intval(Carbon::parse($now)->format('i'));
        $fiveMinutes = 0;

        if ($now_min >= 0 && $now_min < 5) {
            $fiveMinutes = 0;
        } elseif ($now_min >= 5 && $now_min < 10) {
            $fiveMinutes = 1;
        } elseif ($now_min >= 10 && $now_min < 15) {
            $fiveMinutes = 2;
        } elseif ($now_min >= 15 && $now_min < 20) {
            $fiveMinutes = 3;
        } elseif ($now_min >= 20 && $now_min < 25) {
            $fiveMinutes = 4;
        } elseif ($now_min >= 25 && $now_min < 30) {
            $fiveMinutes = 5;
        } elseif ($now_min >= 30 && $now_min < 35) {
            $fiveMinutes = 6;
        } elseif ($now_min >= 35 && $now_min < 40) {
            $fiveMinutes = 7;
        } elseif ($now_min >= 40 && $now_min < 45) {
            $fiveMinutes = 8;
        } elseif ($now_min >= 45 && $now_min < 50) {
            $fiveMinutes = 9;
        } elseif ($now_min >= 50 && $now_min < 55) {
            $fiveMinutes = 10;
        } elseif ($now_min >= 55 && $now_min <= 59) {
            $fiveMinutes = 11;
        }

        $current_five_minute_id = ($now_hour * 12) + $fiveMinutes;

        return $current_five_minute_id+1;
    }

    /**
     * @return array
     */
    public static function realtime_num_of()
    {
        $conferences = Conference::where('room_enabled', 1)->get();
        $conferences_count = 0;
        foreach ($conferences as $conference) {
            if (DB::table('conference_user')->where('conference_id',$conference->id)->where('in_meeting',true)->count() > 0) {
                $conferences_count++;
            }
        }

        $users_no_desktop = 0;
        $users_no_h323 = 0;

        foreach ($conferences as $conference) {

            $conf_users_no_desktop = DB::table('conference_user')->where('conference_id',$conference->id)->where('device','Desktop-Mobile')->where('in_meeting',true)->count();
            $conf_users_no_h323 = DB::table('conference_user')->where('conference_id',$conference->id)->where('device','H323')->where('in_meeting',true)->count();


            $users_no_desktop = $users_no_desktop + $conf_users_no_desktop;
            $users_no_h323 = $users_no_h323 + $conf_users_no_h323;
        }

        $json = array('conferences' => $conferences_count, 'users_no_desktop' => $users_no_desktop, 'users_no_h323' => $users_no_h323);
        return $json;
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public static function realtime_users_per_room()
    {
        $active_conferences = Conference::where('room_enabled', 1)->orderBy('id', 'asc')->get();
        $conf_details = array();
        $users_no_desktop = array();
        $users_no_h323 = array();
        $i = 0;

        foreach ($active_conferences as $active_conference) {

            $total_connected_users = DB::table('conference_user')->where('conference_id',$active_conference->id)->where('in_meeting',true)->count();

            if ($total_connected_users > 0 ) {

                $conference_users = DB::table('conference_user')->where('conference_id', $active_conference->id)->limit(30)->get();

                $total_desktop_mobile_participants = $active_conference->participants()->wherePivot('device','Desktop-Mobile')->count();
                $total_h323_participants = $active_conference->participants()->wherePivot('device','H323')->count();

                $total_users = $total_h323_participants + $total_desktop_mobile_participants;
                $title = str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", $active_conference->title));
                $institution = str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", $active_conference->institution->title)) . '-' . str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", $active_conference->department->title));
                $moderator = str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", $active_conference->user->lastname)) . ' ' . str_replace(array('"', '\''), "", preg_replace("/[\[{\(]|[\]}\)]/", "", $active_conference->user->firstname));

                $start = Carbon::parse($active_conference->start)->format('H:i');
                $end = Carbon::parse($active_conference->end)->format('H:i');

                if (strlen($title) > 90) {
                    $title = mb_substr($title, 0, 90, "utf-8") . '...';
                }

                if(Auth::user()->hasRole('SuperAdmin')){
                    $num = $active_conference->id;
                    $conf_users_collection = collect($conference_users);
                    $html = view('statistics._realtimeTooltipAdmin',
                        [
                            'active_conference'=>$active_conference,
                            'total_desktop_mobile_participants'=>$total_desktop_mobile_participants,
                            'total_h323_participants'=>$total_h323_participants,
                            'conf_users_collection'=>$conf_users_collection,
                            'title'=>$title,
                            'institution'=>$institution,
                            'moderator'=>$moderator,
                            'start'=>$start,
                            'end'=>$end,
                            'total_users'=>$total_users,
                            'total_connected_users'=>$total_connected_users,

                        ])->render();

                }else{
                    $num = $i + 1;
                    $html = view('statistics._realtimeTooltip',
                        [
                            'active_conference'=>$active_conference,
                            'title'=>$title,
                            'institution'=>$institution,
                            'moderator'=>$moderator,
                            'start'=>$start,
                            'end'=>$end,
                            'total_users'=>$total_users,
                            'total_desktop_mobile_participants'=>$total_desktop_mobile_participants,
                            'total_h323_participants'=>$total_h323_participants,
                            'total_connected_users'=>$total_connected_users,
                        ])->render();
                }

                $conf_details[$i] = [$num,$html];

                $users_no_desktop[$i] = DB::table('conference_user')->where('conference_id',$active_conference->id)->where('device','Desktop-Mobile')->where('in_meeting',true)->count();
                $users_no_h323[$i] = DB::table('conference_user')->where('conference_id',$active_conference->id)->where('device','H323')->where('in_meeting',true)->count();

                $i++;
            }
        }

        $json = array('conference_info' => $conf_details, 'users_no_desktop' => $users_no_desktop, 'users_no_h323' => $users_no_h323);

        return $json;
    }

    /**
     * @return array
     */
    public static function get_last_year_report_all_conferences_count()
    {
        $data = Array();
        $previous_year = Carbon::now()->startOfMonth()->subYear();
        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();
        while ($time_needle_end <= Carbon::now()->startOfMonth()->addMonth()) {
            $data[] = Conference::whereBetween('start', [$time_needle_start, $time_needle_end])->orderBy('start', 'asc')->count();
            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
        }
        return $data;
    }

    /**
     * @return array
     */
    public static function get_last_year_report_test_conferences_count()
    {
        $data = Array();
        $previous_year = Carbon::now()->startOfMonth()->subYear();

        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();

        while ($time_needle_end <= Carbon::now()->startOfMonth()->addMonth()) {

            $data[] = Conference::whereBetween('start', [$time_needle_start, $time_needle_end])
                ->where(function ($query) {
                    $query->where('title', 'like', '%δοκιμ%')
                        ->orWhere('title', 'like', '%τεστ%')
                        ->orWhere('title', 'like', '%test%')
                        ->orWhere('title', 'like', '%dokim%');
                })->count();

            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
        }

        return $data;
    }

    /**
     * @param $exclude_conferences
     * @return array
     */
    public static function get_last_year_report_elector_conferences_count($exclude_conferences)
    {

        $data = Array();

        $previous_year = Carbon::now()->startOfMonth()->subYear();


        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();

        while ($time_needle_end <= Carbon::now()->startOfMonth()->addMonth()) {


            $data[] = Conference::whereBetween('start', [$time_needle_start, $time_needle_end])
                ->whereNotIn('id', $exclude_conferences)
                ->where(function ($query) {
                    $query->where('title', 'like', '%κλεκτορ%')
                        ->orWhere('title', 'like', '%εξελ%')
                        ->orWhere('title', 'like', '%εκλ%')
                        ->orWhere('title', 'like', '%klektor%');
                })->count();

            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
        }
        return $data;
    }

    /**
     * @param $exclude_conferences
     * @return array
     */
    public static function get_last_year_report_other_conferences_count($exclude_conferences)
    {


        $data = Array();

        $previous_year = Carbon::now()->startOfMonth()->subYear();


        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();

        while ($time_needle_end <= Carbon::now()->startOfMonth()->addMonth()) {


            $data[] = Conference::whereBetween('start', [$time_needle_start, $time_needle_end])
                ->whereNotIn('id', $exclude_conferences)->count();


            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
        }
        return $data;
    }

    /**
     * @param $exclude_conferences
     * @return array
     */
    public static function get_last_year_report_postgraduate_conferences_count($exclude_conferences)
    {


        $data = Array();

        $previous_year = Carbon::now()->startOfMonth()->subYear();


        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();

        while ($time_needle_end <= Carbon::now()->startOfMonth()->addMonth()) {


            $data[] = $all_conferences = Conference::whereBetween('start', [$time_needle_start, $time_needle_end])
                ->whereNotIn('id', $exclude_conferences)
                ->where(function ($query) {
                    $query->where('title', 'like', '%διδακτορ%')
                        ->orWhere('title', 'like', '%διδ%')
                        ->orWhere('title', 'like', '%phd%')
                        ->orWhere('title', 'like', '%didaktor%')
                        ->orWhere('title', 'like', '%μεταπτ%')
                        ->orWhere('title', 'like', '%προπτ%')
                        ->orWhere('title', 'like', '%metapt%');
                })->count();

            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
        }
        return $data;
    }

    /**
     * @param $data
     * @return array
     */
    public static function get_report_conferences_percentage($data)
    {

        $result_data = Array();

        foreach ($data as $conference_type => $conferences) {
            foreach ($conferences as $key => $stat) {

                if ($key == 0)
                    $result_data[$conference_type][] = array('count' => $stat, 'percentage' => 0);
                else {
                    if ($result_data[$conference_type][$key - 1]['count'] == $stat) {
                        $result_data[$conference_type][] = array('count' => $stat, 'percentage' => 0);
                    } else {

                        if ($result_data[$conference_type][$key - 1]['count'] > 0)
                            $result_data[$conference_type][] = array('count' => $stat, 'percentage' => intval((($stat - $result_data[$conference_type][$key - 1]['count']) / $result_data[$conference_type][$key - 1]['count']) * 100));
                        else if ($result_data[$conference_type][$key - 1]['count'] == 0 && $stat > 0)
                            $result_data[$conference_type][] = array('count' => $stat, 'percentage' => 100);

                    }
                }
            }
        }

        return $result_data;
    }

    /**
     * @param $unique
     * @return array
     */
    public static function get_report_conferences_distinct($unique)
    {

        $data = Array();
        $previous_year = Carbon::now()->startOfMonth()->subYear();
        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();
        $i = 0;
        while ($time_needle_end <= Carbon::now()->startOfMonth()->addMonth()) {

            $current_count = Conference::whereBetween('start', [$time_needle_start, $time_needle_end])->groupBy($unique)->pluck($unique)->count();

            if ($i == 0) {
                $data['count'][] = (int)$current_count;
                $data['percentage'][] = 0;
            } else {
                if ($data['count'][$i - 1] == $current_count) {
                    $data['count'][] = (int)$current_count;
                    $data['percentage'][] = 0;
                } else {
                    if ($data['count'][$i - 1] > 0) {
                        $data['count'][] = (int)$current_count;
                        $data['percentage'][] = intval((($current_count - $data['count'][$i - 1]) / $data['count'][$i - 1]) * 100);
                    } else if ($data['count'][$i - 1] == 0 && $current_count > 0) {
                        $data['count'][] = (int)$current_count;
                        $data['percentage'][] = 100;
                    }
                }
            }
            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
            $i++;
        }
        return $data;
    }

    /**
     * @param $device
     * @return array
     */
    public static function get_report_users_distinct($device)
    {

        $data = Array();
        $previous_year = Carbon::now()->startOfMonth()->subYear();
        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();
        $i = 0;
        while ($time_needle_end <= Carbon::now()->startOfMonth()->addMonth()) {

            $current_count = Conference::whereBetween('start', [$time_needle_start, $time_needle_end])
                ->join('conference_user', 'conferences.id', '=', 'conference_user.conference_id')
                ->where('conference_user.joined_once', 1)
                ->where('conference_user.device', $device)
                ->groupBy('conference_user.user_id')
                ->pluck('conference_user.user_id')
                ->count();


            if ($i == 0) {
                $data['count'][] = (int)$current_count;
                $data['percentage'][] = 0;
            } else {
                if ($data['count'][$i - 1] == $current_count) {
                    $data['count'][] = (int)$current_count;
                    $data['percentage'][] = 0;
                } else {
                    if ($data['count'][$i - 1] > 0) {
                        $data['count'][] = (int)$current_count;
                        $data['percentage'][] = intval((($current_count - $data['count'][$i - 1]) / $data['count'][$i - 1]) * 100);
                    } else if ($data['count'][$i - 1] == 0 && $current_count > 0) {
                        $data['count'][] = (int)$current_count;
                        $data['percentage'][] = 100;
                    }
                }
            }
            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
            $i++;
        }


        return $data;
    }

    public static function get_report_month_max_distinct_device($device)
    {


        $data = Array();


        $previous_year = Carbon::now()->startOfMonth()->subYear();
        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();
        $i = 0;

        while ($time_needle_end <= Carbon::now()->startOfMonth()->addMonth()) {


            $row = DB::table('statistics_monthly')->where('month', $time_needle_start->format('Y-m-d'))->first();

            if ($row) {

                switch ($device) {
                    case 'Desktop-Mobile':
                        $data['count'][] = (int)$row->max_desktop;
                        break;
                    case 'H323':
                        $data['count'][] = (int)$row->max_h323;
                        break;
                }


                if ($i == 0)
                    $data['percentage'][] = 0;
                else {
                    if ($data['count'][$i - 1] == $data['count'][$i]) {
                        $data['percentage'][] = 0;
                    } else {
                        if ($data['count'][$i - 1] > 0) {
                            $data['percentage'][] = intval((($data['count'][$i] - $data['count'][$i - 1]) / $data['count'][$i - 1]) * 100);
                        } else if ($data['count'][$i - 1] == 0 && $data['count'][$i] > 0) {
                            $data['percentage'][] = 100;
                        }
                    }
                }
            } else {
                $data['count'][] = 0;
                $data['percentage'][] = 0;
            }

            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
            $i++;

        }

        return $data;

    }

    public static function report_conferences_period_data($months, $date)
    {

        $end_date_ds_2 = Carbon::createFromFormat('d-m-Y', $date);
        $start_date_ds_2 = $end_date_ds_2->copy()->subMonths($months);

        $start_date_ds_1 = $start_date_ds_2->copy()->subYear();
        $end_date_ds_1 = $end_date_ds_2->copy()->subYear();

        //Test conferences

        $all_count_ds_1 = Conference::whereBetween('start', [$start_date_ds_1, $end_date_ds_1])->count();

        $all_count_ds_2 = Conference::whereBetween('start', [$start_date_ds_2, $end_date_ds_2])->count();


        // Test Conferences

        $test_count_ds_1 = Conference::whereBetween('start', [$start_date_ds_1, $end_date_ds_1])
            ->where(function ($query) {
                $query->where('title', 'like', '%δοκιμ%')
                    ->orWhere('title', 'like', '%τεστ%')
                    ->orWhere('title', 'like', '%test%')
                    ->orWhere('title', 'like', '%dokim%');
            })
            ->count();

        $test_count_ds_2 = Conference::whereBetween('start', [$start_date_ds_2, $end_date_ds_2])
            ->where(function ($query) {
                $query->where('title', 'like', '%δοκιμ%')
                    ->orWhere('title', 'like', '%τεστ%')
                    ->orWhere('title', 'like', '%test%')
                    ->orWhere('title', 'like', '%dokim%');
            })
            ->count();

        // Elector conferences

        $elector_count_ds_1 = Conference::whereBetween('start', [$start_date_ds_1, $end_date_ds_1])
            ->where(function ($query) {
                $query->where('title', 'like', '%κλεκτορ%')
                    ->orWhere('title', 'like', '%εξελ%')
                    ->orWhere('title', 'like', '%εκλ%')
                    ->orWhere('title', 'like', '%klektor%');
            })
            ->where(function ($query) {
                $query->where('title', 'not like', '%δοκιμ%')
                    ->where('title', 'not like', '%τεστ%')
                    ->where('title', 'not like', '%test%')
                    ->where('title', 'not like', '%dokim%');
            })
            ->count();

        $elector_count_ds_2 = Conference::whereBetween('start', [$start_date_ds_2, $end_date_ds_2])
            ->where(function ($query) {
                $query->where('title', 'like', '%κλεκτορ%')
                    ->orWhere('title', 'like', '%εξελ%')
                    ->orWhere('title', 'like', '%εκλ%')
                    ->orWhere('title', 'like', '%klektor%');
            })
            ->where(function ($query) {
                $query->where('title', 'not like', '%δοκιμ%')
                    ->where('title', 'not like', '%τεστ%')
                    ->where('title', 'not like', '%test%')
                    ->where('title', 'not like', '%dokim%');
            })
            ->count();


        // Postgraduate/PHDs conferences


        $postgraduate_count_ds_1 = Conference::whereBetween('start', [$start_date_ds_1, $end_date_ds_1])
            ->where(function ($query) {
                $query->where('title', 'like', '%διδακτορ%')
                    ->orWhere('title', 'like', '%διδ%')
                    ->orWhere('title', 'like', '%phd%')
                    ->orWhere('title', 'like', '%didaktor%')
                    ->orWhere('title', 'like', '%μεταπτ%')
                    ->orWhere('title', 'like', '%προπτ%')
                    ->orWhere('title', 'like', '%metapt%');
            })
            ->where(function ($query) {
                $query->where('title', 'not like', '%δοκιμ%')
                    ->where('title', 'not like', '%τεστ%')
                    ->where('title', 'not like', '%test%')
                    ->where('title', 'not like', '%dokim%')
                    ->where('title', 'not like', '%κλεκτορ%')
                    ->where('title', 'not like', '%εξελ%')
                    ->where('title', 'not like', '%εκλ%')
                    ->where('title', 'not like', '%klektor%');
            })
            ->count();


        $postgraduate_count_ds_2 = Conference::whereBetween('start', [$start_date_ds_2, $end_date_ds_2])
            ->where(function ($query) {
                $query->where('title', 'like', '%διδακτορ%')
                    ->orWhere('title', 'like', '%διδ%')
                    ->orWhere('title', 'like', '%phd%')
                    ->orWhere('title', 'like', '%didaktor%')
                    ->orWhere('title', 'like', '%μεταπτ%')
                    ->orWhere('title', 'like', '%προπτ%')
                    ->orWhere('title', 'like', '%metapt%');
            })
            ->where(function ($query) {
                $query->where('title', 'not like', '%δοκιμ%')
                    ->where('title', 'not like', '%τεστ%')
                    ->where('title', 'not like', '%test%')
                    ->where('title', 'not like', '%dokim%')
                    ->where('title', 'not like', '%κλεκτορ%')
                    ->where('title', 'not like', '%εξελ%')
                    ->where('title', 'not like', '%εκλ%')
                    ->where('title', 'not like', '%klektor%');
            })
            ->count();


        $other_count_ds_1 = Conference::whereBetween('start', [$start_date_ds_1, $end_date_ds_1])
            ->where(function ($query) {
                $query->where('title', 'not like', '%δοκιμ%')
                    ->where('title', 'not like', '%τεστ%')
                    ->where('title', 'not like', '%test%')
                    ->where('title', 'not like', '%dokim%')
                    ->where('title', 'not like', '%κλεκτορ%')
                    ->where('title', 'not like', '%εξελ%')
                    ->where('title', 'not like', '%εκλ%')
                    ->where('title', 'not like', '%klektor%')
                    ->where('title', 'not like', '%διδακτορ%')
                    ->where('title', 'not like', '%διδ%')
                    ->where('title', 'not like', '%phd%')
                    ->where('title', 'not like', '%didaktor%')
                    ->where('title', 'not like', '%μεταπτ%')
                    ->where('title', 'not like', '%προπτ%')
                    ->where('title', 'not like', '%metapt%');
            })
            ->count();


        $other_count_ds_2 = Conference::whereBetween('start', [$start_date_ds_2, $end_date_ds_2])
            ->where(function ($query) {
                $query->where('title', 'not like', '%δοκιμ%')
                    ->where('title', 'not like', '%τεστ%')
                    ->where('title', 'not like', '%test%')
                    ->where('title', 'not like', '%dokim%')
                    ->where('title', 'not like', '%κλεκτορ%')
                    ->where('title', 'not like', '%εξελ%')
                    ->where('title', 'not like', '%εκλ%')
                    ->where('title', 'not like', '%klektor%')
                    ->where('title', 'not like', '%διδακτορ%')
                    ->where('title', 'not like', '%διδ%')
                    ->where('title', 'not like', '%phd%')
                    ->where('title', 'not like', '%didaktor%')
                    ->where('title', 'not like', '%μεταπτ%')
                    ->where('title', 'not like', '%προπτ%')
                    ->where('title', 'not like', '%metapt%');
            })
            ->count();


        $result['all_conferences'] = Statistics::calculate_diff_return_string($all_count_ds_1, $all_count_ds_2, $months);
        $result['postgraduate_conferences'] = Statistics::calculate_diff_return_string($postgraduate_count_ds_1, $postgraduate_count_ds_2, $months);
        $result['elector_conferences'] = Statistics::calculate_diff_return_string($elector_count_ds_1, $elector_count_ds_2, $months);
        $result['test_conferences'] = Statistics::calculate_diff_return_string($test_count_ds_1, $test_count_ds_2, $months);
        $result['other_conferences'] = Statistics::calculate_diff_return_string($other_count_ds_1, $other_count_ds_2, $months);


        // Count institutions which have moderators
        $authInstitutions = Institution::whereHas('users', function ($query) {
            $query->whereHas('roles',
                function ($query2) {
                    $query2->whereIn('name', ['InstitutionAdministrator', 'DepartmentAdministrator']);
                });
        })
            ->count();


        $result['authInstitutions'] = $authInstitutions;


        //At least 10 conferences institutions count


        $count_institutions_with_at_least_10_conferences_query_ds_1_result = DB::select("SELECT COUNT(*) as total_institutions FROM (SELECT NULL FROM `conferences`  where `start` between '" . $start_date_ds_1->toDateTimeString() . "' and '" . $end_date_ds_1->toDateTimeString() . "' GROUP BY `institution_id` HAVING count(*)>=10) t1");

        $count_institutions_with_at_least_10_conferences_ds_1 = $count_institutions_with_at_least_10_conferences_query_ds_1_result[0]->total_institutions;


        $count_institutions_with_at_least_10_conferences_query_ds_2_result = DB::select("SELECT COUNT(*) as total_institutions FROM (SELECT NULL FROM `conferences`  where `start` between '" . $start_date_ds_2->toDateTimeString() . "' and '" . $end_date_ds_2->toDateTimeString() . "' GROUP BY `institution_id` HAVING count(*)>=10) t1");

        $count_institutions_with_at_least_10_conferences_ds_2 = $count_institutions_with_at_least_10_conferences_query_ds_2_result[0]->total_institutions;


        $result['tenConferences'] = Statistics::calculate_diff_return_string($count_institutions_with_at_least_10_conferences_ds_1, $count_institutions_with_at_least_10_conferences_ds_2, $months);


        //At least 6 conferences institutions count


        $count_institutions_with_at_least_5_conferences_query_ds_1_result = DB::select("SELECT COUNT(*) as total_institutions FROM (SELECT NULL FROM `conferences`  where `start` between '" . $start_date_ds_1->toDateTimeString() . "' and '" . $end_date_ds_1->toDateTimeString() . "' GROUP BY `institution_id` HAVING count(*)>=5) t1");

        $count_institutions_with_at_least_5_conferences_ds_1 = $count_institutions_with_at_least_5_conferences_query_ds_1_result[0]->total_institutions;


        $count_institutions_with_at_least_5_conferences_query_ds_2_result = DB::select("SELECT COUNT(*) as total_institutions FROM (SELECT NULL FROM `conferences`  where `start` between '" . $start_date_ds_2->toDateTimeString() . "' and '" . $end_date_ds_2->toDateTimeString() . "' GROUP BY `institution_id` HAVING count(*)>=5) t1");

        $count_institutions_with_at_least_5_conferences_ds_2 = $count_institutions_with_at_least_5_conferences_query_ds_2_result[0]->total_institutions;


        $result['fiveConferences'] = Statistics::calculate_diff_return_string($count_institutions_with_at_least_5_conferences_ds_1, $count_institutions_with_at_least_5_conferences_ds_2, $months);

        //At least 1 conference institutions count


        $count_institutions_with_at_least_1_conferences_query_ds_1_result = DB::select("SELECT COUNT(*) as total_institutions FROM (SELECT NULL FROM `conferences`  where `start` between '" . $start_date_ds_1->toDateTimeString() . "' and '" . $end_date_ds_1->toDateTimeString() . "' GROUP BY `institution_id` HAVING count(*)>=1) t1");

        $count_institutions_with_at_least_1_conferences_ds_1 = $count_institutions_with_at_least_1_conferences_query_ds_1_result[0]->total_institutions;


        $count_institutions_with_at_least_1_conferences_query_ds_2_result = DB::select("SELECT COUNT(*) as total_institutions FROM (SELECT NULL FROM `conferences`  where `start` between '" . $start_date_ds_2->toDateTimeString() . "' and '" . $end_date_ds_2->toDateTimeString() . "' GROUP BY `institution_id` HAVING count(*)>=1) t1");

        $count_institutions_with_at_least_1_conferences_ds_2 = $count_institutions_with_at_least_1_conferences_query_ds_2_result[0]->total_institutions;

        $result['oneConferences'] = Statistics::calculate_diff_return_string($count_institutions_with_at_least_1_conferences_ds_1, $count_institutions_with_at_least_1_conferences_ds_2, $months);


        $top_ten_institutions_ds_2_by_total_conf =
            DB::table('institutions')
                ->join('conferences', 'conferences.institution_id', '=', 'institutions.id')
                ->whereBetween('conferences.start', [$start_date_ds_2, $end_date_ds_2])
                ->selectRaw('institutions.title,COUNT(conferences.id) as total_conferences')
                ->groupBy('institutions.id')
                ->orderBy('total_conferences', 'desc')
                ->limit(10)
                ->get();

        $result['sortedInstitutionsByNum'] = $top_ten_institutions_ds_2_by_total_conf;


        $top_ten_institutions_ds_2_by_total_duration =
            DB::table('institutions')
                ->join('conferences', 'conferences.institution_id', '=', 'institutions.id')
                ->whereBetween('conferences.start', [$start_date_ds_2, $end_date_ds_2])
                ->selectRaw('institutions.title,SUM(TIMESTAMPDIFF(MINUTE,conferences.start, conferences.end)) as total_duration')
                ->groupBy('institutions.id')
                ->orderBy('total_duration', 'desc')
                ->limit(10)
                ->get();

        $result['sortedInstitutionsByDuration'] = $top_ten_institutions_ds_2_by_total_duration;

        $unique_users_count_ds_1 = DB::select("SELECT COUNT(*) as total_users FROM (SELECT NULL FROM `conference_user` INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id` where `conferences`.`start` between '" . $start_date_ds_1->toDateTimeString() . "' AND '" . $end_date_ds_1->toDateTimeString() . "' AND `conference_user`.`joined_once` = 1  GROUP BY `conference_user`.`user_id`) t1");
        $unique_users_count_ds_2 = DB::select("SELECT COUNT(*) as total_users FROM (SELECT NULL FROM `conference_user` INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id` where `conferences`.`start` between '" . $start_date_ds_2->toDateTimeString() . "' AND '" . $end_date_ds_2->toDateTimeString() . "' AND `conference_user`.`joined_once` = 1  GROUP BY `conference_user`.`user_id`) t1");

        $result['textΑllUsersNow'] = Statistics::calculate_diff_return_string($unique_users_count_ds_1[0]->total_users, $unique_users_count_ds_2[0]->total_users, $months);


        $unique_desktop_users_count_ds_1 = DB::select("SELECT COUNT(*) as total_users FROM (SELECT NULL FROM `conference_user` INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id` where `conferences`.`start` between '" . $start_date_ds_1->toDateTimeString() . "' AND '" . $end_date_ds_1->toDateTimeString() . "' AND `conference_user`.`device` = 'Desktop-Mobile' AND `conference_user`.`joined_once` = 1   GROUP BY `conference_user`.`user_id`) t1");
        $unique_desktop_users_count_ds_2 = DB::select("SELECT COUNT(*) as total_users FROM (SELECT NULL FROM `conference_user` INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id` where `conferences`.`start` between '" . $start_date_ds_2->toDateTimeString() . "' AND '" . $end_date_ds_2->toDateTimeString() . "' AND `conference_user`.`device` = 'Desktop-Mobile' AND `conference_user`.`joined_once` = 1  GROUP BY `conference_user`.`user_id`) t1");


        $result['textDesktopUsers'] = Statistics::calculate_diff_return_string($unique_desktop_users_count_ds_1[0]->total_users, $unique_desktop_users_count_ds_2[0]->total_users, $months);

        $unique_h323_users_count_ds_1 = DB::select("SELECT COUNT(*) as total_users FROM (SELECT NULL FROM `conference_user` INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id` where `conferences`.`start` between '" . $start_date_ds_1->toDateTimeString() . "' AND '" . $end_date_ds_1->toDateTimeString() . "' AND `conference_user`.`device` = 'H323' AND `conference_user`.`joined_once` = 1  GROUP BY `conference_user`.`user_id`) t1");
        $unique_h323_users_count_ds_2 = DB::select("SELECT COUNT(*) as total_users FROM (SELECT NULL FROM `conference_user` INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id` where `conferences`.`start` between '" . $start_date_ds_2->toDateTimeString() . "' AND '" . $end_date_ds_2->toDateTimeString() . "' AND `conference_user`.`device` = 'H323' AND `conference_user`.`joined_once` = 1  GROUP BY `conference_user`.`user_id`) t1");


        $result['textH323Users'] = Statistics::calculate_diff_return_string($unique_h323_users_count_ds_1[0]->total_users, $unique_h323_users_count_ds_2[0]->total_users, $months);

        //AVERAGE PARTICIPANTS ALL DEVICES

        $total_participants_joined_conferences_ds_1 =
            DB::select("SELECT COUNT(*) as total_users FROM 
          (SELECT NULL FROM `conference_user` 
            INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id`
            WHERE `conferences`.`start` between '" . $start_date_ds_1->toDateTimeString() . "' AND '" . $end_date_ds_1->toDateTimeString() . "'
             AND `conference_user`.`joined_once` = 1           
             ) t1");

        if(($all_count_ds_1) !== 0)
        $average_participants_conference_ds_1 = number_format($total_participants_joined_conferences_ds_1[0]->total_users/$all_count_ds_1,2);
        else
        $average_participants_conference_ds_1 = 0;

        $total_participants_joined_conferences_ds_2 =
            DB::select("SELECT COUNT(*) as total_users FROM 
          (SELECT NULL FROM `conference_user`  
            INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id`
            WHERE `conferences`.`start` between '" . $start_date_ds_2->toDateTimeString() . "' AND '" . $end_date_ds_2->toDateTimeString() . "'
             AND `conference_user`.`joined_once` = 1         
          ) t1");

        if(($all_count_ds_2) !== 0)
            $average_participants_conference_ds_2 = number_format($total_participants_joined_conferences_ds_2[0]->total_users/$all_count_ds_2,2);
        else
            $average_participants_conference_ds_2 = 0;


        $result['average_participants_on_non_test_conference'] = Statistics::calculate_diff_return_string($average_participants_conference_ds_1,$average_participants_conference_ds_2, $months);

        //AVERAGE DESKTOP_MOBILE

        $total_conferences_with_at_least_one_invited_Desktop_mobile_participant_ds_1 =
            Conference::where(function ($query) use($start_date_ds_1,$end_date_ds_1) {
                  $query->whereBetween('start', [$start_date_ds_1, $end_date_ds_1]);
                    })->whereHas('participants',function($query){
                    $query->where('device','Desktop-Mobile');
                })->count();

        $total_Desktop_mobile_joined_conferences_ds_1 =
            DB::select("SELECT COUNT(*) as total_users FROM 
          (SELECT NULL FROM `conference_user` 
            INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id`
            WHERE `conferences`.`start` between '" . $start_date_ds_1->toDateTimeString() . "' AND '" . $end_date_ds_1->toDateTimeString() . "'
             AND `conference_user`.`joined_once` = 1
             AND `conference_user`.`device` = 'Desktop-Mobile'                   
           ) t1");

        $total_Desktop_mobile_joined_conferences_ds_1 = $total_Desktop_mobile_joined_conferences_ds_1[0]->total_users;

        if($total_conferences_with_at_least_one_invited_Desktop_mobile_participant_ds_1!==0)
        $average_desktop_mobile_conference_ds_1 = number_format($total_Desktop_mobile_joined_conferences_ds_1/$total_conferences_with_at_least_one_invited_Desktop_mobile_participant_ds_1,2);
        else
        $average_desktop_mobile_conference_ds_1 = 0;

        $total_conferences_with_at_least_one_invited_Desktop_mobile_participant_ds_2 =
            Conference::where(function ($query) use($start_date_ds_2,$end_date_ds_2) {
                $query->whereBetween('start', [$start_date_ds_2, $end_date_ds_2]);
            })->whereHas('participants',function($query){
                $query->where('device','Desktop-Mobile');
            })->count();

        $total_Desktop_mobile_joined_conferences_ds_2 =
            DB::select("SELECT COUNT(*) as total_users FROM 
          (SELECT NULL FROM `conference_user` 
            INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id`
            WHERE `conferences`.`start` between '" . $start_date_ds_2->toDateTimeString() . "' AND '" . $end_date_ds_2->toDateTimeString() . "'
             AND `conference_user`.`joined_once` = 1
             AND `conference_user`.`device` = 'Desktop-Mobile'                  
            ) t1");

        $total_Desktop_mobile_joined_conferences_ds_2 = $total_Desktop_mobile_joined_conferences_ds_2[0]->total_users;

        if($total_conferences_with_at_least_one_invited_Desktop_mobile_participant_ds_2!=0)
            $average_desktop_mobile_conference_ds_2 = number_format($total_Desktop_mobile_joined_conferences_ds_2/$total_conferences_with_at_least_one_invited_Desktop_mobile_participant_ds_2,2);
        else
            $average_desktop_mobile_conference_ds_2 = 0;

        $result['average_desktop_mobile_on_non_test_conference'] = Statistics::calculate_diff_return_string($average_desktop_mobile_conference_ds_1,$average_desktop_mobile_conference_ds_2, $months);


        //AVERAGE H323

        $total_conferences_with_at_least_one_invited_h323_participant_ds_1 =
            Conference::where(function ($query) use($start_date_ds_1,$end_date_ds_1) {
                $query->whereBetween('start', [$start_date_ds_1, $end_date_ds_1]);
            })->whereHas('participants',function($query){
                $query->where('device','H323');
            })->count();


        $total_h323_joined_conferences_ds_1 =
            DB::select("SELECT COUNT(*) as total_users FROM 
          (SELECT NULL FROM `conference_user` 
            INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id`
            WHERE `conferences`.`start` between '" . $start_date_ds_1->toDateTimeString() . "' AND '" . $end_date_ds_1->toDateTimeString() . "'
             AND `conference_user`.`joined_once` = 1
             AND `conference_user`.`device` = 'H323'                    
             ) t1");

        $total_h323_joined_conferences_ds_1 = $total_h323_joined_conferences_ds_1[0]->total_users;

        if($total_conferences_with_at_least_one_invited_h323_participant_ds_1 !==0)
        $average_h323_conference_ds_1 = number_format($total_h323_joined_conferences_ds_1/$total_conferences_with_at_least_one_invited_h323_participant_ds_1,2);
        else
        $average_h323_conference_ds_1 = 0;

        $total_conferences_with_at_least_one_invited_h323_participant_ds_2 =
            Conference::where(function ($query) use($start_date_ds_2,$end_date_ds_2) {
                $query->whereBetween('start', [$start_date_ds_2, $end_date_ds_2]);
            })->whereHas('participants',function($query){
                $query->where('device','H323');
            })->count();

        $total_h323_joined_conferences_ds_2 =
            DB::select("SELECT COUNT(*) as total_users FROM 
          (SELECT NULL FROM `conference_user` 
            INNER JOIN `conferences` ON `conferences`.`id`=`conference_user`.`conference_id`
            WHERE `conferences`.`start` between '" . $start_date_ds_2->toDateTimeString() . "' AND '" . $end_date_ds_2->toDateTimeString() . "'
             AND `conference_user`.`joined_once` = 1
             AND `conference_user`.`device` = 'H323'            
            ) t1");

        $total_h323_joined_conferences_ds_2 = $total_h323_joined_conferences_ds_2[0]->total_users;

        if($total_conferences_with_at_least_one_invited_h323_participant_ds_2!==0)
        $average_h323_conference_ds_2 = number_format($total_h323_joined_conferences_ds_2/$total_conferences_with_at_least_one_invited_h323_participant_ds_2,2);
        else
        $average_h323_conference_ds_2 = 0;

        $result['average_h323_on_non_test_conference'] = Statistics::calculate_diff_return_string($average_h323_conference_ds_1,$average_h323_conference_ds_2, $months);

        //MAX_MIN HOUR

        $minimum_time_conference_started = Conference::orderByRaw('time(start) asc')->limit(1)->first();

        $result['min_hour_start'] = isset($minimum_time_conference_started) ? Carbon::parse($minimum_time_conference_started->start)->toTimeString() : null;

        $max_time_conference_ended = Conference::orderByRaw('time(end) desc')->limit(1)->first();

        $result['max_hour_end'] =  isset($max_time_conference_ended) ? Carbon::parse($max_time_conference_ended->end)->toTimeString() : null;

        //Average duration for non test conferences

        $total_duration_ds_1 = Conference::where(function ($query) use($start_date_ds_1,$end_date_ds_1){
            $query->whereBetween('start', [$start_date_ds_1, $end_date_ds_1]);
            })->selectRaw('SUM(TIMESTAMPDIFF(MINUTE,conferences.start, conferences.end)) as total_duration')->get();

        if($all_count_ds_1 !== 0)
        $average_duration_ds_1 = $total_duration_ds_1[0]->total_duration/$all_count_ds_1;
        else
        $average_duration_ds_1 = 0;

        $total_duration_ds_2 = Conference::where(function ($query) use($start_date_ds_2,$end_date_ds_2){
            $query->whereBetween('start', [$start_date_ds_2, $end_date_ds_2]);
        })->selectRaw('SUM(TIMESTAMPDIFF(MINUTE,conferences.start, conferences.end)) as total_duration')->get();

        if(($all_count_ds_2) !== 0)
            $average_duration_ds_2 = $total_duration_ds_2[0]->total_duration/$all_count_ds_2;
        else
            $average_duration_ds_2 = 0;

        $result['average_duration'] = Statistics::calculate_diff_return_string($average_duration_ds_1,$average_duration_ds_2, $months,'duration');

        return json_encode($result);
    }

    public static function calculate_diff_return_string($value1, $value2, $period,$type=null)
    {
        if($value1!=0)
        $percentage = number_format(((($value2 - $value1) / $value1) * 100), 2);
        else
        $percentage = 100;

        if ($percentage < 0)
            $action = trans('statistics.decrease');
        else
            $action = trans('statistics.increase');

        if($type=="duration"){
            $value2 = convertMinutesToHoursMins($value2);

            if($value2['hours'] > 0 ){

                if($value2['hours']>1)
                    $value2 = $value2['hours'].' '.trans('statistics.hours').' '.trans('site.and'). ' '.$value2['minutes'].' '.trans('conferences.minutes');
                else
                    $value2 = $value2['hours'].' '.trans('site.hour').' '.trans('site.and'). ' '.$value2['minutes'].' '.trans('conferences.minutes');
            }
            else
                $value2 = $value2['minutes'].' '.trans('conferences.minutes');

        }

        return $value2 . ' (' . $action . ' ' . $percentage . '% ' . trans('statistics.compared_to_same_period_last').')';
    }


    /**
     *
     */
    public static function incrementTotalConferencesServiceUsage(){
        DB::table('service_usage')->where('option', 'total')->increment('total_conferences');
        $avg_old = DB::table('service_usage')->where('option', 'total')->value('average_participants');
        DB::table('service_usage')->where('option', 'total')->increment('euro_saved', round($avg_old / 2 * config('conferences.euro_saved')));
    }
}
