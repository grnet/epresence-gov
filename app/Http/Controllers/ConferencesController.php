<?php

namespace App\Http\Controllers;

use App\Cdr;
use App\Events\ConferenceCreated;
use App\Events\ConferenceDetailsChanged;
use App\Events\ConferenceEnded;
use App\Events\ConferenceLockStatusChanged;
use App\Events\ParticipantAdded;
use App\Events\ParticipantDeviceChanged;
use App\Events\ParticipantRemoved;
use App\Events\ParticipantStatusChanged;
use App\ExtraEmail;
use App\Jobs\Conferences\EndH323IpRetrievalMeeting;
use App\NamedUser;
use Asikamiotis\ZoomApiWrapper\JiraClient;
use Firebase\JWT\JWT;
use App\Email;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Mail;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use URL;
use Validator;
use Storage;
use App\Conference;
use App\Settings;
use Carbon\Carbon;
use App\Statistics;
use App\User;
use SoapClient;
use DateTime;
use Event;
use App\Events\MobileConnectConference;
use App\Http\Requests;
use Input;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Mail\Message;
use SoapFault;

class ConferencesController extends Controller
{
    /**
     * ConferencesController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['userAcceptInvitation', 'calendar_json']]);
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function index()
    {
        if (!is_null(Session::get('previous_url'))) {
            Session::forget('previous_url');
        }

        $authenticated_user = Auth::user();

        $today = Carbon::today();

        $tomorrow = Carbon::tomorrow('Europe/Athens');

        $conferences = '';

        // Limit
        $limit = Input::get('limit') ?: 10;

        if ($authenticated_user->hasRole('SuperAdmin')) {
            $conferences_default = Conference::whereBetween('start', [$today, $tomorrow]);
            $conferences_default = Conference::advancedSearch($conferences_default, Input::all());
            $conferences = $conferences_default->paginate($limit);
        } elseif ($authenticated_user->hasRole('InstitutionAdministrator')) {
            $conferences_default = Conference::whereIn('institution_id', $authenticated_user->institutions->pluck('id')->toArray())->whereBetween('start', [$today, $tomorrow]);
            $conferences_default = Conference::advancedSearch($conferences_default, Input::all());
            $conferences = $conferences_default->paginate($limit);
        } elseif ($authenticated_user->hasRole('DepartmentAdministrator')) {
            $conferences_default = Conference::whereIn('department_id', $authenticated_user->departments->pluck('id')->toArray())->whereBetween('start', [$today, $tomorrow]);
            $conferences_default = Conference::advancedSearch($conferences_default, Input::all());
            $conferences = $conferences_default->paginate($limit);
        }

        $data['authenticated_user'] = $authenticated_user;
        $data['is_mobile'] = $authenticated_user->is_mobile();
        $data['future_conferences'] = $authenticated_user->futureConferences();
        $data['active_conferences'] = $authenticated_user->activeConferences();

        return view('conferences.index', $data, compact('conferences'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_active_conferences_container_ajax(Request $request)
    {

        if ($request->ajax()) {

            $authenticated_user = Auth::user();
            $data['authenticated_user'] = $authenticated_user;
            $data['is_mobile'] = $authenticated_user->is_mobile();
            $data['active_conferences'] = $authenticated_user->activeConferences();

            return response()->json(View::make('conferences.active_conferences_table', $data)->render());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_future_conferences_container_ajax(Request $request)
    {

        if ($request->ajax()) {

            $authenticated_user = Auth::user();

            $data['authenticated_user'] = $authenticated_user;
            $data['future_conferences'] = $authenticated_user->futureConferences();

            return response()->json(View::make('conferences.future_conferences_table', $data)->render());
        }
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function all()
    {
        if (!is_null(Session::get('previous_url'))) {
            Session::forget('previous_url');
        }

        if (Gate::denies('view_conferences')) {
            abort(403);
        }

        $authenticated_user = Auth::user();

        // Limit
        $limit = Input::get('limit') ?: 10;

        $conferences = '';

        if (($authenticated_user->hasRole('SuperAdmin')) && empty(array_except(Input::all(), ['page', 'limit']))) {
            $conferences = Conference::orderBy('id', 'desc')->paginate($limit);
        } elseif ($authenticated_user->hasRole('SuperAdmin') && !empty(array_except(Input::all(), ['page', 'limit']))) {
            $conferences_default = Conference::distinct();
            $conferences_default = Conference::advancedSearch($conferences_default, Input::all());
            $conferences = $conferences_default->paginate($limit);
        } elseif ($authenticated_user->hasRole('InstitutionAdministrator')) {
            $conferences_default = Conference::whereIn('institution_id', $authenticated_user->institutions->pluck('id')->toArray());
            $conferences_default = Conference::advancedSearch($conferences_default, Input::all());
            $conferences = $conferences_default->paginate($limit);
        } elseif ($authenticated_user->hasRole('DepartmentAdministrator')) {
            $conferences_default = Conference::whereIn('department_id', $authenticated_user->departments->pluck('id')->toArray());
            $conferences_default = Conference::advancedSearch($conferences_default, Input::all());
            $conferences = $conferences_default->paginate($limit);
        }

        $data['authenticated_user'] = $authenticated_user;
        $data['is_mobile'] = $authenticated_user->is_mobile();
        $data['future_conferences'] = $authenticated_user->futureConferences();
        $data['active_conferences'] = $authenticated_user->activeConferences();

        return view('conferences.index', $data, compact('conferences'));
        // return $conferences;
    }

    /**
     * @param $date
     * @return Factory|\Illuminate\View\View
     */
    public function conferencesOnDate($date)
    {
        if (!is_null(Session::get('previous_url'))) {
            Session::forget('previous_url');
        }

        if (Gate::denies('view_conferences')) {
            abort(403);
        }
        $authenticated_user = Auth::user();

        // Limit
        $limit = Input::get('limit') ?: 10;

        $thisDay = Carbon::createFromFormat('d-m-Y', $date)->startOfDay()->toDateTimeString();

        $nextDay = Carbon::createFromFormat('d-m-Y', $date)->endOfDay()->toDateTimeString();

        if ($authenticated_user->hasRole('SuperAdmin')) {
            $conferences_default = Conference::whereBetween('start', [$thisDay, $nextDay]);
        } elseif ($authenticated_user->hasRole('InstitutionAdministrator')) {
            $conferences_default = Conference::whereIn('institution_id', $authenticated_user->institutions->pluck('id')->toArray())->whereBetween('start', [$thisDay, $nextDay]);
        } elseif ($authenticated_user->hasRole('DepartmentAdministrator')) {
            $conferences_default = Conference::whereIn('department_id', $authenticated_user->departments->pluck('id')->toArray())->whereBetween('start', [$thisDay, $nextDay]);
        } else {
            $conference = '';
        }

        $conferences_default = Conference::advancedSearch($conferences_default, Input::all());

        $conferences = $conferences_default->paginate($limit);

        $data['authenticated_user'] = $authenticated_user;
        $data['is_mobile'] = $authenticated_user->is_mobile();
        $data['future_conferences'] = $authenticated_user->futureConferences();
        $data['active_conferences'] = $authenticated_user->activeConferences();

        return view('conferences.index', $data, compact('conferences'));
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function ongoing()
    {
        if (Gate::denies('view_conferences')) {
            abort(403);
        }

        if (Auth::user()->hasRole('SuperAdmin')) {
            $conferences = Conference::where('room_enabled', 1)
                ->get();
        } elseif (Auth::user()->hasRole('InstitutionAdministrator')) {
            $conferences = Conference::where('room_enabled', 1)
                ->whereIn('institution_id', Auth::user()->institutions->pluck('id')->toArray())
                ->get();
        } elseif (Auth::user()->hasRole('DepartmentAdministrator')) {
            $conferences = Conference::where('room_enabled', 1)
                ->whereIn('department_id', Auth::user()->departments->pluck('id')->toArray())
                ->get();
        }

        return view('conferences.ongoing', compact('conferences'));
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function create()
    {
        if (Gate::denies('create_conference')) {
            abort(403);
        }

        $now_hour = intval(Carbon::now('Europe/Athens')->format('H'));
        $now_min = intval(Carbon::now('Europe/Athens')->format('i'));
        $start_hour = '';
        $end_hour = '';
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

        $start_date = Carbon::now('Europe/Athens')->format('d-m-Y');
        $start_time = $start_hour . ':' . $start_minute;
        $end_date = Carbon::now('Europe/Athens')->format('d-m-Y');
        $end_time = $end_hour . ':' . $end_minute;
        $max_duration = Settings::option('conference_maxDuration');

        $default_values = collect(['start_date' => $start_date, 'start_time' => $start_time, 'end_date' => $end_date, 'end_time' => $end_time, 'start_minute' => $start_minute, 'end_minute' => $end_minute, 'start_hour' => $start_hour, 'end_hour' => $end_hour, 'max_duration' => $max_duration]);

        return view('conferences.create', compact('default_values'));
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function createTest()
    {
        if (Gate::denies('create_conference')) {
            abort(403);
        }

        $now_hour = intval(Carbon::now('Europe/Athens')->format('H'));
        $now_min = intval(Carbon::now('Europe/Athens')->format('i'));
        $start_minute = '';
        $end_minute = '';
        $start_hour = '';
        $end_hour = '';

        if ($now_min >= 0 && $now_min < 15) {
            $start_minute = '00';
            $end_minute = '45';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 15 && $now_min < 30) {
            $start_minute = '15';
            $end_minute = '00';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour + 1, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 30 && $now_min < 45) {
            $start_minute = '30';
            $end_minute = '15';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour + 1, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 45 && $now_min <= 59) {
            $start_minute = '45';
            $end_minute = '30';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour + 1, 2, '0', STR_PAD_LEFT);
        }

        $start_date = Carbon::now('Europe/Athens')->format('d-m-Y');
        $start_time = $start_hour . ':' . $start_minute;
        $end_date = Carbon::now('Europe/Athens')->format('d-m-Y');
        $end_time = $end_hour . ':' . $end_minute;

        $default_values = collect([
            'title' => 'Test ' . Auth::user()->institutions()->first()->title . " " . Carbon::now()->toDateTimeString(),
            'start_date' => $start_date,
            'start_time' => $start_time,
            'end_date' => $end_date,
            'end_time' => $end_time,
            'start_minute' => $start_minute,
            'end_minute' => $end_minute,
            'start_hour' => $start_hour,
            'end_hour' => $end_hour
        ]);

        return view('conferences.create-test', compact('default_values'));
    }


    /**
     * @param Requests\CreateConferenceRequest $request
     * @return RedirectResponse|Redirector
     */
    public function store(Requests\CreateConferenceRequest $request)
    {
        if (Gate::denies('create_conference')) {
            abort(403);
        }
        $input = $request->all();
        $input['created_at'] = Carbon::now();
        $input['updated_at'] = Carbon::now();
        $is_copy = isset($input['copy_of']);
        $startDateTime = $input['start_date'] . ' ' . $input['start_time'] . ':00';
        $input['start'] = Carbon::createFromFormat('d-m-Y H:i:s', $startDateTime)->toDateTimeString();
        $endDateTime = $input['end_date'] . ' ' . $input['end_time'] . ':00';
        $input['end'] = Carbon::createFromFormat('d-m-Y H:i:s', $endDateTime)->toDateTimeString();
        $start = $input['start'];
        $end = $input['end'];
        if (!$request->user()->hasRole('SuperAdmin') || !$request->has('max_duration') || !is_numeric($input['max_duration'])) {
            $input['max_duration'] = Settings::option('conference_maxDuration');
        }

        // Empty description field (Bootstrap summernote bug)
        if (empty(strip_tags($input['desc']))) {
            $input['desc'] = NULL;
        }
        if (empty(strip_tags($input['descEn']))) {
            $input['descEn'] = NULL;
        }
        if (isset($input['host_url_accessible']) && $input['host_url_accessible'] == 1 && Auth::user()->hasRole('SuperAdmin')) {
            $input['host_url_accessible'] = 1;
        } else {
            $input['host_url_accessible'] = 0;
        }
        // Moderator user_id
        $input['user_id'] = Auth::user()->id;
        $errors = Conference::hasErrors($input, 'store');
        //Field disabled
        $back_url = $is_copy ? 'conferences/' . $input['copy_of'] . '/copy' : 'conferences/create';
        if (!empty($errors)) {
            return redirect($back_url)->withErrors($errors)->withInput();
        }

        if($request->has('repeat_type') && $input['repeat_type'] !== "never"){
            if($request->has('repeat_for') && ($input['repeat_for'] > 10 || $input['repeat_for'] <= 0)){
                $errors[] = trans('conferences.repetition_times_error');
                return redirect($back_url)->withErrors($errors)->withInput();
            }
        }
        if(($request->has('repeat_for') && ($input['repeat_for'] > 0 && $input['repeat_for'] <= 10)) && ($request->has('repeat_type') && $input['repeat_type'] == "never")){
            $errors[] = trans('conferences.repetition_type_error');
            return redirect($back_url)->withErrors($errors)->withInput();
        }

        //Handle named users rotation for the zoom api
        $next_named_user_in_line = NamedUser::get_next_named_user_in_line($start, $end);
        //Checks if we found an available named user to create the conference
        if ($next_named_user_in_line == false) {
            $errors[] = trans('conferences.named_user_error');
            return redirect($back_url)->withErrors($errors)->withInput();
        }
        $input['named_user_id'] = $next_named_user_in_line->id;
        //end of handle for named users rotation
        // Create Scheduled Room ZOOM api calls start
        $create_parameters = Conference::get_zoom_create_parameters($input);
        $zoom_client = new JiraClient();
        $create_meeting_info = $zoom_client->create_meeting($create_parameters, $next_named_user_in_line->zoom_id);
        $input['join_url'] = $create_meeting_info->join_url;
        $input['start_url'] = $create_meeting_info->start_url;
        $input['zoom_meeting_id'] = $create_meeting_info->id;

        // Create Scheduled Room ZOOM api calls end
        if (isset($input['copy_of'])) {
            $copy_of = $input['copy_of'];
        }

        //Create the conference in our db
        $conference = Conference::create($input);
        if(isset($copy_of)) {
            $conference->add_participants_from_other_conference($copy_of);
            $recurrent_meetings_created = [];
            if($request->has('repeat_type') && $request->has('repeat_for')){
                if($input['repeat_type'] !== "never" && $input['repeat_for'] > 0){
                    $new_start = $input['repeat_type'] == "week" ?  Carbon::parse($input['start'])->addWeek() : Carbon::parse($input['start'])->addMonth();
                    $new_end = $input['repeat_type'] == "week" ?  Carbon::parse($input['end'])->addWeek() : Carbon::parse($input['end'])->addMonth();
                    for($i = 1; $i <= ($input['repeat_for']-1); $i++) {
                        $recurrent_meeting_input = $input;
                        //Handle named users rotation for the zoom api
                        $next_named_user_in_line = NamedUser::get_next_named_user_in_line($new_start, $new_end);
                        //Checks if we found an available named user to create the conference
                        if ($next_named_user_in_line == false) {
                            //Roll back here delete all the conferences created because on the recurrent conference creation failed
                            foreach($recurrent_meetings_created as $recurrent_conference){
                                $recurrent_conference->delete();
                            }
                            $errors[] = trans('conferences.named_user_error');
                            return redirect($back_url)->withErrors($errors)->withInput();
                        }
                        $create_recurrent_meeting_info = $zoom_client->create_meeting($create_parameters, $next_named_user_in_line->zoom_id);
                        $recurrent_meeting_input['start'] = $new_start;
                        $recurrent_meeting_input['end'] = $new_end;
                        $recurrent_meeting_input['join_url'] = $create_recurrent_meeting_info->join_url;
                        $recurrent_meeting_input['start_url'] = $create_recurrent_meeting_info->start_url;
                        $recurrent_meeting_input['zoom_meeting_id'] = $create_recurrent_meeting_info->id;
                        $recurrent_meeting_created = Conference::create($recurrent_meeting_input);
                        $recurrent_meetings_created[] = $recurrent_meeting_created;
                        $new_start = $input['repeat_type'] == "week" ?  Carbon::parse($new_start)->addWeek() : Carbon::parse($new_start)->addMonth();
                        $new_end = $input['repeat_type'] == "week" ?  Carbon::parse($new_end)->addWeek() : Carbon::parse($new_end)->addMonth();
                    }
                }
            }
            if(count($recurrent_meetings_created) > 0){
                session()->flash('storesSuccessfullyRecurrent',trans('controllers.recurrentConferenceSaved',['conferences_created'=>count($recurrent_meetings_created)+1]));
            }
            foreach($recurrent_meetings_created as $recurrent_conference){
                $recurrent_conference->add_participants_from_other_conference($copy_of);
            }
        }
        // Instant activation
        if (Carbon::now()->greaterThanOrEqualTo($start)) {
            // Add conference to statistics table
            $conference->update(['room_enabled' => 1, 'instantActivation' => 1]);
            Statistics::create(['conference_id' => $conference->id, 'institution_id' => $conference->institution_id, 'department_id' => $conference->department_id, 'active' => 1, 'created_at' => Carbon::now()]);
            Statistics::incrementTotalConferencesServiceUsage();
        }
        $type = $conference->room_enabled ? 'active' : 'future';
        event(new ConferenceCreated($conference, $type));
        return redirect('conferences/' . $conference->id . '/edit')->with('storesSuccessfully', trans('controllers.conferenceSaved') . trans('controllers.click') . '<a href="#ParticipatsBody">' . trans('controllers.here') . '</a>' . trans('controllers.toAddParticipants'));
    }

    /**
     * @param Requests\CreateTestConferenceRequest $request
     * @return RedirectResponse|Redirector
     */
    public function storeTest(Requests\CreateTestConferenceRequest $request)
    {
        if (Gate::denies('create_conference')) {
            abort(403);
        }
        $input = $request->all();

        $input['created_at'] = Carbon::now();
        $input['updated_at'] = Carbon::now();

        $now_hour = intval(Carbon::now('Europe/Athens')->format('H'));
        $now_min = intval(Carbon::now('Europe/Athens')->format('i'));
        $start_minute = '';
        $end_minute = '';

        $start_hour = '';
        $end_hour = '';

        if ($now_min >= 0 && $now_min < 15) {
            $start_minute = '00';
            $end_minute = '45';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 15 && $now_min < 30) {
            $start_minute = '15';
            $end_minute = '00';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour + 1, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 30 && $now_min < 45) {
            $start_minute = '30';
            $end_minute = '15';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour + 1, 2, '0', STR_PAD_LEFT);
        } elseif ($now_min >= 45 && $now_min <= 59) {
            $start_minute = '45';
            $end_minute = '30';
            $start_hour = str_pad($now_hour, 2, '0', STR_PAD_LEFT);
            $end_hour = str_pad($now_hour + 1, 2, '0', STR_PAD_LEFT);
        }

        $start_date = Carbon::now('Europe/Athens')->format('d-m-Y');
        $start_time = $start_hour . ':' . $start_minute;
        $end_date = Carbon::now('Europe/Athens')->format('d-m-Y');
        $end_time = $end_hour . ':' . $end_minute;

        $input['start_date'] = $start_date;
        $input['end_date'] = $end_date;
        $input['start_time'] = $start_time;
        $input['end_time'] = $end_time;

        $input['start'] = Carbon::createFromFormat('d-m-Y H:i:s', $start_date . " " . $start_time . ":00");
        $input['end'] = Carbon::createFromFormat('d-m-Y H:i:s', $end_date . " " . $end_time . ":00");

        $start = $input['start'];
        $end = $input['end'];

        // Moderator user_id

        $input['user_id'] = Auth::user()->id;
        $errors = Conference::hasErrors($input, 'store-test');

        //Field disabled

        $back_url = 'test-conferences/create';

        if (!empty($errors)) {
            return redirect($back_url)->withErrors($errors)->withInput();
        }

        //Handle named users rotation for the zoom api

        $next_named_user_in_line = NamedUser::get_next_named_user_in_line($start, $end);

        //Checks if we found an available named user to create the conference

        if ($next_named_user_in_line == false) {
            $errors[] = trans('conferences.named_user_error');
            return redirect($back_url)->withErrors($errors)->withInput();
        }

        $input['named_user_id'] = $next_named_user_in_line->id;

        //end of handle for named users rotation

        // Create Scheduled Room ZOOM api calls start

        $create_parameters = Conference::get_zoom_create_parameters($input);
        $zoom_client = new JiraClient();

        $create_meeting_info = $zoom_client->create_meeting($create_parameters, $next_named_user_in_line->zoom_id);

        if($create_meeting_info !== false){
            $input['join_url'] = $create_meeting_info->join_url;
            $input['start_url'] = $create_meeting_info->start_url;
            $input['zoom_meeting_id'] = $create_meeting_info->id;

            // Create Scheduled Room ZOOM api calls end

            //Create the conference in our db

            $input['room_enabled'] = 1;
            $input['instantActivation'] = 1;
            $input['test'] = 1;
            $conference = Conference::create($input);
            $participant = Auth::user();
            $conference->participants()->save($participant);
            $zoom_api_response = $conference->assignParticipant($participant->id, "approve");
            $join_url = isset($zoom_api_response->join_url) ? $zoom_api_response->join_url : null;
            $registrant_id = isset($zoom_api_response->registrant_id) ? $zoom_api_response->registrant_id : null;

            DB::table('conference_user')->where('conference_id', $conference->id)->where('user_id', $participant->id)
                ->update([
                    'device' => "Desktop-Mobile",
                    'join_url' => $join_url,
                    'registrant_id' => $registrant_id,
                    'enabled' => 1
                ]);

            Statistics::create(['conference_id' => $conference->id, 'institution_id' => $conference->institution_id, 'department_id' => $conference->department_id, 'active' => 1, 'created_at' => Carbon::now()]);
            Statistics::incrementTotalConferencesServiceUsage();
            event(new ConferenceCreated($conference, 'active'));
            return redirect('test-conferences/' . $conference->id . '/edit')->with('storesSuccessfully', trans('controllers.conferenceSaved') . trans('controllers.click') . '<a href="#ParticipatsBody">' . trans('controllers.here') . '</a>' . trans('controllers.toAddParticipants'));
        }

        return redirect($back_url);
    }

    /**
     * @param $id
     * @return RedirectResponse|Redirector
     */
    public function show($id)
    {

        Session::put('previous_url', URL::previous());

        return redirect("/conferences/" . $id . "/edit");
    }

    /**
     * @param $id
     * @return Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function manage($id)
    {
        $conference = Conference::findOrFail($id);

        if (Auth::user()->hasAdminAccessToConference($conference)) {

            if ($conference->room_enabled) {

                return view('conferences.manage_conference_page', compact('conference'));

                // return redirect($conference->start_url);
            } else {
                return redirect('/conferences/' . $conference->id . '/edit');
            }
        } else {
            abort(403);
        }
    }

    /**
     * @param $id
     * @return RedirectResponse|Redirector
     */
    public function join_as_host($id)
    {
        $conference = Conference::findOrFail($id);

        if (Auth::user()->hasRole('SuperAdmin') || ($conference->host_url_accessible && Auth::user()->hasAdminAccessToConference($conference))) {
            return redirect($conference->start_url);
        } else {
            abort(404);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function get_participants_table_container_ajax(Request $request, $id)
    {
        if ($request->ajax()) {
            $conference = Conference::find($id);

            if (Auth::user()->hasAdminAccessToConference($conference)) {
                return response()->json(View::make('conferences.manage_participants_table', ['conference' => $conference])->render());
            }
        }
    }


    /**
     * @param $id
     * @return Factory|\Illuminate\View\View
     */
    public function details($id)
    {
        if (Gate::denies('view_conferences')) {
            abort(403);
        }

        $conference = Conference::findOrFail($id);
        $user_institutions = Auth::user()->institutions->pluck('id')->all();
        $user_departments = Auth::user()->departments->pluck('id')->all();

        if (Auth::user()->hasRole('InstitutionAdministrator') && in_array($conference->institution_id, $user_institutions, true) == false) {
            abort(403);
        } elseif (Auth::user()->hasRole('DepartmentAdministrator') && in_array($conference->department_id, $user_departments, true) == false) {
            abort(403);
        } else {
        }

        return view('conferences.details', compact('conference'));

    }

    /**
     * @param $id
     * @return Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function edit($id)
    {
        $conference = Conference::findOrFail($id);
        $now = Carbon::now('Europe/Athens');

        if($conference->test){
            return redirect('test-conferences/' . $conference->id . '/edit');
        }

        if ($conference->end <= $now || (!empty($conference->forced) && $conference->forced <= $now)) {

            return redirect('/conferences/' . $id . '/details');

        } elseif (Auth::user()->hasAdminAccessToConference($conference)) {

            //Continue

        } else {
            abort(403);
        }

        return view('conferences.edit', compact('conference'));
    }

    /**
     * @param $id
     * @return Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function editTest($id)
    {
        $conference = Conference::findOrFail($id);
        $now = Carbon::now('Europe/Athens');

        if ($conference->end <= $now || (!empty($conference->forced) && $conference->forced <= $now)) {

            return redirect('/conferences/' . $id . '/details');

        } elseif (Auth::user()->hasAdminAccessToConference($conference)) {

            //Continue

        } else {
            abort(403);
        }

        return view('conferences.edit-test', compact('conference'));
    }

    /**
     * @param Requests\CreateConferenceRequest $request
     * @param $id
     * @return RedirectResponse|Redirector
     */
    public function update(Requests\CreateConferenceRequest $request, $id)
    {
        $conference = Conference::findOrFail($id);
        $input = $request->all();

        $input['updated_at'] = Carbon::now();

        $startDateTime = $input['start_date'] . ' ' . $input['start_time'] . ':00';
        $input['start'] = Carbon::createFromFormat('d-m-Y H:i:s', $startDateTime)->toDateTimeString();

        $endDateTime = $input['end_date'] . ' ' . $input['end_time'] . ':00';
        $input['end'] = Carbon::createFromFormat('d-m-Y H:i:s', $endDateTime)->toDateTimeString();

        $start = Carbon::createFromFormat('d-m-Y H:i:s', $startDateTime);
        $end = Carbon::createFromFormat('d-m-Y H:i:s', $endDateTime);

        // Empty description field (Bootstrap summernote bug)
        if (empty(strip_tags($input['desc']))) {
            $input['desc'] = NULL;
        }

        if (!$request->user()->hasRole('SuperAdmin') || !$request->has('max_duration') || !is_numeric($input['max_duration'])) {
            $input['max_duration'] = Settings::option('conference_maxDuration');
        }

        // Empty descriptionEN field (Bootstrap summernote bug)
        if (empty(strip_tags($input['descEn']))) {
            $input['descEn'] = NULL;
        }

        if (!isset($input['invisible'])) {
            $input['invisible'] = 0;
        }

        if (isset($input['host_url_accessible']) && $input['host_url_accessible'] == 1 && Auth::user()->hasRole('SuperAdmin')) {
            $input['host_url_accessible'] = 1;
        } else {
            $input['host_url_accessible'] = 0;
        }

        $errors = Conference::hasErrors($input, 'update', $conference->id);

        if (!empty($errors)) {
            return redirect('conferences/' . $id . '/edit')->withErrors($errors)->withInput();
        }

        // Instant activation
        if (Carbon::now()->greaterThanOrEqualTo($start) && $conference->room_enabled == 0) {
            //Add room_enabled value
            $input['room_enabled'] = 1;

            // Add conference to statistics table

            Statistics::create(['conference_id' => $conference->id, 'institution_id' => $conference->institution_id, 'department_id' => $conference->department_id, 'active' => 1, 'created_at' => Carbon::now()]);
        }

        //Check if end or title fields are updated to send Conference-details-changed event

        $notify = false;
        $dates_changed = false;

        if ($input['end'] != $conference->end) {
            $fields_updated['end'] = $end->format('H:i');
            $notify = true;
            $dates_changed = true;
        }

        if ($input['start'] != $conference->start) {
            if ((!isset($input['room_enabled']) || $input['room_enabled'] !== 1) || !$conference->room_enabled) {
                $fields_updated['start'] = $start->format('d-m-Y και ώρα H:i');
                $notify = true;
            }
            $dates_changed = true;
        }

        if ($input['title'] != $conference->title) {
            $fields_updated['title'] = $input['title'];
            $notify = true;
        }

        $already_updated = false;

        if ($dates_changed) {

            //Check if the currently named user is available for the new dates

            $colliding_conferences = $conference->named_user
                ->conferences()->where('id', '!=', $conference->id)
                ->whereNull("forced_end")
                ->where('end', '>=', $start)
                ->where('start', '<=', $end)
                ->count();

            $colliding_forced_conferences = $conference->named_user
                ->conferences()->where('id', '!=', $conference->id)
                ->whereNotNull("forced_end")
                ->where('forced_end', '>=', $start)
                ->where('start', '<=', $end)
                ->count();

            if ($colliding_conferences + $colliding_forced_conferences !== 0) {

                if ($conference->room_enabled) {

                    return redirect('conferences/' . $id . '/edit')->withErrors(['named_users' => 'Δεν υπάρχουν διαθέσιμοι πόροι για τις ημερομηνιές που διαλέξατε'])->withInput();
                } else {
                    //Try to find the next available named user to use
                    //if not found return error
                    //else recreate the conference using the new named user assigned
                    $result = $conference->assign_new_named_user($input);
                    if ($result == false) {
                        return redirect('conferences/' . $id . '/edit')->withErrors(['named_users' => 'Δεν υπάρχουν διαθέσιμοι πόροι για τις ημερομηνιές που διαλέξατε'])->withInput();
                    } else {
                        $already_updated = true;
                    }
                }
            } else {
                // If named user is not colliding do nothing
                // Log::info("Named user is still available for the new dates...doing nothing");
            }
        }

        if (!$already_updated)
            $conference->update($input);

        $update_parameters = $conference->get_zoom_update_parameters();

        $zoom_client = new JiraClient();
        $zoom_client->update_meeting($update_parameters, $conference->zoom_meeting_id);

        if (isset($fields_updated) && count($fields_updated) > 0 && $notify)
            event(new ConferenceDetailsChanged($conference, $fields_updated));

        return redirect('conferences/' . $conference->id . '/edit')->with('storesSuccessfully', trans('controllers.conferenceSaved'));
    }

    /**
     * @param Requests\CreateTestConferenceRequest $request
     * @param $id
     * @return RedirectResponse
     */
    public function updateTest(Requests\CreateTestConferenceRequest $request, $id)
    {
        $conference = Conference::findOrFail($id);
        $input = $request->all();
        $input['updated_at'] = Carbon::now();
        //Check if title field is updated to send Conference-details-changed event
        $notify = false;
        if ($input['title'] != $conference->title) {
            $fields_updated['title'] = $input['title'];
            $notify = true;
        }
        $conference->update($input);
        $update_parameters = $conference->get_zoom_update_parameters();
        $zoom_client = new JiraClient();
        $zoom_client->update_meeting($update_parameters, $conference->zoom_meeting_id);
        if (isset($fields_updated) && count($fields_updated) > 0 && $notify)
            event(new ConferenceDetailsChanged($conference, $fields_updated));

        return redirect('test-conferences/' . $conference->id . '/edit')->with('storesSuccessfully', trans('controllers.conferenceSaved'));
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function changeParticipantStatus(Request $request, $id)
    {
        $input = $request->all();
        $user_id = $input['user_id'];
        $status_requested = $input['action'];
        $conference = Conference::findOrFail($id);
        $user = User::findOrFail($user_id);
        $user_institutions = Auth::user()->institutions->pluck('id')->all();
        if (Auth::user()->hasRole('DepartmentAdministrator') && Auth::user()->owns($conference)) {
            //Continue
        } elseif (Auth::user()->hasRole('InstitutionAdministrator') && (($conference->user->hasRole('DepartmentAdministrator') || Auth::user()->owns($conference)) && in_array($conference->institution_id, $user_institutions, true))) {
            //Continue
        } elseif (Auth::user()->hasRole('SuperAdmin')) {
            //Continue
        } else {
            abort(403);
        }
        $current_participant_status = $user->participantValues($conference->id)->enabled;
        $json = array();
        $action = null;

        if ($status_requested == 0 && $current_participant_status == 1) {
            $action_required = true;
            $action = "deny";
            // Change status
            DB::table('conference_user')
                ->where('conference_id', $conference->id)
                ->where('user_id', $user->id)
                ->update(['enabled' => 0]);
            $json['status'] = 'success';
            $json['message'] = trans('controllers.participantStatusChanged');
        } elseif ($status_requested == 1 && $current_participant_status == 0) {
            $action_required = true;
            $action = "approve";
            // Change status
            DB::table('conference_user')
                ->where('conference_id', $conference->id)
                ->where('user_id', $user->id)
                ->update(['enabled' => 1]);
            $json['status'] = 'success';
            $json['message'] = trans('controllers.participantStatusChanged');
        } else {
            $action_required = false;
            $json['status'] = 'error';
            $json['message'] = 'already at this status';
        }
        if ($action_required && $user->confirmed) {
            $registrant_parameters = [
                "action" => $action,
                "registrants" => [
                    [
                        "id" => $user->participantValues($conference->id)->registrant_id,
                        "email" => "user" . $user->id . "@" . env("APP_ALIAS")
                    ]
                ]
            ];

            $zoom_client = new JiraClient();
            $zoom_client->update_participant_status($registrant_parameters, $conference->zoom_meeting_id);
            event(new ParticipantStatusChanged($conference->id, $status_requested, $user->id));
        }
        return response()->json($json);
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        $conference = Conference::findOrFail($id);
        if ((Gate::denies('delete_any_conference') && Auth::user()->owns($conference) == false) && $conference->room_enabled == 1) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotDeleteConferenceRights')
            );
            echo json_encode($results);
        } elseif ((Gate::denies('delete_any_conference') && Auth::user()->owns($conference) == false) && $conference->room_enabled == 0) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotDeleteConferenceEnded')
            );
            echo json_encode($results);
        } else {
            $type = 'future';
            if ($conference->room_enabled == 1) {
                $type = 'active';
            } elseif ($conference->room_enabled == 0 && $conference->start > Carbon::now()) {
                $conference->cancelConferenceEmail();
            }
            $participants_ids = $conference->participants()->pluck("id")->toArray();
            $admin_ids = $conference->getAdminsIds();
            event(new ConferenceEnded($conference->id, "deleted", $type, $participants_ids, $admin_ids));
            $zoom_client = new JiraClient();
            $zoom_client->delete_meeting($conference->zoom_meeting_id);
            $conference->delete();
            $results = array(
                'status' => 'success',
                'data' => trans('controllers.conferenceDeleted')
            );
            echo json_encode($results);
        }
    }

