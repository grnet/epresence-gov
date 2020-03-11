<?php

namespace App\Http\Controllers;

use App\Cdr;
use App\Http\Controllers\Controller;
use App\Statistics;
use App\User;
use App\Conference;
use App\Institution;
use App\Department;
use App\Settings;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Log;
use Auth;
use App\Http\Requests;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\View;


class StatisticsController extends Controller
{
    /**
     * StatisticsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $statistics['realtime_daily'] = Statistics::realtime_daily();
        $statistics['realtime_num_of'] = Statistics::realtime_num_of();
        $statistics['realtime_users_per_room'] = Statistics::realtime_users_per_room();
        $statistics['sec_till_five_from_now'] = (Carbon::now('Europe/Athens')->diffInSeconds(Conference::timeFromNow(Carbon::now('Europe/Athens'), 5, 'add')) + 20) * 1000;

        return view('statistics.index', compact('statistics'));
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function utilization_statistics()
    {
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        }
        $statistics_results = self::get_utilization_results('active');
        return view('statistics.utilization', [
            'statistics_results' => $statistics_results,
            'total_resources'=>Settings::option('conference_totalResources')
        ]);
    }

    //Gets the information required to show old and new utilization statistics page
    /**
     * @param $type
     * @return array
     */
    private static function get_utilization_results($type){

        $table_name = $type == "former" ? 'former_utilization_statistics' : 'utilization_statistics';
        $util_prefix = $type == "former" ? 'dm' : 'host';
    
        //Creating starting point

        $first_statistic = DB::table($table_name)->orderBy('month', 'asc')->first();
        $start_year = $first_statistic ? Carbon::parse($first_statistic->month)->startOfYear() : Carbon::today()->startOfYear();
        $current_year = Carbon::today()->month > 1 ? Carbon::today()->startOfYear() : Carbon::today()->startOfYear()->subDay();

        $statistics_results = array();

        $start_date = $start_year->copy();
        $end_date = $start_date->copy()->addYear();

        while ($start_date->lte($current_year)) {

            $year_statistics = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->get();
            $statistics_results[$start_date->year]['month_statistics'] = $year_statistics;

            $total_active_days = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum('active_days');
            $total_months = count($year_statistics);

            //Calculate average year conferences

            $total_average_conference_multiply_sum = DB::table($table_name)
                ->where('month', '>=', $start_date)
                ->where('month', '<', $end_date)
                ->selectRaw('SUM(average_conferences * active_days) as total')->get();

            if ($total_active_days != 0)
                $statistics_results[$start_date->year]['average_conferences'] = number_format($total_average_conference_multiply_sum[0]->total / $total_active_days, 2);
            else
                $statistics_results[$start_date->year]['average_conferences'] = 0;


            if($type !== "former"){
                //Calculate max conferences concurrent

                $max_conferences_row = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->orderBy('max_conferences', 'desc')->first();

                $statistics_results[$start_date->year]['max_conferences'] = $max_conferences_row ? $max_conferences_row->max_conferences : 0;
                $statistics_results[$start_date->year]['max_conferences_day'] = $max_conferences_row ? $max_conferences_row->max_conferences_day : null;

                $max_dm_concurrent_connections_row = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->orderBy('max_concurrent_conferences', 'desc')->first();
                $statistics_results[$start_date->year]['max_concurrent_conferences'] = $max_dm_concurrent_connections_row ? $max_dm_concurrent_connections_row->max_concurrent_conferences : 0;
                $statistics_results[$start_date->year]['max_concurrent_conferences_day'] = $max_dm_concurrent_connections_row ? $max_dm_concurrent_connections_row->max_concurrent_conferences_day : null;

            }

            //Desktop Mobile


            //Calculate max dm connections for year

            $max_dm_connections_row = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->orderBy('max_dm_connections', 'desc')->first();

            $statistics_results[$start_date->year]['max_dm_connections'] = $max_dm_connections_row ? $max_dm_connections_row->max_dm_connections : 0;
            $statistics_results[$start_date->year]['max_dm_connections_day'] = $max_dm_connections_row ? $max_dm_connections_row->max_dm_connections_day : null;

            //Calculate max dm concurrent

            $max_dm_concurrent_connections_row = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->orderBy('max_concurrent_dm', 'desc')->first();

            $statistics_results[$start_date->year]['max_concurrent_dm'] = $max_dm_concurrent_connections_row ? $max_dm_concurrent_connections_row->max_concurrent_dm : 0;
            $statistics_results[$start_date->year]['max_concurrent_dm_day'] = $max_dm_concurrent_connections_row ? $max_dm_concurrent_connections_row->max_concurrent_dm_day : null;

            //Calculate average dm connections

            $total_average_dm_conference_mult_sum = DB::table($table_name)
                ->where('month', '>=', $start_date)
                ->where('month', '<', $end_date)
                ->selectRaw('SUM(average_dm_connections * active_days) as total')->get();

            if ($total_active_days != 0)
                $statistics_results[$start_date->year]['average_dm_connections'] = number_format($total_average_dm_conference_mult_sum[0]->total / $total_active_days, 2);
            else
                $statistics_results[$start_date->year]['average_dm_connections'] = 0;


            //H323

            //Calculate max h323 connections for year

            $max_h323_connections_row = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->orderBy('max_h323_connections', 'desc')->first();

            $statistics_results[$start_date->year]['max_h323_connections'] = $max_h323_connections_row ? $max_h323_connections_row->max_h323_connections : 0;
            $statistics_results[$start_date->year]['max_h323_connections_day'] = $max_h323_connections_row ? $max_h323_connections_row->max_h323_connections_day : null;

            //Calculate max h323 concurrent

            $max_h323_concurrent_connections_row = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->orderBy('max_concurrent_h323', 'desc')->first();

            $statistics_results[$start_date->year]['max_concurrent_h323'] = $max_h323_concurrent_connections_row ? $max_h323_concurrent_connections_row->max_concurrent_h323 : 0;
            $statistics_results[$start_date->year]['max_concurrent_h323_day'] = $max_h323_concurrent_connections_row ? $max_h323_concurrent_connections_row->max_concurrent_h323_day : null;

            //Calculate average h323 connections

            $total_average_h323_conference_mult_sum = DB::table($table_name)
                ->where('month', '>=', $start_date)
                ->where('month', '<', $end_date)
                ->selectRaw('SUM(average_h323_connections * active_days) as total')->get();

            if ($total_active_days != 0)
                $statistics_results[$start_date->year]['average_h323_connections'] = number_format($total_average_h323_conference_mult_sum[0]->total / $total_active_days, 2);
            else
                $statistics_results[$start_date->year]['average_h323_connections'] = 0;


            //Calculate dm  year utilization


            $total_dm_0_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum($util_prefix.'_cap_0');


            if ($total_months > 0)
                $statistics_results[$start_date->year][$util_prefix.'_cap_0'] = number_format($total_dm_0_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year][$util_prefix.'_cap_0'] = 0;

            $total_dm_20_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum($util_prefix.'_cap_20');


            if ($total_months > 0)
                $statistics_results[$start_date->year][$util_prefix.'_cap_20'] = number_format($total_dm_20_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year][$util_prefix.'_cap_20'] = 0;


            $total_dm_40_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum($util_prefix.'_cap_40');


            if ($total_months > 0)
                $statistics_results[$start_date->year][$util_prefix.'_cap_40'] = number_format($total_dm_40_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year][$util_prefix.'_cap_40'] = 0;


            $total_dm_60_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum($util_prefix.'_cap_60');


            if ($total_months > 0)
                $statistics_results[$start_date->year][$util_prefix.'_cap_60'] = number_format($total_dm_60_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year][$util_prefix.'_cap_60'] = 0;


            $total_dm_80_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum($util_prefix.'_cap_80');


            if ($total_months > 0)
                $statistics_results[$start_date->year][$util_prefix.'_cap_80'] = number_format($total_dm_80_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year][$util_prefix.'_cap_80'] = 0;

            //Calculate h323 year utilization

            $total_h323_0_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum('h323_cap_0');


            if ($total_months > 0)
                $statistics_results[$start_date->year]['h323_cap_0'] = number_format($total_h323_0_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year]['h323_cap_0'] = 0;


            $total_h323_20_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum('h323_cap_20');


            if ($total_months > 0)
                $statistics_results[$start_date->year]['h323_cap_20'] = number_format($total_h323_20_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year]['h323_cap_20'] = 0;


            $total_h323_40_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum('h323_cap_40');


            if ($total_months > 0)
                $statistics_results[$start_date->year]['h323_cap_40'] = number_format($total_h323_40_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year]['h323_cap_40'] = 0;


            $total_h323_60_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum('h323_cap_60');


            if ($total_months > 0)
                $statistics_results[$start_date->year]['h323_cap_60'] = number_format($total_h323_60_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year]['h323_cap_60'] = 0;


            $total_h323_80_percentage = DB::table($table_name)->where('month', '>=', $start_date)->where('month', '<', $end_date)->sum('h323_cap_80');


            if ($total_months > 0)
                $statistics_results[$start_date->year]['h323_cap_80'] = number_format($total_h323_80_percentage / $total_months, 2);
            else
                $statistics_results[$start_date->year]['h323_cap_80'] = 0;


            $start_date->addYear();
            $end_date->addYear();
        }


        return array_reverse($statistics_results, true);
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function report()
    {
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        }

        $periods = Array();
        $data = Array();

        $previous_year = Carbon::now()->startOfMonth()->subYear();
        $time_needle_start = $previous_year->copy();
        $time_needle_end = $time_needle_start->copy()->addMonth();

        $end_time_final = Carbon::now()->startOfMonth()->addMonth();

        while ($time_needle_end <= $end_time_final) {
            $periods[] = Carbon::parse($time_needle_start)->format('m-Y');
            $time_needle_end->addMonth();
            $time_needle_start->addMonth();
        }


        $data['periods'] = $periods;

        $data['chart_1']['all'] = Statistics::get_last_year_report_all_conferences_count();
        $data['chart_1']['test'] = Statistics::get_last_year_report_test_conferences_count();

        // Test conferences to exclude
        $test_conferences_id = Conference::whereBetween('start', [$previous_year, $end_time_final])->where(function ($query) {
            $query->where('title', 'like', '%δοκιμ%')
                ->orWhere('title', 'like', '%τεστ%')
                ->orWhere('title', 'like', '%test%')
                ->orWhere('title', 'like', '%dokim%');
        })
            ->pluck('id')->toArray();


        $data['chart_1']['elector'] = Statistics::get_last_year_report_elector_conferences_count($test_conferences_id);

        $data['chart_1']['post_graduate'] = Statistics::get_last_year_report_postgraduate_conferences_count($test_conferences_id);


//        $all_conferences = Conference::whereBetween('start', [$previous_year, $end_time_final])
//            ->orderBy('start', 'asc')
//            ->get();


        $notInConferencesOther = Conference::whereBetween('start', [$previous_year, $end_time_final])
            ->where(function ($query) {
                $query->orWhere('title', 'like', '%διδακτορ%')
                    ->orWhere('title', 'like', '%διδ%')
                    ->orWhere('title', 'like', '%phd%')
                    ->orWhere('title', 'like', '%didaktor%')
                    ->orWhere('title', 'like', '%μεταπτ%')
                    ->orWhere('title', 'like', '%προπτ%')
                    ->orWhere('title', 'like', '%metapt%')
                    ->orWhere('title', 'like', '%κλεκτορ%')
                    ->orWhere('title', 'like', '%εξελ%')
                    ->orWhere('title', 'like', '%εκλ%')
                    ->orWhere('title', 'like', '%klektor%');
            })
            ->pluck('id')->toArray();


        $conference_ids_to_exclude = array_collapse([$notInConferencesOther, $test_conferences_id]);


        $data['chart_1']['other'] = Statistics::get_last_year_report_other_conferences_count($conference_ids_to_exclude);

        $data['chart_2'] = Statistics::get_report_conferences_percentage($data['chart_1']);

        $data['chart_3'] = Statistics::get_report_conferences_distinct('institution_id');

        $data['chart_4'] = Statistics::get_report_conferences_distinct('department_id');

        $data['chart_5'] = Statistics::get_report_users_distinct('Desktop-Mobile');

        $data['chart_6'] = Statistics::get_report_users_distinct('H323');

        $data['chart_7'] = Statistics::get_report_month_max_distinct_device('Desktop-Mobile');

        $data['chart_8'] = Statistics::get_report_month_max_distinct_device('H323');

        // Statistics reports

        $initial_period_stats = Statistics::report_conferences_period_data(3, Carbon::today()->format('d-m-Y'));


        return view('statistics.report', ['data' => $data, 'initial_period_stats' => $initial_period_stats]);
    }

