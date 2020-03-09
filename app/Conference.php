<?php

namespace App;

use App\Email;
use App\Jobs\Conferences\AddNamedUserToBlockingGroup;
use App\Jobs\Conferences\AddRegistrant;
use App\Jobs\Conferences\CloseIpAddressForH323;
use App\Jobs\Conferences\OpenIpAddressForH323;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Psr\Http\Message\ResponseInterface;
use Session;
use DateTime;
use SoapFault;
use URL;
use Mail;
use App\User;
use \Storage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Illuminate\Contracts\Bus\Dispatcher;

use Illuminate\Database\Eloquent\Model;

class Conference extends Model
{
    protected $fillable = [
        'title',
        'desc',
        'descEn',
        'start',
        'end',
        'user_id',
        'institution_id',
        'department_id',
        'room_enabled',
        'admin_id',
        'instantActivation',
        'invisible',
        'locked',
        'forced_end',
        'apella_id',
        'named_user_id',
        'join_url',
        'start_url',
        'zoom_meeting_id',
        'host_url_accessible',
        'test',
        'max_duration'
    ];

	protected $dates = ['start', 'end','forced_end'];

    /**
     * @return BelongsTo
     */
	public function user()
	{
		return $this->belongsTo('App\User', 'user_id');
	}

    /**
     * @return HasOne
     */
	public function statistics_row()
	{
		return $this->hasOne('App\Statistics');
	}

    /**
     * @return BelongsTo
     */
	public function institution()
	{
		return $this->belongsTo('App\Institution', 'institution_id');
	}

    /**
     * @return BelongsTo
     */
	public function department()
	{
		return $this->belongsTo('App\Department', 'department_id');
	}

    /**
     * @param $date
     * @return string
     */
	public function getDate($date)
	{
		return (is_object($date) && get_class($date) === "Carbon\Carbon") ? $date->format('d-m-Y') : Carbon::parse($date)->format('d-m-Y');
	}

    /**
     * @param $date
     * @return string
     */
	public function getTime($date)
	{
        return (is_object($date) && get_class($date) === "Carbon\Carbon") ? $date->format('H:i') : Carbon::parse($date)->format('H:i');
	}

    /**
     * @return BelongsToMany
     */
	public function participants()
	{
		return $this->belongsToMany('App\User')->withPivot(
            'invited', 'confirmed', 'joined_once', 'duration', 'intervals', 'address', 'in_meeting', 'enabled', 'confirmation_code', 'device', 'identifier'
        );
	}

    /**
     * @return mixed
     */
	public function notParticipants()
	{
		$id = $this->id;
		return User::whereDoesntHave('conferences', function ($q) use($id){
			$q->whereId($id);
		})->get();
	}

    /**
     * @param null $user
     * @return mixed
     */
	public function isParticipant($user = null)
	{
        if (empty($user))
            $user = Auth::user();

        return DB::table('conference_user')->where('conference_id', $this->id)->where('user_id',$user->id)->exists();
	}

    /**
     * @return mixed
     */
	public function is_future()
    {
        return $this->start->gte(Carbon::now());
    }

    //Test conferences scopes and checks