    /**
     * @param $id
     */
    public function disconnectConferenceAllParticipants($id)
    {
        $conference = Conference::findorFail($id);
        $participants_ids = $conference->participants()->pluck("id")->toArray();
        $admin_ids = $conference->getAdminsIds();
        event(new ConferenceEnded($conference->id, "forced_end", "active", $participants_ids, $admin_ids));
        $conference->endConference();
        $conference->update(['forced_end' => Carbon::now()]);
        $results = array(
            'status' => 'success',
            'data' => 'OK'
        );
        echo json_encode($results);
    }

    /**
     * @param $id
     * @return Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function copy($id)
    {
        if (Gate::denies('create_conference')) {
            abort(403);
        }
        $conference = Conference::findOrFail($id);
        if ($conference->test) {
            return redirect('conferences');
        }
        // Check if now time is before or after start time
        $today = Carbon::today('Europe/Athens')->format('d-m-Y');
        $tomorrow = Carbon::tomorrow('Europe/Athens')->format('d-m-Y');
        $conference_today_start_time = Carbon::createFromFormat('d-m-Y H:i:s', $today . ' ' . $conference->getTime($conference->start) . ':00');
        $conference_tomorrow_start_time = Carbon::createFromFormat('d-m-Y H:i:s', $tomorrow . ' ' . $conference->getTime($conference->start) . ':00');
        $conference_today_end_time = Carbon::createFromFormat('d-m-Y H:i:s', $today . ' ' . $conference->getTime($conference->end) . ':00');
        $conference_tomorrow_end_time = Carbon::createFromFormat('d-m-Y H:i:s', $tomorrow . ' ' . $conference->getTime($conference->end) . ':00');
        if (Carbon::now('Europe/Athens')->diffInSeconds($conference_today_start_time, false) >= 0) {
            $conference->start = $conference_today_start_time;
        } elseif (Carbon::now('Europe/Athens')->diffInSeconds($conference_today_start_time, false) < 0) {
            $conference->start = $conference_tomorrow_start_time;
        }
        if (Carbon::now('Europe/Athens')->diffInSeconds($conference_today_end_time, false) >= 0 && Carbon::now('Europe/Athens')->diffInSeconds($conference_today_start_time, false) < 0) {
            $conference->end = $conference_tomorrow_end_time;
        } elseif (Carbon::now('Europe/Athens')->diffInSeconds($conference_today_end_time, false) >= 0 && Carbon::now('Europe/Athens')->diffInSeconds($conference_today_start_time, false) >= 0) {
            $conference->end = $conference_today_end_time;
        } elseif (Carbon::now('Europe/Athens')->diffInSeconds($conference_today_end_time, false) < 0) {
            $conference->end = $conference_tomorrow_end_time;
        }
        return view('conferences.copy', compact('conference'));
    }

    /**
     * @param $id
     * @return RedirectResponse|Response|Redirector
     */
    public function join_conference_mobile($id)
    {
        $authenticated_user = Auth::user();
        $conference = Conference::findOrFail($id);
        $data = array();
        // $room_information = $conference->GetRoomInfo();
        //  $isLocked = json_encode($room_information->RoomMode->isLocked);
        if ($conference->isParticipant() == false) {
            abort(403);
        }

        $participant_values = $authenticated_user->participantValues($conference->id);
        $participantIsEnabled = $participant_values->enabled;
        $join_url = $participant_values->join_url;

        if ($conference->participantConferenceStatus(Auth::user()->id) == 1) {
            $data['message'] = trans('controllers.alreadyConnected') . ', <br/> ' . trans('controllers.closeTab');
            $data['type'] = "already_in_conference";
            return response()->view('conferences.conferenceConnectionFailed', $data);
        } elseif ($participantIsEnabled == 0) {
            //$data['heading'] = 'Δεν είστε ενεργός για αυτή την τηλεδιάσκεψη.';
            $data['message'] = trans('controllers.youAreDisabled') . ', <br/> ' . trans('controllers.closeTab');
            $data['type'] = "disabled_participant";
            return response()->view('conferences.conferenceConnectionFailed', $data);
        }

        if (!empty($join_url)) {
            return redirect($join_url);
        } else {
            $join_url = $conference->look_for_join_urls($participant_values->registrant_id);
            if (!empty($join_url)) {
                return redirect($join_url);
            } else {
                return back();
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function detach_participant(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();
        $conference = Conference::findOrFail($input['conference_id']);
        if ($user->hasRole('SuperAdmin') || ($user->hasRole('InstitutionAdministrator') && in_array($conference->institution_id, $user->institutions->pluck('id')->toArray())) || ($user->hasRole('DepartmentAdministrator') && in_array($conference->department_id, $user->departments->pluck('id')->toArray()))) {

            $detached_user = User::find($input['user_id']);
            $participant_info = $detached_user->participantValues($conference->id);
            $conference->detachParticipant($input['user_id']);
            if($participant_info->invited == 1 && $detached_user->status == 1) {
                $parameters['conference'] = $conference;
                $email = Email::where('name', 'participantDeleted')->first();
                $moderator = User::findOrFail($conference->user_id);
                Mail::send('emails.conference_participantDeleted', $parameters, function ($message) use ($detached_user, $email, $moderator) {
                    $message->from($email->sender_email, config('mail.from.name'))
                        ->to($detached_user->email)
                        ->replyTo($moderator->email, $moderator->firstname . ' ' . $moderator->lastname)
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }
            $type = $conference->room_enabled == 1 ? 'active' : 'future';
            event(new ParticipantRemoved($conference->id, $input['user_id'], $type));
            return response()->json(['status' => 'true']);
        } else {
            return response()->json(['status' => 'false', 'message' => 'You do not have permission to do this action']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function assign_participant(Request $request)
    {
        $input = $request->all();
        $conference = Conference::findOrFail($input['conference_id']);
        $device = 'Desktop-Mobile';
        $user = User::findOrFail($input['user_id']);
        $type = $conference->room_enabled == 1 ? 'active' : 'future';

        //Check if this user is already a participant of this meeting

        if ($conference->isParticipant($user)) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.userAlreadyAdded')
            );
            return response()->json($results);
        }

        //Check if this participant overcomes the total participants allowed in a conference
        $total_participants_allowed = Settings::where('title', 'conference_maxParticipants')->first();
        if ($conference->participants->count() >= $total_participants_allowed->option) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotEnterMoreThan') . ' ' . $total_participants_allowed->option . ' ' . trans('controllers.participants') . '!'
            );
            return response()->json($results);
        }
        //If everything is ok continue
        $conference->participants()->save($user);
        if ($user->confirmed) {
            $this->add_participant($conference, $input['user_id'], $device);
        } else {
            DB::table('conference_user')->where('conference_id', $conference->id)->where('user_id', $input['user_id'])->update(['device' => $device]);
        }
        $results = array(
            'status' => 'success',
            'data' => trans('controllers.userAdded'),
            'user_id' => $user->id,
            'full_name' => $user->firstname . " " . $user->lastname,
            'email' => $user->email
        );
        event(new ParticipantAdded($conference->id, $input['user_id'], $type));
        return response()->json($results);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function assign_multiple_participants(Request $request)
    {
        $device = 'Desktop-Mobile';
        $input = $request->all();
        $conference = Conference::findOrFail($input['conference_id']);
        if(!empty($input['emails_input'])){
            $emails = explode(PHP_EOL, $input['emails_input']);
            $emails_failed = [];
            $emails_added = 0;
            foreach ($emails as $email) {
                $trimmed_email = trim($email);
                $user_found = null;
                $user = User::where("email", $trimmed_email)->first();
                if (isset($user->id)) {
                    $user_found = $user;
                } else {
                    $extra_email = ExtraEmail::where("email", $trimmed_email)->where("confirmed", true)->first();
                    if (isset($extra_email->id) && isset($extra_email->user->id)) {
                        $user_found = $extra_email->user;
                    }
                }
                if (!empty($user_found) && !$conference->isParticipant($user_found)) {
                    //User is found add him to conference
                    $conference->participants()->save($user_found);
                    $this->add_participant($conference, $user_found->id, $device);
                    $emails_added++;
                } else {
                    //User not found add email to errors
                    $emails_failed[] = $email;
                }
            }
            return back()->with("multiple_participants_assigned",$emails_added)->with("multiple_participants_error",$emails_failed);
        }else{
            return back()->with("multiple_participants_input_empty");
        }
    }

    /**
     * @param $conference
     * @param $user_id
     * @param $device
     */
    private function add_participant($conference, $user_id, $device)
    {
        $zoom_api_response = $conference->assignParticipant($user_id);
        $join_url = isset($zoom_api_response->join_url) ? $zoom_api_response->join_url : null;
        $registrant_id = isset($zoom_api_response->registrant_id) ? $zoom_api_response->registrant_id : null;
        DB::table('conference_user')
            ->where('conference_id', $conference->id)
            ->where('user_id', $user_id)
            ->update(['device' => $device, 'join_url' => $join_url, 'registrant_id' => $registrant_id]);
    }

    /**
     * @return false|string
     */
    public function calendar_json()
    {
        $start = $_REQUEST['from'] / 1000;
        $start_time = date('Y-m-d', $start);
        $end = $_REQUEST['to'] / 1000;
        $end_time = date('Y-m-d', $end);
        $conferences = Conference::where('start', '>=', $start_time)
            ->where('start', '<=', $end_time)
            ->where('invisible', 0)
            ->get();
        $string = $conferences;
        $out = array();
        $logArr = array();
        foreach ($conferences as $conference) {
            $out[] = array(
                'id' => $conference->id,
                'title' => $conference->title . ' (' . $conference->getTime($conference->start) . ' - ' . $conference->getTime($conference->end) . ', ' . $conference->institution->title . ' - ' . $conference->department->title . ')',
                'class' => 'event-success',
                'url' => 'conferences/' . $conference->id . '/details',
                'start' => strtotime($conference->start) . '000',
                'end' => strtotime($conference->end) . '000'
            );
        }
        return json_encode(array('success' => 1, 'result' => $out));
    }

    /**
     * @return RedirectResponse
     */
    public function sendParticipantEmail()
    {
        $conference = Conference::findOrFail($_POST['conference_id']);
        $email = Email::where('name', 'conferenceInvitation')->first();
        $moderator = User::findOrFail($conference->user_id);
        if (isset($_POST['participants'])) {
            $participants = $_POST['participants'];
            foreach ($participants as $participant) {
                $user = User::findOrFail($participant);
                $participant_details = DB::table('conference_user')
                    ->select('confirmed', 'confirmation_code')
                    ->where('conference_id', $conference->id)
                    ->where('user_id', $user->id)
                    ->get();
                $parameters = array('conference' => $conference, 'support_url' => URL::to("/support"));
                if (empty($participant_details[0]->confirmation_code)) {
                    if ($participant_details[0]->confirmed == 0) {
                        $token = str_random(20);
                        //Save token for user
                        DB::table('conference_user')
                            ->where('conference_id', $conference->id)
                            ->where('user_id', $user->id)
                            ->update(['confirmation_code' => $token, 'invited' => 1]);
                    }
                } else {
                    $token = $participant_details[0]->confirmation_code;
                }
                $confirmation_link = URL::to("conferences/" . $conference->id . '/accept_invitation/' . $token);
                $parameters['confirmation_link'] = $confirmation_link;
                $parameters['conferences_url'] = URL::to("conferences");
                $parameters['ical_start'] = $conference->start->format('Ymd\THis');
                $parameters['ical_end'] = $conference->end->format('Ymd\THis');
                $parameters['user'] = $user;
                $parameters['device'] = DB::table('conference_user')->where('conference_id', $conference->id)->where('user_id', $user->id)->value('device');
                $parameters['moderator'] = $moderator;
                //If user is local and unconfirmed create a new token so he can resend an activation email via the link generated
                if (!$user->confirmed && $user->state == 'local') {
                    $token = str_random(20);
                    $user->update(['activation_token' => $token]);
                }
                $ics = $conference->geticsFile($moderator, $user);
                if ($user->status == 1) {
                    Mail::send('emails.conference_invitation', $parameters, function ($message) use ($user, $email, $conference, $moderator, $ics) {
                        $message->from($email->sender_email, config('mail.from.name'))
                            ->to($user->email)
                            ->replyTo($moderator->email, $moderator->firstname . ' ' . $moderator->lastname)
                            ->returnPath(env('RETURN_PATH_MAIL'))
                            ->subject($email->title.' '. $conference->getDate($conference->start))
                            ->attach(storage_path("app" . $ics), array('mime' => "text/calendar"));
                    });
                }
            }
            return redirect(redirect()->getUrlGenerator()->previous())->with('message', trans('controllers.invitationEmailMessage'));
        } elseif (!isset($_POST['participants'])) {
            $message = trans('controllers.mustSelectOneParticipant');
            return redirect(redirect()->getUrlGenerator()->previous())->with('error', $message);
        }
    }

    /**
     * @param $id
     * @param $user_token
     * @return RedirectResponse
     */
    public function userAcceptInvitation($id, $user_token)
    {
        $user_id = DB::table('conference_user')
            ->where('conference_id', $id)
            ->where('confirmation_code', $user_token)
            ->value('user_id');

        if (!empty($user_id)) {
            $success = Auth::onceUsingId($user_id);
            $conference = Conference::findOrFail($id);
            $message = '';
            if ($success) {
                //Check auth user confirmation_code for the specific conference
                $user_confirmed = DB::table('conference_user')
                    ->where('conference_id', $conference->id)
                    ->where('user_id', $user_id)
                    ->first();
                //If user token is not valid
                if ($user_confirmed->confirmation_code != $user_token) {
                    abort(403);
                } //If user hasn't confirmed
                elseif ($user_confirmed->confirmed == 0) {
                    //Update db record
                    DB::table('conference_user')
                        ->where('conference_id', $conference->id)
                        ->where('user_id', $user_id)
                        ->update(['confirmed' => 1]);
                    $message = trans('controllers.participationConfirmed');
                } //If user has already confirmed
                elseif ($user_confirmed->confirmed == 1) {
                    $message = trans('controllers.participationAlreadyConfirmed');
                }
                return redirect('message')->with('message', $message);
            } elseif (!$success) {
                abort(403);
            }
        } else {
            abort(404);
        }
    }

    /**
     *
     */
    public function conferenceUserDisconnect()
    {
        $conference_id = $_POST['conference_id'];
        //if user has already joined once
        DB::table('conference_user')
            ->where('conference_id', $conference_id)
            ->where('user_id', Auth::user()->id)
            ->update(['active' => 0]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function conferenceAddUserEmail(Request $request)
    {
        if (Auth::user()->hasRole('EndUser')) {
            abort(403);
        }
        $email = $request->email;
        if (intval(strlen($email)) < 5) {
            abort(403);
        }
        if (str_contains($email, '_')) {
            $email = str_replace('_', '\_', $email);
        }
        $userprimaryMails = User::where('deleted', false)->where('email', 'like', $email . '%')->get();
        $extraMails = ExtraEmail::where('email', 'like', $email . '%')->get();
        $data = array();
        foreach ($userprimaryMails as $user) {
            $mailtxt = $user->email;

            if ($user->confirmed == 0)
                $mailtxt = $user->email . " (" . trans('users.emailNotConfirmedShort') . ")";

            if ($user->status == 0) {
                $mailtxt = '(' . trans('users.inactive') . ') ' . $mailtxt;
                $disabled = true;
            } else
                $disabled = false;

            $data[] = ['id' => $user->email, 'text' => $mailtxt, 'disabled' => $disabled];
        }

        foreach ($extraMails as $mail) {
            $mailtxt = $mail->email;
            if ($mail->confirmed == 0)
                $mailtxt = $mail->email . " (" . trans('users.emailNotConfirmedShort') . ")";

            if ($mail->user->status == 0) {
                $disabled = true;
                $mailtxt = '(' . trans('users.inactive') . ')' . $mailtxt;
            } else
                $disabled = false;

            if (!$mail->user->deleted) {
                if (!in_array($mail, $data))
                    $data[] = ['id' => $mail->email, 'text' => $mailtxt, 'disabled' => $disabled];

            }
        }
        return response()->json($data);
    }

    /**
     * @param $email
     * @return JsonResponse
     */
    public function requestParticipant($email)
    {
        if (Auth::user()->hasRole('EndUser')) {
            abort(403);
        }
        $user = User::where('email', $email)->first();
        $xtraMailUserId = ExtraEmail::where('email', $email)->first();
        if (!empty($xtraMailUserId)) {
            $xtraMailUser = User::find($xtraMailUserId->user_id);
        }
        if (empty($user) && empty($xtraMailUser)) {
            abort(404);
        }
        if (empty($user) && !empty($xtraMailUser)) {
            $user = $xtraMailUser;
        }
        $name = $user->lastname . ' ' . $user->firstname;
        $role = trans($user->roles->first()->label);
        if ($user->institutions()->count() > 0) {
            $institution = $user->institutions()->first()->title;
        } else {
            $institution = trans('controllers.notDefinedYet');
        }
        if ($user->departments()->count() > 0) {
            $department = $user->departments()->first()->title;
        } else {
            $department = trans('controllers.notDefinedYet');
        }
        $button = '<button id="RowAddtoTele-' . $user->id . '" type="button" class="btn btn-success btn-sm btn-border" onclick="assignUserID(' . $user->id . ')">' . trans('conferences.adduser') . ' <span class="glyphicon glyphicon-share-alt"></span></button>';
        $json = array();
        $json['sEcho'] = 1;
        $json['iTotalRecords'] = 1;
        $json['iTotalDisplayRecords'] = 1;
        $json['aaData'] = [[$name, $role, $institution, $department, $button]];
        return response()->json($json);
    }

    /**
     * @param Request $request
     */
    public function userConferenceDeviceAssign(Request $request)
    {
        $input = $request->all();
        $conference = Conference::findOrFail($input['conference_id']);
        $participant = User::findOrFail($input['user_id']);
        if ($conference->participantConferenceStatus($input['user_id']) == 1) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.toChangeDeviceDisconnect'),
                'oldValue' => $participant->participantValues($conference->id)->device
            );
            echo json_encode($results);
        } elseif ($input['device'] == 'Desktop-Mobile') {
            DB::table('conference_user')->where('conference_id', $conference->id)->where('user_id', $input['user_id'])->update(['device' => $input['device']]);
            $results = array(
                'status' => 'success',
                'data' => trans('controllers.deviceChanged')
            );
            event(new ParticipantDeviceChanged($conference, $input['device'], $input['user_id']));
            echo json_encode($results);
        } elseif ($input['device'] == 'H323') {
            DB::table('conference_user')->where('conference_id', $conference->id)->where('user_id', $input['user_id'])->update(['device' => $input['device']]);
            $results = array(
                'status' => 'success',
                'data' => trans('controllers.deviceChanged')
            );
            event(new ParticipantDeviceChanged($conference, $input['device'], $input['user_id']));
            echo json_encode($results);
        } else {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotAddMore') . $input['device'] . trans('controllers.usersThanDeclared'),
                'oldValue' => $participant->participantValues($conference->id)->device
            );
            echo json_encode($results);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function inviteH323ToConference(Request $request, $id)
    {
        $conference = Conference::findOrFail($id);
        $currently_total_online_h323 = Cdr::whereNull('leave_time')->where('device', 'H323')->count();
        $max_h323_allowed = Settings::where('title', 'conference_H323Resources')->first();
        $is_h323_ip_detection_feature_enabled = Settings::where('title', 'conference_EnabledH323IpDetection')->first();
        if (isset($max_h323_allowed->id) && isset($is_h323_ip_detection_feature_enabled->id)) {
            if (($max_h323_allowed->option - $is_h323_ip_detection_feature_enabled->option) <= $currently_total_online_h323) {

                //Return max cap reached error

                $response['status'] = 'error';
                $response['error_message'] = trans('conferences.max_h323_allowed_error');

                return response()->json($response);
            }
        }
        $validator = Validator::make($request->all(), [
            'H323IP' => 'required|ip',
        ],
            ['H323IP.required' => trans('controllers.enterValidIpUri'),
                'H323IP.ip' => trans('controllers.enterValidIpUri')]
        );
        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['error_message'] = $validator->errors()->first();
        } else {
            $response['status'] = 'success';
        }
        $input = $request->all();
        $IP = $input['H323IP'];
        $conference->participantIdentifier($IP);
        //Remove named user from H323 blocking group here and queue a job to add the named user back to the blocking group after 5 minutes
        $conference->enableH323Connections($IP);
        return response()->json($response);
    }

    /**
     * @param $id
     * @return Factory|\Illuminate\View\View
     */
    public function conferenceConnection($id)
    {
        $conference = Conference::findOrFail($id);
        if ($conference->room_enabled == 0) {
            $data['message'] = trans('controllers.conferenceInactive');
            return view('conferences.conferenceConnectionFailed', $data);
        } else {
            $participantIsEnabled = Auth::user()->participantValues($conference->id)->enabled;
            if ($conference->isParticipant() == false) {
                abort(403);
            } elseif ($participantIsEnabled == 0) {
                $data['message'] = trans('controllers.youAreDisabled');
                return view('conferences.conferenceConnectionFailed', $data);
            }
            $seconds_left = Carbon::now()->lessThan($conference->end) ? Carbon::now()->diffInSeconds($conference->end) : 0;
            return view('conferences.conferenceConnection', compact('conference'))->with('seconds_left', $seconds_left);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function lockUnlockRoom(Request $request, $id)
    {
        $conference = Conference::findOrFail($id);
        $authenticated_user = Auth::user();
        $status = null;
        $title = null;
        $message = null;
        $action = null;
        //Permissions check
        if (!$authenticated_user->hasAdminAccessToConference($conference)) {
            $status = "error";
            $title = "wrong action";
            $message = "invalid parameters or inefficient permissions";
        } else {
            $action = $request->action;
            if ($action == 'lock') {
                $conference->lock_room();
                $status = "success";
                $message = trans('controllers.roomLocked');
                $title = trans('controllers.unlockRoom');
                event(new ConferenceLockStatusChanged($conference->id, 'locked'));
            } elseif ($action == 'unlock') {
                $conference->unlock_room();
                $status = "success";
                $title = trans('controllers.lockRoom');
                $message = trans('controllers.roomUnlocked');
                event(new ConferenceLockStatusChanged($conference->id, 'unlocked'));
            } else {
                $status = "error";
                $title = "wrong action";
                $message = "invalid parameters or inefficient permissions";
            }
        }
        $response = array(
            'status' => $status,
            'message' => $message,
            'title' => $title,
            'participant_ids' => $conference->participants()->pluck("id")->toArray()
        );
        return response()->json($response);
    }

    /**
     * @return false|string
     */
    public function update_front_stats()
    {
        $total_row = DB::table('service_usage')->where('option', 'total')->first();
        $today_row = DB::table('service_usage')->where('option', 'today')->first();
        $now_row = DB::table('service_usage')->where('option', 'now')->first();
        $data = array("total_total_conferences" => isset($total_row->total_conferences) ? $total_row->total_conferences : 0,
            "total_desktop_mobile" => isset($total_row->desktop_mobile) ? $total_row->desktop_mobile : 0,
            "total_h323" => isset($total_row->h323) ? $total_row->h323 : 0,
            "today_total_conferences" => isset($today_row->total_conferences) ? $today_row->total_conferences : 0,
            "today_desktop_mobile" => isset($today_row->desktop_mobile) ? $today_row->desktop_mobile : 0,
            "today_h323" => isset($today_row->h323) ? $today_row->h323 : 0,
            "now_total_conferences" => isset($now_row->total_conferences) ? $now_row->total_conferences : 0,
            "now_desktop_mobile" => isset($now_row->desktop_mobile) ? $now_row->desktop_mobile : 0,
            "now_h323" => isset($now_row->h323) ? $now_row->h323 : 0
        );
        return json_encode($data);
    }

    /**
     * @return Factory|\Illuminate\View\View
     */
    public function post_attendee()
    {
        return view('conferences.postAttendee');
    }

    /**
     * @param $conference_id
     * @return Factory|\Illuminate\View\View
     */
    public function show_ip_retrieval_page($conference_id)
    {
        $data['conference'] = Conference::findOrFail($conference_id);
        $feature_is_enabled = Settings::where('title', 'conference_EnabledH323IpDetection')->first();
        if ($feature_is_enabled->option == 0) {
            session()->flash('error', trans('errors.ip_retrieval_feature_not_available'));
            return view('conferences.retrieve_ip_address', $data);
        } else {
            $named_user_responsible_for_h323_ip_retrieval = NamedUser::where('type', 'h323_ip_detection')->where('latest_used', false)->first();
            $redis = Redis::connection();
            if (isset($named_user_responsible_for_h323_ip_retrieval->id)) {
                //Create room for ip retrieval and keep meeting id in redis
                $zoom_client = new JiraClient();
                //Create new meeting
                $h323_ip_retrieval_named_user_zoom_id = $named_user_responsible_for_h323_ip_retrieval->zoom_id;
                $start_time = Carbon::now()->format("Y-m-d\TH:i:s");
                $parameters = [
                    "topic" => "h323-ip-retrieval",
                    "type" => "2",
                    "start_time" => $start_time,
                    "duration" => 30,
                    "timezone" => "Europe/Athens",
                    "password" => "",
                    "agenda" => "",
                    "settings" => [
                        "host_video" => "true",
                        "participant_video" => "true",
                        "cn_meeting" => "false",
                        "in_meeting" => "false",
                        "join_before_host" => "true",
                        "mute_upon_entry" => "false",
                        "watermark" => "false",
                        "use_pmi" => "false",
                        //0 -> participants are required to fill a form in order to join them meeting even if they are using their personal link
                        "approval_type" => "1",
                        "registration_type" => "2",
                        "audio" => "voip",
                        "auto_recording" => "none",
                        "enforce_login" => "false",
                        "enforce_login_domains" => "",
                        "alternative_hosts" => ""
                    ]
                ];
                $create_response = $zoom_client->create_meeting($parameters, $h323_ip_retrieval_named_user_zoom_id);
                $zoom_meeting_id = $create_response->id;
                $update_parameters = $parameters;
                $update_parameters['settings']['registrants_confirmation_email'] = "false";
                $zoom_client->update_meeting($update_parameters, $zoom_meeting_id);
                $meeting_id_key = 'h323_ip_retrieval:meeting_id';
                $redis->set($meeting_id_key, $zoom_meeting_id);
                $user_id_key = 'h323_ip_retrieval:user_id';
                $redis->set($user_id_key, Auth::user()->id);
                $conference_id_key = 'h323_ip_retrieval:conference_id';
                $redis->set($conference_id_key, $conference_id);
                $h323_ip_address_key = 'h323_ip_retrieval:address';
                $redis->set($h323_ip_address_key, null);
                $now = Carbon::now()->toDateTimeString();
                $h323_time_key = 'h323_ip_retrieval:time';
                $redis->set($h323_time_key, $now);
                //Open firewall for all ip addresses
                if (config('firewall.protection') == "on") {
                    $key = new RSA();
                    $key->loadKey(file_get_contents(config('firewall.ssh_key')));
                    $ssh = new SSH2(config('firewall.host'));
                    if (!$ssh->login(config('firewall.username'), $key)) {
                        Log::error("Firewall ssh2 connection: Public Key Authentication Failed!");
                    } else {
                        Log::info("Firewall ssh2 connection: Public key auth successful!");
                        $insert_exec_1 = "sudo /sbin/iptables -I FORWARD -p tcp -d  " . config('services.zoom.h323_sensor_ip_address') . " --dport 1720 -j ACCEPT";
                        $insert_exec_2 = "sudo /sbin/iptables -I FORWARD -p tcp -d " . config('services.zoom.h323_sensor_ip_address') . "  --dport 5060 -j ACCEPT";
                        Log::info("Executing: " . $insert_exec_1);
                        $response = $ssh->exec($insert_exec_1);
                        if (empty($response)) {
                            Log::info("Exec is Successful!");
                        } else {
                            Log::error("Exec error: " . $response);
                        }
                        Log::info("Executing: " . $insert_exec_2);
                        $response = $ssh->exec($insert_exec_2);
                        if (empty($response)) {
                            Log::info("Exec is Successful!");
                        } else {
                            Log::error("Exec error: " . $response);
                        }
                    }
                }
                EndH323IpRetrievalMeeting::dispatch()->delay(now()->addMinutes(5));
                NamedUser::where('type', 'h323_ip_detection')->where('latest_used', false)->update(['latest_used' => true]);
                $ip_address = config('services.zoom.h323_sensor_ip_address');
                $data['test_connection_ip_address'] = $ip_address;
                $data['test_connection_meeting_id'] = $zoom_meeting_id;
                $data['seconds_left'] = 300;
                $data['retrieved_ip_address'] = null;
                return view('conferences.retrieve_ip_address', $data);
            } else {
                $user_id_key = 'h323_ip_retrieval:user_id';
                $user_id_saved = $redis->get($user_id_key);
                $meeting_id_key = 'h323_ip_retrieval:meeting_id';
                $meeting_id_saved = $redis->get($meeting_id_key);
                $conference_id_key = 'h323_ip_retrieval:conference_id';
                $conference_id_saved = $redis->get($conference_id_key);
                $h323_time_key = 'h323_ip_retrieval:time';
                $data['seconds_left'] = 300 - Carbon::parse($redis->get($h323_time_key))->diffInSeconds(Carbon::now());
                if ($user_id_saved == Auth::user()->id && $conference_id == $conference_id_saved) {
                    $ip_address = config('services.zoom.h323_sensor_ip_address');
                    $data['test_connection_ip_address'] = $ip_address;
                    $data['test_connection_meeting_id'] = $meeting_id_saved;
                    $retrieved_address_key = 'h323_ip_retrieval:address';
                    $data['retrieved_ip_address'] = $redis->get($retrieved_address_key);
                    return view('conferences.retrieve_ip_address', $data);
                } else {
                    session()->flash('error', 'Ip retrieval room is not currently available please try again later.');
                    return view('conferences.retrieve_ip_address', $data);
                }
            }
        }
    }
}