    /**
     * @param Request $request
     * @return false|JsonResponse|string
     */
    public function report_select_period(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'to' => 'required|date_format:d-m-Y|before:tomorrow',
            'period' => 'required|in:3,6'
        ], [
            'to.required' => 'Η ημερομηνία δεν μπορεί να είναι κενή!',
            'to.date_format' => 'Η ημερομηνία πρέπει να έχει τη μορφή ημέρα-μήνας-έτος',
            'to.before' => 'Η ημερομηνία πρέπει να είναι πριν από την αυριανή ημερομηνία',
            'period' => 'Το διάστημα δεν μπορεί να είναι κενό!',
            'period.in' => 'Το διάστημα μπορεί να είναι μόνο Τρίμηνο ή Εξάμηνο',
        ]);

        if ($validator->fails()) {
            $text = '<ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">';
            foreach (json_decode($validator->errors(), true) as $error) {
                $text .= '<li>' . $error[0] . '</li>';
            }
            $text .= '</ul>';
            return json_encode([$text]);
        }

        $initial_period_stats = Statistics::report_conferences_period_data($request->period, $request->to);

        return response()->json(View::make('statistics.text_reports_template', ['initial_period_stats' => $initial_period_stats])->render());
    }

    /**
     * @param Requests\StatisticsRequest $request
     * @return Factory|RedirectResponse|\Illuminate\View\View
     */
    public function periods(Requests\StatisticsRequest $request)
    {
        $input = $request->input();

        if (empty($input)) {
            $end = Carbon::today();
            $start = Carbon::today()->subYears(2);
            $selected_period = 'month';
        } else {
            $end = Carbon::createFromFormat('d-m-Y', $input['end'])->toDateTimeString();
            $start = Carbon::createFromFormat('d-m-Y', $input['start'])->toDateTimeString();
            $selected_period = $input['select_period'];
        }

        $start_from = Carbon::parse($start)->format('d-m-Y');
        $end_from = Carbon::parse($end)->format('d-m-Y');

        $errors = array();

        if (Carbon::parse($end)->diffInDays(Carbon::parse($start)) > 60 && $selected_period == 'day') {
            $errors [] = trans('statistics.noDailyFor2Months');
        }

        if (!empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $statistics = Statistics::where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->orderBy('created_at', 'asc')
            ->get();

        $dates = array();
        $current = $start;

        $group = array();

        if ($selected_period == 'year') {
            $group = $statistics->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y');
            })->toArray();

            while ($current <= $end) {
                $dates [] = Carbon::parse($current)->format('Y');
                $current = Carbon::parse($current)->addYear()->toDateTimeString();
            }

        } elseif ($selected_period == 'month') {
            $group = $statistics->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m-Y');
            })->toArray();

            while ($current <= $end) {
                $dates [] = Carbon::parse($current)->format('m-Y');
                $current = Carbon::parse($current)->addMonth()->toDateTimeString();
            }
        } elseif ($selected_period == 'day') {
            $group = $statistics->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('d-m-Y');
            })->toArray();

            while ($current <= $end) {
                $dates [] = Carbon::parse($current)->format('d-m-Y');
                $current = Carbon::parse($current)->addDay()->toDateTimeString();
            }
        }

        return view('statistics.periods', ['dates' => $dates, 'group' => $group, 'start_from' => $start_from, 'end_from' => $end_from, 'selected_period' => $selected_period, 'statistics' => $statistics]);
    }

    /**
     *
     */
    public function realtime_count_conferences_refresh()
    {

        $active_conferences = Conference::where('room_enabled', 1)->get();
        $conferences = 0;
        foreach ($active_conferences as $active_conference) {
            if (DB::table('conference_user')->where('conference_id', $active_conference->id)->where('in_meeting', true)->count() > 0) {
                $conferences++;
            }
        }

        echo $conferences;
    }

    /**
     *
     */
    public function realtime_count_desktop_refresh()
    {

        $conferences = Conference::where('room_enabled', 1)->get();
        $users_no_desktop = 0;

        foreach ($conferences as $conference) {

            $conf_users_no_desktop = DB::table('conference_user')->where('device', 'Desktop-Mobile')->where('conference_id', $conference->id)->where('in_meeting', true)->count();
            $users_no_desktop += $conf_users_no_desktop;
        }

        echo $users_no_desktop;
    }

    /**
     *
     */
    public function realtime_count_h323_refresh()
    {

        $conferences = Conference::where('room_enabled', 1)->get();
        $users_no_h323 = 0;

        foreach ($conferences as $conference) {
            $conf_users_no_h323 = DB::table('conference_user')->where('device', 'H323')->where('conference_id', $conference->id)->where('in_meeting', true)->count();
            $users_no_h323 += $conf_users_no_h323;
        }

        echo $users_no_h323;
    }

    /**
     * @return JsonResponse
     */
    public function realtime_users_per_room_refresh()
    {
        return response()->json(Statistics::realtime_users_per_room());
    }

    /**
     * @return false|string
     */
    public function users_daily()
    {
        $today = Carbon::today();
        $current_five_minute_id = Statistics::current_five_minute_id($today, Carbon::now('Europe/Athens'));
        if (Auth::user()->hasRole('SuperAdmin')) {
            $users = DB::table('statistics_daily')
                ->where('id', $current_five_minute_id)
                ->first();
        } else {
            $users = DB::table('statistics_daily')
                ->where('id', $current_five_minute_id)
                ->select('id', 'users_no_desktop', 'users_no_h323','conferences_no')
                ->first();
        }
        return json_encode($users);
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function demo_room()
    {

        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        }

        $total_hourly_connections = DB::table('demo_room_statistics_hourly')->sum('connections');

        $hourly_data = DB::table('demo_room_statistics_hourly')->orderBy('hour', 'asc')->get();

        $total_monthly_connections = DB::table('demo_room_statistics_monthly')->sum('connections');

        $monthly_data = DB::table('demo_room_statistics_monthly')->orderBy('month', 'desc')->get();

        $top_ten_coordinators = User::whereHas('roles', function ($query) {
            $query->where('name', 'InstitutionAdministrator');
        })->join('demo_room_connections', 'users.id', '=', 'demo_room_connections.user_id')->orderBy('demo_room_connections.total_connections', 'desc')->limit(10)->get();


        $top_ten_coordinators_last_month = User::whereHas('roles', function ($query) {
            $query->where('name', 'InstitutionAdministrator');
        })->join('demo_room_connections', 'users.id', '=', 'demo_room_connections.user_id')->orderBy('demo_room_connections.last_month_connections', 'desc')->limit(10)->get();


        $institutions = Institution::whereHas('users', function ($query) {
            $query->join('demo_room_connections', 'users.id', '=', 'demo_room_connections.user_id');
        })->get();


        foreach ($institutions as $inst) {
            $inst->total_connections_count = DB::table('demo_room_connections')->whereIn('user_id', $inst->users()->pluck('id')->toArray())->sum('total_connections');
            $inst->last_month_connections_count = DB::table('demo_room_connections')->whereIn('user_id', $inst->users()->pluck('id')->toArray())->sum('last_month_connections');
        }

        $formatted_total_ints = collect($institutions)->sortBy('total_connections_count')->reverse()->toArray();

        $inst_result = array_slice($formatted_total_ints, 0, 10);

        $formatted_monthly_ints = collect($institutions)->sortBy('last_month_connections_count')->reverse()->toArray();

        $inst_month_result = array_slice($formatted_monthly_ints, 0, 10);


        return view('statistics.demo_room', [
            'hourly_statistics_data' => $hourly_data,
            'monthly_statistics_data' => $monthly_data,
            'top_ten_coordinators_last_month' => $top_ten_coordinators_last_month,
            'top_ten_coordinators' => $top_ten_coordinators,
            'top_ten_institutions_last_month' => $inst_month_result,
            'top_ten_institutions' => $inst_result,
            'total_hourly_connections' => $total_hourly_connections,
            'total_monthly_connections' => $total_monthly_connections,
        ]);
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function personalised_statistics()
    {
        $user = Auth::user();
        //Calculate statistics start
        //Total statistics
        if ($user->hasRole('DepartmentAdministrator') || $user->hasRole('InstitutionAdministrator') || $user->hasRole('SuperAdmin')) {
            $statistics['all_time']['total_conferences_created'] = $user->conferenceAdmin()->count();
            $duration = DB::table('conferences')->where('user_id', $user->id)->selectRaw('SUM(TIMESTAMPDIFF(minute,start,end)) as total_duration')->get();
            $statistics['all_time']['total_duration'] = convertMinutesToHoursMins($duration[0]->total_duration);
        }
        $total_conferences_joined = $user->conferences()->wherePivot("joined_once", 1)->count();
        $total_conferences_invited = $user->conferences()->count();
        $total_duration_in_conferences = $user->conferences()->wherePivot("joined_once", 1)->sum("duration");
        $statistics['all_time']['total_conferences_invited'] = $total_conferences_invited;
        $statistics['all_time']['total_conferences_joined'] = $total_conferences_joined;
        $statistics['all_time']['total_duration_joined'] = convertMinutesToHoursMins(floor($total_duration_in_conferences / 60));
        $total_desktop_mobile = $user->conferences()->wherePivot("joined_once", 1)->wherePivot("device", "Desktop-Mobile")->count();
        $total_h323 = $user->conferences()->wherePivot("joined_once", 1)->wherePivot("device", "H323")->count();
        $statistics['all_time']['conferences_joined_by_type']['Desktop-Mobile'] = $total_desktop_mobile;
        $statistics['all_time']['conferences_joined_by_type']['H323'] = $total_h323;
        //Current year

        $this_year_start_date = Carbon::now()->startOfYear();

        if ($user->hasRole('DepartmentAdministrator') || $user->hasRole('InstitutionAdministrator') || $user->hasRole('SuperAdmin')) {
            $statistics['current_year']['total_conferences_created'] = $user->conferenceAdmin()->where('conferences.start', '>=', $this_year_start_date)->count();

            $duration = DB::table('conferences')
                ->where('user_id', $user->id)
                ->selectRaw('SUM(TIMESTAMPDIFF(minute,start,end)) as total_duration')
                ->where('conferences.start', '>=', $this_year_start_date)->get();

            $statistics['current_year']['total_duration'] = convertMinutesToHoursMins($duration[0]->total_duration);
        }

        $total_conferences_joined = $user->conferences()->where('conferences.start', '>=', $this_year_start_date)->wherePivot("joined_once", 1)->count();
        $total_conferences_invited = $user->conferences()->where('conferences.start', '>=', $this_year_start_date)->count();
        $total_duration_in_conferences = $user->conferences()->where('conferences.start', '>=', $this_year_start_date)->wherePivot("joined_once", 1)->sum("duration");


        $statistics['current_year']['total_conferences_invited'] = $total_conferences_invited;
        $statistics['current_year']['total_conferences_joined'] = $total_conferences_joined;
        $statistics['current_year']['total_duration_joined'] = convertMinutesToHoursMins(floor($total_duration_in_conferences / 60));

        $total_desktop_mobile = $user->conferences()->where('conferences.start', '>=', $this_year_start_date)->wherePivot("joined_once", 1)->wherePivot("device", "Desktop-Mobile")->count();
        $total_h323 = $user->conferences()->where('conferences.start', '>=', $this_year_start_date)->wherePivot("joined_once", 1)->wherePivot("device", "H323")->count();


        $statistics['current_year']['conferences_joined_by_type']['Desktop-Mobile'] = $total_desktop_mobile;
        $statistics['current_year']['conferences_joined_by_type']['H323'] = $total_h323;

        //Last Year

        $last_year_start_date = Carbon::now()->startOfYear()->subYear();
        $last_year_end_date = Carbon::now()->startOfYear();


        if ($user->hasRole('DepartmentAdministrator') || $user->hasRole('InstitutionAdministrator') || $user->hasRole('SuperAdmin')) {
            $statistics['previous_year']['total_conferences_created'] =
                $user->conferenceAdmin()
                    ->where('conferences.start', '>=', $last_year_start_date)
                    ->where('conferences.start', '<', $last_year_end_date)
                    ->count();

            $duration = DB::table('conferences')
                ->where('user_id', $user->id)
                ->selectRaw('SUM(TIMESTAMPDIFF(minute,start,end)) as total_duration')
                ->where('conferences.start', '>=', $last_year_start_date)
                ->where('conferences.start', '<', $last_year_end_date)
                ->get();

            $statistics['previous_year']['total_duration'] = convertMinutesToHoursMins($duration[0]->total_duration);
        }


        $total_conferences_joined = $user->conferences()->where('conferences.start', '>=', $last_year_start_date)->where('conferences.start', '<', $last_year_end_date)->wherePivot("joined_once", 1)->count();
        $total_conferences_invited = $user->conferences()->where('conferences.start', '>=', $last_year_start_date)->where('conferences.start', '<', $last_year_end_date)->count();
        $total_duration_in_conferences = $user->conferences()->where('conferences.start', '>=', $last_year_start_date)->where('conferences.start', '<', $last_year_end_date)->wherePivot("joined_once", 1)->sum("duration");


        $statistics['previous_year']['total_conferences_invited'] = $total_conferences_invited;
        $statistics['previous_year']['total_conferences_joined'] = $total_conferences_joined;
        $statistics['previous_year']['total_duration_joined'] = convertMinutesToHoursMins(floor($total_duration_in_conferences / 60));

        $total_desktop_mobile = $user->conferences()->where('conferences.start', '>=', $last_year_start_date)->where('conferences.start', '<', $last_year_end_date)->wherePivot("joined_once", 1)->wherePivot("device", "Desktop-Mobile")->count();
        $total_h323 = $user->conferences()->where('conferences.start', '>=', $last_year_start_date)->where('conferences.start', '<', $last_year_end_date)->wherePivot("joined_once", 1)->wherePivot("device", "H323")->count();

        $statistics['previous_year']['conferences_joined_by_type']['Desktop-Mobile'] = $total_desktop_mobile;
        $statistics['previous_year']['conferences_joined_by_type']['H323'] = $total_h323;


        //Calculate statistics end

        return view('statistics.personalized', ['user' => $user, 'statistics' => $statistics]);
    }

    /**
     * @param null $start_date_range
     * @param null $end_date_range
     */
    public static function calculate_last_month_concurrency_stats($start_date_range = null, $end_date_range = null)
    {

        if ($start_date_range == null && $end_date_range == null) {
            $start_date_range = Carbon::now()->startOfMonth()->subMonth();
            $end_date_range = Carbon::today()->startOfMonth();
        }





        //Total active days that have at least one conference in this time period / this is used to calculate averages

        $conference_days_count = DB::select("Select count(*) as total_rows FROM (SELECT NULL FROM `conferences`                         
                       where `conferences`.`start` >= :start_date_range
                       and `conferences`.`start` < :end_date_range                    
                       group by DATE(`start`)
                         ) dt", ['start_date_range' => $start_date_range->toDateTimeString(), 'end_date_range' => $end_date_range->toDateTimeString()]);

        $conference_days_count = isset($conference_days_count[0]->total_rows) ? $conference_days_count[0]->total_rows : 0;


        //Total conferences that took place in this time period


        $total_conferences = Conference::where('start', '>=', $start_date_range)->where('start', '<', $end_date_range)->count();

        $max_conferences_data = Conference::where('conferences.start', '>=', $start_date_range)->where('conferences.start', '<', $end_date_range)
            ->selectRaw('COUNT(conferences.id) as total_conferences_per_day,DATE(conferences.start) as day')
            ->groupBy(DB::raw('DATE(conferences.start)'))
            ->orderBy('total_conferences_per_day', 'desc')
            ->first();

        $max_conferences_value = isset($max_conferences_data->total_conferences_per_day) ? $max_conferences_data->total_conferences_per_day : 0;
        $max_conferences_day = isset($max_conferences_data->day) ? $max_conferences_data->day : null;


        //Total Desktop-Mobile connections made in this time period

        $dm_total_connections = DB::table('conference_user')
            ->join('conferences', 'conference_user.conference_id', '=', 'conferences.id')
            ->where('conferences.start', '>=', $start_date_range)->where('conferences.start', '<', $end_date_range)
            ->where('conference_user.device', 'Desktop-Mobile')
            ->where('conference_user.joined_once', true)->count();

        //Calculate maximum dm connections in one day in this time period

        $max_dm_connection_data = DB::table('conference_user')
            ->join('conferences', 'conference_user.conference_id', '=', 'conferences.id')
            ->where('conferences.start', '>=', $start_date_range)->where('conferences.start', '<', $end_date_range)
            ->where('conference_user.device', 'Desktop-Mobile')
            ->where('conference_user.joined_once', true)
            ->selectRaw('COUNT(conference_user.device) as total_dm_per_day,DATE(conferences.start) as day')
            ->groupBy(DB::raw('DATE(conferences.start)'))
            ->orderBy('total_dm_per_day', 'desc')
            ->first();

        $max_dm_connections_value = isset($max_dm_connection_data->total_dm_per_day) ? $max_dm_connection_data->total_dm_per_day : 0;
        $max_dm_connections_day = isset($max_dm_connection_data->day) ? $max_dm_connection_data->day : null;


        //Total H323-VidyoRoom connections made in this time period


        $h323_total_connections = DB::table('conference_user')
            ->join('conferences', 'conference_user.conference_id', '=', 'conferences.id')
            ->where('conferences.start', '>=', $start_date_range)->where('conferences.start', '<', $end_date_range)
            ->where('conference_user.device', 'H323')
            ->where('conference_user.joined_once', true)->count();


        //Calculate maximum h323/vidyoRoom connections in one day in this time period

        $max_h323_connection_data = DB::table('conference_user')
            ->join('conferences', 'conference_user.conference_id', '=', 'conferences.id')
            ->where('conferences.start', '>=', $start_date_range)->where('conferences.start', '<', $end_date_range)
            ->where('conference_user.device', 'H323')
            ->where('conference_user.joined_once', true)
            ->selectRaw('COUNT(conference_user.device) as total_h323_per_day,DATE(conferences.start) as day')
            ->groupBy(DB::raw('DATE(conferences.start)'))
            ->orderBy('total_h323_per_day', 'desc')
            ->first();

        $max_h323_connections_value = isset($max_h323_connection_data->total_h323_per_day) ? $max_h323_connection_data->total_h323_per_day : 0;
        $max_h323_connections_day = isset($max_h323_connection_data->day) ? $max_h323_connection_data->day : null;


        //Calculate max dm concurrent value/day

        $dm_cdr_rows_in_this_period = Cdr::where('join_time', '>=', $start_date_range)
            ->where('join_time', '<', $end_date_range)
            ->whereNotNull('leave_time')
            ->where('leave_time', '>=', $start_date_range)
            ->where('leave_time', '<', $end_date_range)
            ->where('device', 'Desktop-Mobile')
            ->get();


        $final_array = self::create_interval_events_array_sorted('join_time','leave_time',$dm_cdr_rows_in_this_period,false);
        $max_dm_concurrent_results = self::get_max_date_value_from_sorted_events($final_array);

        //Calculate max H323 concurrent value/day

        $h323_cdr_rows_in_this_period = Cdr::where('join_time', '>=', $start_date_range)
            ->where('join_time', '<', $end_date_range)
            ->whereNotNull('leave_time')
            ->where('leave_time', '>=', $start_date_range)
            ->where('leave_time', '<', $end_date_range)
            ->where('device', 'H323')
            ->get();


        $h323_sorted_events_array = self::create_interval_events_array_sorted('join_time','leave_time',$h323_cdr_rows_in_this_period,false);
        $max_h323_concurrent_results = self::get_max_date_value_from_sorted_events($h323_sorted_events_array);

        //Max concurrent conferences

        $conferences = Conference::where('start', '>=', $start_date_range)->where('start', '<', $end_date_range)->whereNotNull('end')->where('end', '>=', $start_date_range)->where('end', '<', $end_date_range)->get();

        $conferences_sorted_events_array = self::create_interval_events_array_sorted('start','end',$conferences,false);
        $max_conferences_concurrent_results = self::get_max_date_value_from_sorted_events($conferences_sorted_events_array);

        if($conference_days_count > 0) {
            $average_conferences = number_format($total_conferences / $conference_days_count, 2);
            $average_dm_connections = number_format($dm_total_connections / $conference_days_count, 2);
            $average_h323_connections = number_format($h323_total_connections / $conference_days_count, 2);
        } else {
            $average_conferences = 0;
            $average_dm_connections = 0;
            $average_h323_connections = 0;
        }

        DB::table('utilization_statistics')->insert([
            [
                'month' => $end_date_range->copy()->subDay()->toDateString(),
                'active_days' => $conference_days_count,
                'average_conferences' => $average_conferences,
                'max_concurrent_conferences'=>$max_conferences_concurrent_results['value'],
                'max_concurrent_conferences_day'=>$max_conferences_concurrent_results['date'],
                'max_conferences'=>$max_conferences_value,
                'max_conferences_day'=>$max_conferences_day,
                'average_dm_connections' => $average_dm_connections,
                'max_dm_connections' => $max_dm_connections_value,
                'max_dm_connections_day' => $max_dm_connections_day,
                'max_concurrent_dm' => $max_dm_concurrent_results['value'],
                'max_concurrent_dm_day' => $max_dm_concurrent_results['date'],
                'average_h323_connections' => $average_h323_connections,
                'max_h323_connections' => $max_h323_connections_value,
                'max_h323_connections_day' => $max_h323_connections_day,
                'max_concurrent_h323' => $max_h323_concurrent_results['value'],
                'max_concurrent_h323_day' => $max_h323_concurrent_results['date'],
                'host_cap_0' => 0,
                'host_cap_20' => 0,
                'host_cap_40' => 0,
                'host_cap_60' => 0,
                'host_cap_80' => 0,
                'h323_cap_0' => 0,
                'h323_cap_20' => 0,
                'h323_cap_40' => 0,
                'h323_cap_60' => 0,
                'h323_cap_80' => 0,
                'host_resources' => 0,
                'h323_resources' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }

    /**
     * @param null $start_date_range
     * @param null $end_date_range
     */
    public static function calculate_last_month_utilization_stats($start_date_range = null, $end_date_range = null)
    {
        if ($start_date_range == null && $end_date_range == null) {
            $start_date_range = Carbon::now()->startOfMonth()->subMonth();
            $end_date_range = Carbon::today()->startOfMonth();
        }

        $conferences = Conference::where('start', '>', $start_date_range)
            ->where('end', '<', $end_date_range)
            ->whereRaw("HOUR(end) >= 9")
            ->whereRaw("HOUR(start) <= 17")
            ->get();


        $h323_cdrs = Cdr::where('join_time', '>', $start_date_range)
            ->where('leave_time', '<', $end_date_range)
            ->where("device", "H323")->whereRaw("HOUR(leave_time) >= 9")
            ->whereRaw("HOUR(join_time) <= 17")
            ->get();


        $total_conference_capacity = Settings::where('title', 'conference_totalResources')->first()->option;

        $conference_events_array_sorted = self::create_interval_events_array_sorted('start', 'end', $conferences, true);
        $conference_utilization_results = self::get_util_percentage_from_sorted_events($conference_events_array_sorted, $total_conference_capacity);


        $total_h323_capacity = Settings::where('title', 'conference_H323Resources')->first()->option;

        $h323_events_array_sorted = self::create_interval_events_array_sorted('join_time', 'leave_time', $h323_cdrs, true);
        $h323_utilization_results = self::get_util_percentage_from_sorted_events($h323_events_array_sorted, $total_h323_capacity);

        DB::table('utilization_statistics')
            ->where('month', $end_date_range->copy()->subDay()->toDateString())
            ->update([
                'host_cap_0' => $conference_utilization_results[0],
                'host_cap_20' => $conference_utilization_results[1],
                'host_cap_40' => $conference_utilization_results[2],
                'host_cap_60' => $conference_utilization_results[3],
                'host_cap_80' => $conference_utilization_results[4],
                'h323_cap_0' => $h323_utilization_results[0],
                'h323_cap_20' => $h323_utilization_results[1],
                'h323_cap_40' => $h323_utilization_results[2],
                'h323_cap_60' => $h323_utilization_results[3],
                'h323_cap_80' => $h323_utilization_results[4],
                'host_resources' => $total_conference_capacity,
                'h323_resources' => $total_h323_capacity,
                'updated_at' => Carbon::now()
            ]);
    }

    //Helper functions for calculation of utilization and concurrency statistics

    /**
     * @param $start_key
     * @param $end_key
     * @param $items
     * @param bool $nine_to_five_restricted
     * @return array
     */
    public static function create_interval_events_array_sorted($start_key, $end_key, $items, $nine_to_five_restricted = false)
    {

        $events_array = [];

        foreach ($items as $item) {

            if ($nine_to_five_restricted) {

                $start_at_nine = $item->{$start_key}->copy();
                $start_at_nine->hour = 9;
                $start_at_nine->minute = 0;
                $start_at_nine->second = 0;

                $end_at_five = $item->{$end_key}->copy();
                $end_at_five->hour = 17;
                $end_at_five->minute = 0;
                $end_at_five->second = 0;

                $start_time = $item->{$start_key}->lt($start_at_nine) ? $start_at_nine->toDateTimeString() : $item->{$start_key}->toDateTimeString();
                $end_time = $item->{$end_key}->gt($end_at_five) ? $end_at_five->toDateTimeString() : $item->{$end_key}->toDateTimeString();
            } else {
                $start_time = $item->{$start_key}->toDateTimeString();
                $end_time = $item->{$end_key}->toDateTimeString();
            }

            $event_object['time'] = $start_time;
            $event_object['type'] = "started";

            $events_array[] = $event_object;

            $event_object['time'] = $end_time;
            $event_object['type'] = "ended";

            $events_array[] = $event_object;
        }

        return collect($events_array)->sortBy('time')->toArray();
    }

    /**
     * @param $events_sorted
     * @param $total_capacity
     * @return array
     */
    private static function get_util_percentage_from_sorted_events($events_sorted, $total_capacity)
    {

        $concurrent = 0;
        $last_time_checked = null;
        $current_utilization_scale = null;

        $utilization_results = [0, 0, 0, 0, 0];

        foreach ($events_sorted as $item) {

            //Adds seconds to the scale only if at least one session is active
            //Need to identify what is the active time to calculate percentage
            //Should we count the time while there are no sessions active as time belonging to scale zero ? is it 0-2 or 1-2 ?
            //add && $concurrent > 0 in the condition bellow if we only need to count the time that there is at least one session active

            if (!is_null($last_time_checked) && !is_null($current_utilization_scale) && $concurrent > 0 ) {

                $seconds_in_this_scale = $last_time_checked->diffInSeconds(Carbon::parse($item['time']));
                $utilization_results[$current_utilization_scale] += $seconds_in_this_scale;

               // Log::info("Checking from: ".$last_time_checked->toDateTimeString()." to: ".$item['time']);
               //Log::info("Adding: ".$seconds_in_this_scale." to ".$current_utilization_scale." scale!");
            }

            $current_utilization_scale = self::get_utilization_scale($concurrent, $total_capacity);
            $last_time_checked = Carbon::parse($item['time']);

            if ($item['type'] == "started")
                $concurrent++;
            else
                $concurrent--;

        }

       // Log::info("Results in seconds: ".json_encode($utilization_results));

        $total_seconds_of_util = collect($utilization_results)->sum();

        foreach ($utilization_results as $key => $util_scale) {

            if ($total_seconds_of_util != 0)
                $utilization_results[$key] = number_format(($util_scale / $total_seconds_of_util) * 100, 2);
            else
                $utilization_results[$key] = 0;

        }


        return $utilization_results;
    }

    /**
     * @param $events_sorted
     * @return mixed
     */
    public static function get_max_date_value_from_sorted_events($events_sorted){

        $max_concurrent = 0;
        $result['value'] = 0;
        $result['date'] = null;

        foreach ($events_sorted as $item) {

            if ($item['type'] == "started")
                $max_concurrent++;
            else
                $max_concurrent--;

            if ($max_concurrent > $result['value']) {
                $result['value'] = $max_concurrent;
                $result['date'] = $item['time'];
            }
        }

        return $result;
    }

    /**
     * @param $concurrent_length
     * @param $total_capacity
     * @return int|null
     */
    private static function get_utilization_scale($concurrent_length, $total_capacity)
    {
        $current_utilization_scale = null;

        if ($concurrent_length <= ($total_capacity / 100) * 20) {
            $current_utilization_scale = 0;
        }

        if ($concurrent_length > ($total_capacity / 100) * 20 && $concurrent_length <= ($total_capacity / 100) * 40) {
            $current_utilization_scale = 1;
        }

        if ($concurrent_length > ($total_capacity / 100) * 40 && $concurrent_length <= ($total_capacity / 100) * 60) {
            $current_utilization_scale = 2;
        }

        if ($concurrent_length > ($total_capacity / 100) * 60 && $concurrent_length <= ($total_capacity / 100) * 80) {
            $current_utilization_scale = 3;
        }

        if ($concurrent_length > ($total_capacity / 100) * 80) {
            $current_utilization_scale = 4;
        }

        return $current_utilization_scale;
    }
}