    /**
     * @param $query
     * @return mixed
     */
    public function scopeNonTest($query){
        $test_terms = config('conferences.test_terms');
        foreach($test_terms as $term){
            $query->where('title', 'not like', '%'.$term.'%');
        }

	    return $query;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeTest($query){
        $test_terms = config('conferences.test_terms');
        $query->where("test",true);
        foreach($test_terms as $k=>$term){
            $query->Orwhere('title', 'like', '%'.$term.'%');
        }
        return $query;
    }

    /**
     * @return bool
     */
    public function is_test(){

	    if($this->test)
	        return true;

        $test_terms = config('conferences.test_terms');

        foreach($test_terms as $term){
            if(strpos($this->title, $term) !== false)
                return true;
        }

	    return false;
	}


    //Named User(zoom user) used in the api call to organize the conference

    /**
     * @return BelongsTo
     */
    public function named_user()
    {
        return $this->belongsTo('App\NamedUser', 'named_user_id');
    }


    /**
     * @param $participant
     * @return string
     */
    public function userConferenceDevice($participant)
    {
        $devices = ['', 'Desktop-Mobile', 'H323'];

        //Disabled device selection for now

        $dropdown = '<table style="border: solid 0px; width: 100%; table-layout: fixed !important;">
						<tr>
							<td>
								<div>
                                 <select id="selectPDevice-' . $participant->id . '" style="width: 100%">';
        foreach ($devices as $device) {
            if ($participant->participantValues($this->id)->device == $device) {
                $dropdown .= '<option value="' . $device . '" selected>' . $device . '</option>';
            } else {
                $dropdown .= '<option value="' . $device . '">' . $device . '</option>';
            }
        }
        $dropdown .= '</select>
								</div>
							</td>
						</tr>
					</table>';

        return $dropdown;
    }

    /**
     * @param $device
     * @return mixed
     */
    public function participantsPerDevice($device)
    {
        $num = DB::table('conference_user')->where('conference_id', $this->id)->where('device', $device)->count();
        return $num;
    }

    /**
     * @return bool
     */
    public function isActiveOrFuture()
    {
        $now = Carbon::now('Europe/Athens');
        $active = $this->room_enabled;
        return $active == 1 || Carbon::parse($this->start)->gt($now) ? true : false;
    }

    /**
     * @return string
     */
    public function rowButtonDeleteDisabled()
    {
        $now = Carbon::now('Europe/Athens');
        $disabled = '';
        $active = Statistics::where('conference_id', $this->id)->value('active');

        if ($this->start > $now) {
            $disabled = '';
        } elseif (Auth::user()->hasRole('SuperAdmin')) {
            $disabled = '';
        } else {
            $disabled = 'disabled';
        }

        return $disabled;
    }

    /**
     *
     */
    public function cancelConferenceEmail()
    {
        $participants = $this->participants;
        $conference = Conference::findOrFail($this->id);
        $parameters['conference'] = $conference;
        $email = Email::where('name', 'conferenceCanceled')->first();
        $moderator = User::findOrFail($conference->user_id);

        foreach ($participants as $participant) {
            // Send cancelation email to the participants that have been invited
            if ($participant->participantValues($this->id)->invited == 1 && $participant->status == 1) {
                Mail::send('emails.conference_conferenceCanceled', $parameters, function ($message) use ($participant, $email, $moderator) {
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($participant->email)
                        ->replyTo($moderator->email, $moderator->firstname . ' ' . $moderator->lastname)
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }
        }

    }

    /**
     * @param $invisible
     * @return string
     */
    public function invisible_icon($invisible)
    {
        if ($invisible == 1) {
            return 'glyphicon-ban-circle';
        } else {
            return 'glyphicon-ok';
        }
    }

    /**
     * @param $invisible
     * @return array|Translator|string|null
     */
    public function invisible_string($invisible)
    {

        if ($invisible == 1) {
            return trans('application.hidden');
        } else {
            return trans('application.visible');
        }
    }

    // Store and update restrictions

    /**
     * @param $input
     * @param $from
     * @param null $conference_id
     * @return array
     */
    public static function hasErrors($input, $from, $conference_id = null)
    {

        $startDateTime = $input['start_date'] . ' ' . $input['start_time'] . ':00';
        $input['start'] = Carbon::createFromFormat('d-m-Y H:i:s', $startDateTime)->toDateTimeString();

        $endDateTime = $input['end_date'] . ' ' . $input['end_time'] . ':00';
        $input['end'] = Carbon::createFromFormat('d-m-Y H:i:s', $endDateTime)->toDateTimeString();

        $start = Carbon::createFromFormat('d-m-Y H:i:s', $startDateTime);
        $end = Carbon::createFromFormat('d-m-Y H:i:s', $endDateTime);

        $errors = array();

        if (Conference::ScheduledMaintenanceModeOn($start, $end) && !Auth::user()->hasRole('SuperAdmin')) {
            $maintenance_startDate = Settings::option('maintenance_start');
            $maintenance_endDate = Settings::option('maintenance_end');
            $errors [] = trans('application.at') . ' ' . Carbon::createFromTimestamp($maintenance_startDate)->format("d-m-Y H:i:s") . ' - ' . Carbon::createFromTimestamp($maintenance_endDate)->format("d-m-Y H:i:s") . ' ' . trans('application.maintenanceScheduled');
        }

        if ($start->diffInMinutes($end, false) < 0) {
            $errors [] = trans('application.noEndBeforeStart');
        }

        if ($start->diffInMinutes($end, false) < 15) {
            $errors [] = trans('application.noDurationlt15min');
        }

        if ($input['end'] < Carbon::now()) {
            $errors [] = trans('application.invalidEnd');
        }

        if ($input['end'] < $input['start']) {
            $errors [] = trans('application.invalidEnd');
        }

        switch ($from) {
            case 'store':

                if ($input['start'] < Carbon::now()->subMinutes(15)) {
                    $errors [] = trans('application.minStartToday') . ' ' . Carbon::now()->subMinutes(15)->format('H:i');
                }

                if (!Auth::user()->hasRole('SuperAdmin') && $start->diffInMinutes($end, false) > Settings::option('conference_maxDuration')) {
                    $errors [] = trans('application.durationGt',['minutes'=>Settings::option('conference_maxDuration')]);
                }

                break;
            case 'store-test':


                break;
            case 'update':

                $conference = Conference::findOrFail($conference_id);

                if ($input['start'] != $conference->start && $conference->room_enabled == 1) {
                    $errors[] = trans('application.startCannotChange');
                }

                if ($input['end'] < Carbon::now() && $conference->room_enabled == 1) {
                    $errors[] = trans('application.endCannotChange');
                }

                if ($input['start'] < Carbon::now()->subMinutes(15) && $conference->room_enabled == 0) {
                    $errors[] = trans('application.minStartToday') . ' ' . Carbon::now()->subMinutes(15)->format('H:i');
                }

                if (!Auth::user()->hasRole('SuperAdmin') && $start->diffInMinutes($end, false) > $conference->max_duration) {
                    $errors [] = trans('application.durationGt',['minutes'=>Settings::option('conference_maxDuration')]);
                }

                break;
        }

        $utf8_errors = array();
        foreach ($errors as $error) {
            $utf8_errors [] = htmlentities($error, ENT_QUOTES | ENT_IGNORE, "UTF-8");
        }

        return $utf8_errors;
    }

    /**
     * @param $conferences
     * @param $input
     * @return mixed
     */
    public static function advancedSearch($conferences, $input)
    {

        $admin_advanced_search = ['firstname', 'lastname'];

        if (Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('InstitutionAdministrator')) {
            $admin_advanced_search = ['firstname', 'lastname', 'email'];
        }

        $conference_advanced_search = ['id', 'title', 'invisible', 'institution_id', 'department_id', 'created_at', 'apella'];


        if (empty($input)) {
            $conferences = $conferences->orderBy('start', 'asc');
        } else {

            foreach ($input as $k => $v) {
                if (in_array($k, $conference_advanced_search) && !empty($v)) {

                    if ($k == 'title') {
                        $conferences = $conferences->where($k, 'like', '%' . escape_like($v) . '%');
                    } elseif ($k == 'created_at') {
                        $conferences = $conferences->whereRaw("Date(created_at) ='" . Carbon::parse($v)->format('Y-m-d') . "'");
                    } elseif ($k == 'apella') {

                        if(!empty($v))
                         $conferences = $v === "*" ? $conferences->whereNotNull('apella_id') : $conferences->where("apella_id", "like", "%" . escape_like($v) . "%");

                    } else {
                        $conferences = $conferences->where($k, intval($v));
                    }


                } elseif ($k == 'start_from' && !empty($v)) {

                    $from = Carbon::now()->subYears(50);
                    $to = Carbon::now()->addYear();
                    if (!empty($input['start_from'])) {
                        $from = Carbon::createFromFormat('d-m-Y', $input['start_from'])->startOfDay()->toDateTimeString();
                    }
                    if (!empty($input['start_to'])) {
                        $to = Carbon::createFromFormat('d-m-Y', $input['start_to'])->endOfDay()->toDateTimeString();
                    }

                    $conferences = $conferences->whereBetween('start', [$from, $to]);
                } elseif (in_array($k, $admin_advanced_search)) {
                    if (!empty($v)) {
                        $conferences = $conferences->whereHas(
                            'user', function ($query) use ($k, $v) {
                            $query->where($k, 'like', '%' . escape_like($v) . '%');
                        }
                        );
                    }
                } elseif (str_contains($k, 'sort_')) {
                    $sort = substr($k, 5);
                    if (in_array($sort, ['title', 'id', 'start', 'end', 'invisible'])) {
                        $conferences = $conferences->orderBy($sort, $v);
                    }
                }
            }
        }

        return $conferences;
    }

    /**
     * @return bool
     */
    public function isPastConference()
    {

        $now = Carbon::now()->timestamp;
        $confEnd = Carbon::parse($this->end)->timestamp;

        if ($confEnd < $now) {
            return true;
        }
        return false;
    }

    /**
     * @return array|Translator|string|null
     */
    public function instantActiveConferenceMessages()
    {

        $message = '';

        $total_users = $this->users_no + $this->users_h323 + $this->users_vidyo_room;
        $usersToAdd = $total_users - $this->participants()->count();
        if ($usersToAdd > 0) {
            $message = trans('application.activationUsersRequired') . ' ' . $usersToAdd . ' ' . trans('application.usersRemaining');
        } else {
            $message = trans('application.conferenceActivated');
        }
        return $message;
    }

    /**
     * @param $now
     * @param $minsFormNow
     * @param $action
     * @return Carbon
     */
    public static function timeFromNow($now, $minsFormNow, $action)
    {
        $min = $now->minute;
        if ($min >= 0 && $min < 5) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':00:00';
        } elseif ($min >= 5 && $min < 10) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':05:00';
        } elseif ($min >= 10 && $min < 15) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':10:00';
        } elseif ($min >= 15 && $min < 20) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':15:00';
        } elseif ($min >= 20 && $min < 25) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':20:00';
        } elseif ($min >= 25 && $min < 30) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':25:00';
        } elseif ($min >= 30 && $min < 35) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':30:00';
        } elseif ($min >= 35 && $min < 40) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':35:00';
        } elseif ($min >= 40 && $min < 45) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':40:00';
        } elseif ($min >= 45 && $min < 50) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':45:00';
        } elseif ($min >= 50 && $min < 55) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':50:00';
        } elseif ($min >= 55 && $min <= 59) {
            $exactNow = Carbon::parse($now)->toDateString() . ' ' . Carbon::parse($now)->format('H') . ':55:00';
        }

        // Time from now
        if ($action == 'add') {
            $ExactTimeFromNow = Carbon::parse($exactNow)->addMinutes($minsFormNow);
        } elseif ($action == 'sub') {
            $ExactTimeFromNow = Carbon::parse($exactNow)->subMinutes($minsFormNow);
        }

        $timeFromNow = Carbon::parse($ExactTimeFromNow)->toDateTimeString();

        return $ExactTimeFromNow;
    }

    public function sendStartConferenceReminders()
    {

        if (Conference::ScheduledMaintenanceModeOn($this->start, $this->end)) {
            // Do nothing
        } else {

            // Users to notify
            $usersToRemind = $this->participants()->where("status", 1)->get();

            foreach ($usersToRemind as $userToRemind) {

                $user_confirmed = DB::table('conference_user')
                    ->select('confirmed', 'confirmation_code')
                    ->where('conference_id', $this->id)
                    ->where('user_id', $userToRemind->id)
                    ->get();

                if ($user_confirmed[0]->confirmed == 0 && empty($user_confirmed[0]->confirmation_code)) {
                    $token = str_random(20);
                    $confirmation_link = URL::to("conferences/" . $this->id . '/accept_invitation/' . $token);


                    //Save token for user
                    DB::table('conference_user')
                        ->where('conference_id', $this->id)
                        ->where('user_id', $userToRemind->id)
                        ->update(['confirmation_code' => $token, 'invited' => 1]);

                } elseif ($user_confirmed[0]->confirmed == 0 && !empty($user_confirmed[0]->confirmation_code)) {
                    $token = $user_confirmed[0]->confirmation_code;
                    $confirmation_link = URL::to("conferences/" . $this->id . '/accept_invitation/' . $token);

                } elseif ($user_confirmed[0]->confirmed == 1 && !empty($user_confirmed[0]->confirmation_code)) {
                    $token = $user_confirmed[0]->confirmation_code;
                    $confirmation_link = URL::to("conferences/" . $this->id . '/accept_invitation/' . $token);
                } else {

                    $confirmation_link = "confirmation_link not found";
                }

                $email = Email::where('name', 'conferenceInvitationReminder')->first();
                $moderator = User::findOrFail($this->user_id);
                $parameters = array('body' => $email->body, 'conference' => $this, 'support_url' => URL::to("support"), 'conferences_url' => URL::to("conferences"), 'confirmation_link' => $confirmation_link);
                $parameters['ical_start'] = $this->start->format('Ymd\THis');
                $parameters['ical_end'] = $this->end->format('Ymd\THis');
                $parameters['user'] = $userToRemind;
                $parameters['device'] = DB::table('conference_user')->where('conference_id', $this->id)->where('user_id', $userToRemind->id)->value('device');
                $parameters['moderator'] = $moderator;

                Mail::send('emails.conference_invitationReminder', $parameters, function ($message) use ($userToRemind, $email, $moderator) {
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($userToRemind->email)
                        ->replyTo($moderator->email, $moderator->firstname . ' ' . $moderator->lastname)
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }

            // Send email to moderator

            $moderator = User::findOrFail($this->user_id);

            if (count($usersToRemind) == 0 && $moderator->status == 1) {
                $email = Email::where('name', 'conferenceRationalUseNoParticipants')->first();
                $parameters = array('conference' => $this);
                Mail::send('emails.conference_rationalUseNoParticipants', $parameters, function ($message) use ($moderator, $email) {
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($moderator->email)
                        ->cc(env('SUPPORT_MAIL'), 'e:Presence')
                        ->replyTo(env('SUPPORT_MAIL'), 'e:Presence')
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }

            return 'OK';
        }
    }


    public function conferencesUsersToNotifyClose()
    {

        if (Conference::ScheduledMaintenanceModeOn($this->start, $this->end)) {
            // Do nothing
        } else {
            // Users to notify
            $usersToRemind = $this->participants()->where("joined_once", 1)->where("status", 1)->get();

            foreach ($usersToRemind as $userToRemind) {
                $moderator = User::findOrFail($this->user_id);

                $email = Email::where('name', 'conferenceEndNotification')->first();
                $parameters = array('body' => $email->body, 'title' => $this->title);
                Mail::send('emails.conference_endNotification', $parameters, function ($message) use ($userToRemind, $email, $moderator) {
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($userToRemind->email)
                        ->replyTo($moderator->email, $moderator->firstname . ' ' . $moderator->lastname)
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }

            return 'OK';
        }
    }

    /**
     * @param $fiveMinsBeforeNow_timestamp
     * @param $now_timestamp
     * @param $current_five_minute_id
     * @return int
     */
    public function activeParticipantsForLastFiveMins($fiveMinsBeforeNow_timestamp, $now_timestamp, $current_five_minute_id)
    {

        $total_desktop = 0;
        $total_distinct_desktop = 0;
        $total_h323 = 0;
        $total_distinct_h323 = 0;


        $cdrs = Cdr::where('conference_id', $this->id)
            ->where(function ($query) use ($fiveMinsBeforeNow_timestamp, $now_timestamp) {
                $query->whereNull('leave_time')
                    ->orWhere(function ($query2) use ($fiveMinsBeforeNow_timestamp, $now_timestamp) {
                        $query2->whereBetween('leave_time', [$fiveMinsBeforeNow_timestamp, $now_timestamp]);
                    });
            })
            ->get();

        $distinct_participants = [];

        foreach ($cdrs as $cdr) {

            // Desktop-Mobile

            if ($cdr->device == "Desktop-Mobile") {
                $total_desktop += 1;

                if (!in_array($cdr->user_id, $distinct_participants)) {
                    $total_distinct_desktop += 1;
                    $distinct_participants [] = $cdr->user_id;
                }
            }

            // H323

            elseif ($cdr->device == "H323") {
                $total_h323 += 1;
                if (!in_array($cdr->user_id, $distinct_participants)) {
                    $total_distinct_h323 += 1;
                    $distinct_participants [] = $cdr->user_id;
                }
            }
        }

        // Update statistics for quarter ID
        DB::table('statistics_daily')->where('id', $current_five_minute_id)->increment('distinct_users_no_desktop', $total_distinct_desktop);
        DB::table('statistics_daily')->where('id', $current_five_minute_id)->increment('users_no_desktop', $total_desktop);
        DB::table('statistics_daily')->where('id', $current_five_minute_id)->increment('distinct_users_no_h323', $total_distinct_h323);
        DB::table('statistics_daily')->where('id', $current_five_minute_id)->increment('users_no_h323', $total_h323);

        return $total_distinct_desktop;
    }


    /**
     * @param $conference_id_to_pull_participants
     */
    public function add_participants_from_other_conference($conference_id_to_pull_participants){
        $initial_conference = Conference::findOrFail(intval($conference_id_to_pull_participants));
        $participants = $initial_conference->participants;
        if ($initial_conference->participants->count() > 0) {
            foreach ($participants as $participant) {
                // Assign participant to new conference
                if (!$participant->deleted) {
                    // Assign device to participant
                    $this->participants()->save($participant);
                    $device = $participant->participantValues($initial_conference->id)->device;
                    $enabled = $participant->participantValues($initial_conference->id)->enabled;
                    DB::table('conference_user')->where('conference_id', $this->id)->where('user_id', $participant->id)->update(['device' => $device, 'enabled' => $enabled]);
                    if ($participant->confirmed) {
                        AddRegistrant::dispatch($this,$participant)->onQueue('high');
                    }
                }
            }
        }
    }


    /**
     * @param $start_date
     * @param $end_date
     * @return bool
     */
    public static function ScheduledMaintenanceModeOn($start_date, $end_date)
    {

        $maintenance_startDate = Settings::option('maintenance_start');
        $maintenance_endDate = Settings::option('maintenance_end');

        $start = Carbon::parse($start_date)->timestamp;
        $end = Carbon::parse($end_date)->timestamp;

        // if(Carbon::instance(new DateTime( $start_date ))->between($maintenance_startDate, $maintenance_endDate)){
        if (($maintenance_startDate <= $start && $start <= $maintenance_endDate) || ($maintenance_startDate < $end && $end <= $maintenance_endDate) || ($maintenance_startDate >= $start && $maintenance_endDate <= $end)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $identifier
     * @return string
     */
    public function participantIdentifier($identifier)
    {
        // Add identifier
        DB::table('conference_user')
            ->where('conference_id', $this->id)
            ->where('user_id', Auth::user()->id)
            ->update(['identifier' => $identifier]);

        return 'OK';
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function participantConferenceStatus($user_id)
    {
        return User::findOrFail($user_id)->participantValues($this->id)->in_meeting;
    }

    /**
     *Locks room
     */
    public function lock_room()
    {

        $this->update(['locked' => true]);

        DB::table('conference_user')
            ->where('conference_id', $this->id)
            ->update(['enabled' => 0]);

        $registrant_parameters = [
            "action"=> "deny",
            "registrants"=>[
            ]
        ];

        foreach($this->participants as $participant){
            $registrant_parameters['registrants'][] = ["id"=>$participant->participantValues($this->id)->registrant_id, "email"=>"user".$participant->id."@".env("APP_ALIAS")];
        }


        $zoom_client = new ZoomClient();
        $zoom_client->update_participant_status($registrant_parameters,$this->zoom_meeting_id);
    }

    /**
     * Unlocks room
     */
    public function unlock_room()
    {

        $this->update(['locked' => false]);

        DB::table('conference_user')
            ->where('conference_id', $this->id)
            ->update(['enabled' => 1]);

        $registrant_parameters = [
            "action"=> "approve",
            "registrants"=>[
            ]
        ];
        foreach($this->participants as $participant){
            $registrant_parameters['registrants'][] = ["id"=>$participant->participantValues($this->id)->registrant_id, "email"=>"user".$participant->id."@".env("APP_ALIAS")];
        }

        $zoom_client = new ZoomClient();
        $zoom_client->update_participant_status($registrant_parameters,$this->zoom_meeting_id);

    }

    /**
     * @param $moderator
     * @param $attendee
     * @return string
     * @throws \Exception
     */
    public function geticsFile($moderator, $attendee)
    {

        $uuid1 = Uuid::uuid1()->toString();
        $date_start = $this->start->setTimezone('UTC')->format('Ymd\THis\Z');
        $date_end = $this->end->setTimezone('UTC')->format('Ymd\THis\Z');

        $description = !empty($this->desc) ? $this->desc : null;

        if (!empty($this->descEn)) {
            $description .= '<hr>';
            $description .= $this->descEn;
        }

        $content = 'BEGIN:VCALENDAR' . "\r\n";
        $content .= 'VERSION:2.0' . "\r\n";
        $content .= 'METHOD:REQUEST' . "\r\n";
        $content .= 'PRODID:-//epresence.grnet.gr//iCal Event' . "\r\n";
        $content .= 'X-WR-CALNAME' . $this->title . "\r\n";
        $content .= 'CALSCALE:GREGORIAN' . "\r\n";
        $content .= 'ATTENDEE:' . $attendee->firstname . " " . $attendee->lastname . "\r\n";
        $content .= 'BEGIN:VEVENT' . "\r\n";
        $content .= 'ORGANIZER;CN=' . $moderator->firstname . " " . $moderator->lastname . ':MAILTO:' . $moderator->email . "\r\n";
        $content .= 'UID:' . $uuid1 . "\r\n";
        $content .= 'DTSTAMP:' . Carbon::now()->setTimezone('UTC')->format('Ymd\THis\Z') . "\r\n";
        $content .= 'LOCATION: e:Presence' . "\r\n";
        $content .= 'DESCRIPTION:' . $description . "\r\n";
        $content .= 'URL;VALUE=URI:epresence.grnet.gr' . "\r\n";
        $content .= 'SUMMARY:' . $this->title . "\r\n";
        $content .= 'DTSTART:' . $date_start . "\r\n";
        $content .= 'DTEND:' . $date_end . "\r\n";
        $content .= 'END:VEVENT' . "\r\n";
        $content .= 'END:VCALENDAR' . "\r\n";

        $filename = 'invitation.ics';

        Storage::disk('local')->put('/ics/' . $filename, $content);

        $file_link = '/ics/' . $filename;

        return $file_link;
    }


    /**
     * @return mixed
     */
    public function getAdminsIds()
    {

        $conference_admins_ids = User::whereHas('roles', function ($query) {
            $query->where('name', 'SuperAdmin');
        })->orWhere(function ($outer_query_1) {

            $outer_query_1->whereHas('roles', function ($inst_admins_query) {
                $inst_admins_query->where('name', 'InstitutionAdministrator');
            })->whereHas('institutions', function ($inst_query) {
                $inst_query->where('id', $this->institution_id);
            });

            if ($this->user->hasRole('InstitutionAdministrator') || $this->user->hasRole('SuperAdmin'))
                $outer_query_1->where('id', $this->user_id);


        })->orWhere(function ($outer_query_2) {

            $outer_query_2->whereHas('roles', function ($dept_admins_query) {
                $dept_admins_query->where('name', 'DepartmentAdministrator');
            })->whereHas('departments', function ($department_query) {
                $department_query->where('id', $this->department_id);
            })->where('id', $this->user_id);
        })->pluck('id')->toArray();


        return $conference_admins_ids;
    }

    //Zoom web services
    /**
     * @param $input
     * @return array
     */
    public static function get_zoom_create_parameters($input){

        //Calculate settings

        $startDateTime = $input['start_date'] . ' ' . $input['start_time'] . ':00';
        $start = Carbon::createFromFormat('d-m-Y H:i:s', $startDateTime);

        $instant_activation = Carbon::now()->greaterThanOrEqualTo($start) ? true : false;
        $duration = Carbon::parse($input['start'])->diffInMinutes(Carbon::parse($input['end']));
        $start_time = Carbon::parse($input['start'])->format("Y-m-d\TH:i:s");
        $join_before_host = $instant_activation ? "true" : "false";

        $parameters = [
            "topic" => "conference",
            "type" => "2",
            "start_time" => $start_time,
            "duration" => $duration,
            "timezone" => "Europe/Athens",
            "password" => "",
            "agenda" => "",
            "settings" => [
                "host_video" => "true",
                "participant_video" => "true",
                "cn_meeting" => "false",
                "in_meeting" => "false",
                "join_before_host" => $join_before_host,
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

        return $parameters;
    }

    /**
     * @return array
     */
    public function get_zoom_update_parameters(){

        //Calculate settings

        $start = $this->start;
        $end = $this->end;

        $instant_activation = Carbon::now()->greaterThanOrEqualTo($start) ? true : false;
        $duration = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
        $start_time = Carbon::parse($start)->format("Y-m-d\TH:i:s");
        $join_before_host = $instant_activation ? "true" : "false";

        $parameters = [
            "topic" => "conference-".$this->id,
            "type" => "2",
            "start_time" => $start_time,
            "duration" => $duration,
            "timezone" => "Europe/Athens",
            "password" => "",
            "agenda" => "",
            "settings" => [
                "host_video" => "true",
                "participant_video" => "true",
                "cn_meeting" => "false",
                "in_meeting" => "false",
                "join_before_host" => $join_before_host,
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
                "alternative_hosts" => "",
                "registrants_confirmation_email"=>"false"
            ]
        ];

        return $parameters;
    }

    //Starts zoom meeting
    //enables join before host
    /**
     * @return bool
     */
    public function startConference()
    {

        if (Conference::ScheduledMaintenanceModeOn($this->start, $this->end)) {
            // Do nothing
            return false;
        } else {

            // Enable Scheduled Room

            $parameters = [
                "settings" => [
                    "join_before_host" => "true",
                ]
            ];

            $client = new ZoomClient();
            $client->update_meeting($parameters,$this->zoom_meeting_id);

            $this->update(['room_enabled' => 1]);

            $now = Carbon::now();

            // Add conference to statistics table

            Statistics::create(['conference_id' => $this->id, 'institution_id' => $this->institution_id, 'department_id' => $this->department_id, 'active' => 1, 'created_at' => $now]);

            return true;
        }
    }

    /**
     * @return bool
     */
    public function endConference()
    {
        // Disable Scheduled Room

        $update_parameters = [
            "settings" => [
                "join_before_host" => "false",
            ]
        ];

        //Disable join before host on this meeting

        $zoom_client = new ZoomClient();
        $zoom_client->update_meeting($update_parameters,$this->zoom_meeting_id);

        $update_status_parameters = [
            "action" => "end"
        ];

        //End the zoom schedule meeting

        $zoom_client->update_meeting_status($update_status_parameters,$this->zoom_meeting_id);

        $this->update(['room_enabled' => false]);

        $users_no_desktop = DB::table('conference_user')
            ->where('conference_id', $this->id)
            // ->where('joined_once', 1)
            ->where('device', 'Desktop-Mobile')
            ->count();

        $users_no_h323 = DB::table('conference_user')
            ->where('conference_id', $this->id)
            // ->where('joined_once', 1)
            ->where('device', 'H323')
            ->count();

        // Conference duration
        $duration = $this->start->diffInMinutes($this->end, false);

        //Update Statistics table
        Statistics::where('conference_id', $this->id)
            ->update(['active' => 0, 'duration' => $duration, 'users_no_desktop' => $users_no_desktop, 'users_no_h323' => $users_no_h323, 'updated_at' => Carbon::now()]);

        return true;
    }

    /**
     * @param $user_id
     * @return int
     */
    public function detachParticipant($user_id)
    {
        $user = User::findOrFail($user_id);
        $registrant_id = $user->participantValues($this->id)->registrant_id;

        $zoom_client = new ZoomClient();

        $deny_registrant_parameters = [
            "action" => "cancel",
            "registrants"=>[
                [
                    "id"=>$registrant_id,
                    "email"=>"user".$user->id."@".env("APP_ALIAS")
                ]
            ]
        ];

        $zoom_client->update_participant_status($deny_registrant_parameters,$this->zoom_meeting_id);

        return $this->participants()->detach($user_id);
    }

    /**
     * @param $user_id
     * @param string $status
     * @return bool|mixed|ResponseInterface|null
     */
    public function assignParticipant($user_id,$status = "approve")
    {

        Log::info("Adding ".$user_id." as participant in conference: ".$this->id." api call:");

        $participant = User::findOrFail($user_id);
        $zoom_client = new ZoomClient();

        //Adds registrant

        $first_name = !empty($participant->firstname) ? $participant->firstname : $participant->id.'-'.'first_name';
        $last_name = !empty($participant->lastname) ? $participant->lastname : $participant->id.'-'.'last_name';
        $last_name .= "|".$participant->id;

        $add_registrant_parameters = [
            "email" => "user".$participant->id."@".env("APP_ALIAS"),
            "first_name" =>  $first_name,
            "last_name" => $last_name,
        ];

        Log::info("Add participant request:");
        Log::info(json_encode($add_registrant_parameters));

        $add_participant_response = $zoom_client->add_participant($add_registrant_parameters,$this->zoom_meeting_id);

        Log::info("Add participant response:");
        Log::info(json_encode($add_participant_response));

        //Approves registrant

        $approve_registrant_parameters = [
            "action" => $status,
            "registrants"=>[
                [
                    "id"=>$add_participant_response->registrant_id,
                    "email"=>"user".$participant->id."@".env("APP_ALIAS")
                ]
            ]
        ];

        $zoom_client->update_participant_status($approve_registrant_parameters,$this->zoom_meeting_id);

        return $add_participant_response;
    }

    /**
     * @return mixed
     */
    public function getDialString(){

        $ip_address = config('services.zoom.emea_ip_address');

        $result['h323'] = $ip_address."##".$this->zoom_meeting_id;
        $result['sip'] = $this->zoom_meeting_id."@".$ip_address;

        return $result;
    }


    /**Removes named user (owner of the conference) from h323 prevention group
     * @param null $IP
     */
    public function enableH323Connections($IP = null){

        $named_user = $this->named_user;

        $zoom_client = new ZoomClient();
        $zoom_client->delete_user_from_group([],$named_user->zoom_id,config('services.zoom.h323_disabled_group_id'));

        $delay_in_seconds = config('firewall.open_for');

        $job = (new AddNamedUserToBlockingGroup($this))->delay(now()->addSeconds($delay_in_seconds));
        $jobId =  app(Dispatcher::class)->dispatch($job);

        $redis = Redis::connection();
        $key = "last_job_id_".$named_user->id;

        $redis->set($key, $jobId);

        $this->HandleIpAddressForH323($IP);

    }

    /**
     * @param $ip
     */
    public function HandleIpAddressForH323($ip){

        OpenIpAddressForH323::dispatch($this,$ip);

        //Queue job to close the ip address again

        $delay_in_seconds = config('firewall.open_for');

        $job = (new CloseIpAddressForH323($this,$ip))->delay(now()->addSeconds($delay_in_seconds));
        $jobId =  app(Dispatcher::class)->dispatch($job);

        $redis = Redis::connection();
        $ip_with_dashes = str_replace(".","-",$ip);
        $key = "last_job_id_".$ip_with_dashes;
        $redis->set($key, $jobId);

        Log::info("Added job with key: last_job_id_".$ip_with_dashes);
        Log::info("and value: ".$jobId);
    }

    /**
     * @param $ip
     */
    public function OpenIpAddressForH323($ip){

	    if(config('firewall.protection') == "on") {

            //Opens ip address on firewall

            Log::info("Opening ip address: ".$ip." for conference: ".$this->id);

            $key = new RSA();
            $key->loadKey(file_get_contents(config('firewall.ssh_key')));
            $ssh = new SSH2(config('firewall.host'));

            if (!$ssh->login(config('firewall.username'), $key)) {
                Log::error("Firewall ssh2 connection: Public Key Authentication Failed!");

            }else{
                Log::info("Firewall ssh2 connection: Public key auth successful!");

                $delete_exec_1 = "sudo /sbin/iptables -D FORWARD -p tcp -s " . $ip . " --dport 1720 -j ACCEPT";
                $delete_exec_2 = "sudo /sbin/iptables -D FORWARD -p tcp -s " . $ip . " --dport 5060 -j ACCEPT";

                $insert_exec_1 = "sudo /sbin/iptables -I FORWARD -p tcp -s " . $ip . " --dport 1720 -j ACCEPT";
                $insert_exec_2 = "sudo /sbin/iptables -I FORWARD -p tcp -s " . $ip . " --dport 5060 -j ACCEPT";

                Log::info("Executing: ".$delete_exec_1);
                $response = $ssh->exec($delete_exec_1);

                if(empty($response)){
                    Log::info("Exec is Successful!");
                }else{
                    Log::error("Exec error: ".$response);
                }

                Log::info("Executing: ".$delete_exec_2);
                $response = $ssh->exec($delete_exec_2);

                if(empty($response)){
                    Log::info("Exec is Successful!");
                }else{
                    Log::error("Exec error: ".$response);
                }

                Log::info("Executing: ".$insert_exec_1);
                $response = $ssh->exec($insert_exec_1);

                if(empty($response)){
                    Log::info("Exec is Successful!");
                }else{
                    Log::error("Exec error: ".$response);
                }

                Log::info("Executing: ".$insert_exec_2);
                $response = $ssh->exec($insert_exec_2);

                if(empty($response)){
                    Log::info("Exec is Successful!");
                }else{
                    Log::error("Exec error: ".$response);
                }
            }
        }
    }

    /**
     * @param $ip
     */
    public function CloseIpAddressForH323($ip){

        //Blocks ip address on firewall

        Log::info("Closing ip address: ".$ip." for conference: ".$this->id);

        if(config('firewall.protection') == "on") {

            $key = new RSA();
            $key->loadKey(file_get_contents(config('firewall.ssh_key')));
            $ssh = new SSH2(config('firewall.host'));

            if (!$ssh->login(config('firewall.username'), $key)) {
                Log::error("Firewall ssh2 connection: Public Key Authentication Failed!");

            }else{

                Log::info("Firewall ssh2 connection: Public key auth successful!");


                $delete_exec_1 = "sudo /sbin/iptables -D FORWARD -p tcp -s " . $ip . " --dport 1720 -j ACCEPT";
                $delete_exec_2 = "sudo /sbin/iptables -D FORWARD -p tcp -s " . $ip . " --dport 5060 -j ACCEPT";


                Log::info("Executing: ".$delete_exec_1);
                $response = $ssh->exec($delete_exec_1);

                if(empty($response)){
                    Log::info("Exec is Successful!");
                }else{
                    Log::error("Exec error: ".$response);
                }

                Log::info("Executing: ".$delete_exec_2);
                $response = $ssh->exec($delete_exec_2);

                if(empty($response)){
                    Log::info("Exec is Successful!");
                }else{
                    Log::error("Exec error: ".$response);
                }
            }
        }
    }

    //Adds named user (owner of the conference) to h323 prevention group

    /**
     *
     */
    public function disableH323Connections(){

        $named_user = $this->named_user;

        $add_user_to_group_params = [
            "members"=>[
                [
                    "id"=>$named_user->zoom_id
                ]
            ]
        ];

        $zoom_client = new ZoomClient();
        $zoom_client->add_user_to_group($add_user_to_group_params,config('services.zoom.h323_disabled_group_id'));
    }


    //Assigns a new named user to the conference, that means we need to delete the old zoom meeting and create a new one with the
    //new named user assigned to the conference
    //re-invite all participants and update conference_user table updating the join urls & registrant ids for every participant
    //This runs only if the conference is not active at the moment

    public function assign_new_named_user($input){

        //Delete old meeting

        $zoom_client = new ZoomClient();
        $zoom_client->delete_meeting($this->zoom_meeting_id);

        //Get the next available named user in line
        //if none found return false

        $next_named_user_in_line = NamedUser::get_next_named_user_in_line($input['start'], $input['end']);

        if($next_named_user_in_line == false){
            return false;
        }

        $input['named_user_id'] = $next_named_user_in_line->id;

        $create_parameters = Conference::get_zoom_create_parameters($input);

        //Create a new one

        $create_meeting_info = $zoom_client->create_meeting($create_parameters,$next_named_user_in_line->zoom_id);

        $input['join_url'] = $create_meeting_info->join_url;
        $input['start_url'] = $create_meeting_info->start_url;
        $input['zoom_meeting_id'] = $create_meeting_info->id;

        $this->update($input);

        foreach($this->participants as $participant){

            $add_registrant_parameters = [
                "email" => "user".$participant->id."@".env("APP_ALIAS"),
                "first_name" => $participant->firstname,
                "last_name" => $participant->lastname."|".$participant->id,
            ];

            $add_participant_response = $zoom_client->add_participant($add_registrant_parameters,$this->zoom_meeting_id);

            //Approves registrant

            $approve_registrant_parameters = [
                "action" => "approve",
                "registrants"=>[
                    [
                        "id"=>$add_participant_response->registrant_id,
                        "email"=>"user".$participant->id."@".env("APP_ALIAS")
                    ]
                ]
            ];

            $zoom_client->update_participant_status($approve_registrant_parameters,$this->zoom_meeting_id);
            $join_url = isset($add_participant_response->join_url) ? $add_participant_response->join_url : null;
            $registrant_id = isset($add_participant_response->registrant_id) ? $add_participant_response->registrant_id : null;

            DB::table('conference_user')
                ->where('conference_id', $this->id)
                ->where('user_id', $participant->id)
                ->update(['join_url' => $join_url, 'registrant_id' => $registrant_id]);

        }

        return true;
    }



}
