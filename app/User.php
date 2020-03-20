<?php

namespace App;

use App\Jobs\Conferences\AddRegistrant;
use App\Notifications\MailResetPasswordNotification;
use Asikamiotis\ZoomApiWrapper\ZoomClient;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use SoapClient;
use Response;
use Auth;
use Validator;
use App\Email;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Agent\Agent;

class User extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'password', 'firstname', 'lastname', 'telephone', 'tax_id', 'thumbnail', 'state', 'status', 'creator_id', 'comment', 'email_verified_at', 'admin_comment', 'confirmed', 'activation_token', 'accepted_terms',
    'confirmation_code','civil_servant'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];


    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {

        $this->notify(new MailResetPasswordNotification($token));
    }

    /**
     * A user may have a creator
     */
    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
    }

    /**
     * A user may have created many conferences
     */
    public function emails()
    {
        $emails = ExtraEmail::where('user_id', $this->id)->pluck('email')->toArray();
        $emails[] = $this->email;
        return $emails;
    }


    /**
     * @param $query
     * @return mixed
     */
    public function scopeConfirmed($query){
        return $query->where("confirmed",true);
    }



    /**
     * @return HasMany
     */
    public function extra_emails()
    {
        return $this->hasMany('App\ExtraEmail');
    }

    /**
     * @return Collection
     */
    public function extra_emails_sso()
    {
        return $this->hasMany('App\ExtraEmail')->where('type', 'sso')->get();
    }

    /**
     * @return Collection
     */
    public function extra_emails_custom()
    {
        return $this->hasMany('App\ExtraEmail')->where('type', 'custom')->get();
    }


    /**
     * @return BelongsToMany
     */
    public function conferences()
    {
        return $this->belongsToMany('App\Conference')->withPivot('invited', 'confirmed', 'joined_once','confirmation_code', 'device','identifier','join_url','registrant_id','in_meeting','enabled');
    }

    /**
     * A user may have many applications
     */
    public function applications()
    {
        return $this->hasMany('App\Application');
    }

    /**
     * A user may have many cdrs
     */
    public function cdrs()
    {
        return $this->hasMany('App\Cdr');
    }


    /**
     * A user may have many demo room cdrs
     */
    public function demo_cdrs()
    {
        return $this->hasMany('App\DemoRoomCdr');
    }


    // User Apply for role

    /**
     * @param $application
     * @return Model
     */
    public function applyFor($application)
    {
        return $this->applications()->create($application);
    }

    /**
     * A user may have created many institutions
     */
    public function institutions()
    {
        return $this->belongsToMany('App\Institution');
    }

    /**
     * @return mixed
     */
    public function otherInstitutionAdmins()
    {
        $admins = $this->institutions()->first()->institutionAdmins()->except($this->id);

        return $admins;
    }

    /**
     * A user may have created many departments
     */
    public function departments()
    {
        return $this->belongsToMany('App\Department');
    }

    /**
     * @return mixed
     */
    public function get_institutions()
    {
        return $institutions = Institution::pluck('title', 'id')->toArray();

    }

    /**
     * @return mixed
     */
    public function get_departments()
    {
        return $departments = Department::pluck('title', 'id')->toArray();
    }

    /**
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @param $role
     * @return Model
     */
    public function assignRole($role)
    {
        return $this->roles()->save(
            Role::whereName($role)->firstOrFail()
        );
    }

    /**
     * @param $role
     * @return int
     */
    public function removeRole($role)
    {
        return $this->roles()->detach(
            Role::whereName($role)->firstOrFail()
        );
    }


    /**
     * @param $email_address
     * @return mixed
     */
    public function CreateNewExtraMail_sendConfirmationLink($email_address)
    {
        $activation_token = str_random(16);
        $new_extra_email = new ExtraEmail;
        $new_extra_email->user_id = $this->id;
        $new_extra_email->email = $email_address;
        $new_extra_email->type = 'custom';
        $new_extra_email->confirmed = 0;
        $new_extra_email->activation_token = $activation_token;
        $new_extra_email->token_updated = Carbon::now();

        $new_extra_email->save();

        //Check if user is enabled

        if($new_extra_email->user->status == 1){

            $email = Email::where("name", "extraEmailConfirmation")->first();

            $parameters['activation_link'] = URL::to("email_activation") . '/' . $activation_token;

            Mail::send('emails.ExtraEmailConfirmation', $parameters, function ($message) use ($email_address, $email) {

                $message->from($email->sender_email, env('MAIL_FROM_NAME'))
                    ->to($email_address)
                    ->replyTo(env('MAIL_FROM_ADDRESS'))
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });
        }

        return $new_extra_email->id;
    }


    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        foreach ($role as $r) {
            if ($this->hasRole($r->name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function customValues()
    {
        $customValues = ['institution' => null, 'department' => null];

        if ($this->custom_values != null) {
            $customValues = ['institution' => htmlspecialchars_decode(json_decode($this->custom_values)->institution, ENT_QUOTES), 'department' => htmlspecialchars_decode(json_decode($this->custom_values)->department, ENT_QUOTES)];
        }

        return $customValues;
    }

    /**
     * @param $newPassword
     * @return string
     */
    public function createNewPassword($newPassword)
    {

        $userPassword = bcrypt($newPassword);

        $this->update(['password' => $userPassword]);

        return 'OK';
    }

    /**
     * @param $status
     * @return string
     */
    public function status_icon($status)
    {

        if ($status == 1) {
            return 'glyphicon-ok';
        } else {
            return 'glyphicon-ban-circle';
        }
    }

    /**
     * @param $status
     * @return string
     */
    public function status_button($status)
    {

        if ($status == 1) {
            return 'success';
        } else {
            return 'danger';
        }
    }

    /**
     * @param $status
     * @return array|Translator|string|null
     */
    public function status_string($status)
    {

        if ($status == 1) {
            return trans('application.active');
        } else {
            return trans('application.inactive');
        }
    }

    /**
     * @return false|string
     */
    public function statusUsersTableButton()
    {

        $icon = 'glyphicon-trash';
        $btn_bg = 'btn-danger';
        $tooltipText = trans('application.deleteUser');

        // Cannot delete a user if had at least one conference
        if (!$this->canBeDeleted()) {
            switch ($this->status) {
                case 1:
                    $icon = 'glyphicon-ban-circle';
                    $tooltipText = trans('application.deactivateUser');
                    break;
                case 0:
                    $icon = 'glyphicon-ok';
                    $btn_bg = 'btn-success';
                    $tooltipText = trans('application.activateUser');
                    break;
            }
        }

        $json = ['icon' => $icon, 'btn_bg' => $btn_bg, 'tooltipText' => $tooltipText];
        return json_encode($json);
    }

    /**
     * @return false|string
     */
    public function statusDisableUsersTableButton()
    {

        $icon = '';
        $btn_bg = 'btn-danger';
        $tooltipText = '';

        switch ($this->status) {
            case 1:
                $icon = 'glyphicon-ban-circle';
                $tooltipText = trans('application.deactivateUser');
                break;
            case 0:
                $icon = 'glyphicon-ok';
                $btn_bg = 'btn-success';
                $tooltipText = trans('application.activateUser');
                break;
        }

        $json = ['icon' => $icon, 'btn_bg' => $btn_bg, 'tooltipText' => $tooltipText];
        return json_encode($json);
    }

    /**
     * @param $state
     * @return string
     */
    public function state_icon($state)
    {

        if ($state == 'local') {
            return 'glyphicon-user';
        } elseif ($state == 'sso') {
            return 'glyphicon-cloud';
        }
    }

    /**
     * @param $state
     * @return array|Translator|string|null
     */
    public function state_string($state)
    {

        if ($state == 'local') {
            return trans('application.yes');
        } elseif ($state == 'sso') {
            return trans('application.no');
        }
    }

    /**
     * @param $status
     * @return string
     */
    public function check_icon($status)
    {

        if ($status == 1) {
            return 'glyphicon-ok';
        } else {
            return 'glyphicon-ban-circle';
        }
    }

    /**
     * @return bool
     */
    public function canBeDeleted()
    {
        $activeParticipant = DB::table('conference_user')->where('user_id', $this->id)->where('joined_once', 1)->count();
        // Cannot delete a user if had at least one conference
        if ($activeParticipant > 0 || $this->conferenceAdmin()->count() > 0) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function deleteUnconfirmedUser()
    {
        // Delete user
        if ($this->hasRole('DepartmentAdministrator') || $this->hasRole('InstitutionAdministrator')) {
            $active_or_future_conferences = $this->activeFutureConferences();
            foreach ($active_or_future_conferences as $conference) {
                if ($conference->room_enabled == 1) {
                } elseif ($conference->room_enabled == 0 && $conference->start > Carbon::now()) {
                    $conference->cancelConferenceEmail();
                }
                $conference->delete();
            }
        }
        $this->delete();
        return 'OK';
    }


    /** Emails to be send
     * @param $password
     */
    public function email_for_new_account($password)
    {
        $user = User::findOrFail($this->id);
        $email = Email::where('name', 'userAccountEnableSso')->first();
        $login_url = URL::to("register/" . $user->activation_token);
        $email_view = 'emails.enable_account_sso';
        $creator = User::findOrFail($this->creator_id);
        $parameters = array('body' => $email->body, 'user' => $user, 'password' => $password, 'login_url' => $login_url, 'account_url' => URL::to("account"));
        Mail::send($email_view, $parameters, function ($message) use ($user, $email, $creator) {
            $message->from($email->sender_email, config('mail.from.name'))
                ->to($user->email)
                ->replyTo($creator->email, $creator->firstname . ' ' . $creator->lastname)
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });
    }


    /**
     * @param $conference_id
     * @return bool
     */
    public function hasConference($conference_id)
    {
        if (is_numeric($conference_id)) {
            return $this->conferences->contains('id', $conference_id);
        }

        return !!$conference_id->intersect($this->$conference_id)->count();
    }

    /**
     * @return HasMany
     */
    public function conferenceAdmin()
    {
        return $this->hasMany('App\Conference');
    }

    /**
     * @return Collection
     */
    public function pastConferences()
    {
        $now = Carbon::now();

        $conferences = $this->conferences()->where('start', '<=', $now)
            ->where('room_enabled', 0)
            ->withPivot('joined_once')
//            ->whereIn('institution_id', $this->institutions()->pluck('id'))

            ->get();

        return $conferences;
    }

    /**
     * @return mixed
     */
    public function institutionPastConferences()
    {
        $now = Carbon::now();

        $conferences = Conference::where('start', '<', $now)
            ->where('room_enabled', 0)
            ->whereIn('institution_id', $this->institutions()->pluck('id')->toArray())
            ->get();

        return $conferences;
    }


    /**
     * @return Collection
     */
    public function futureAdminConferences()
    {
        $now = Carbon::now();
        $conferences = $this->conferenceAdmin()->where('start', '>=', $now)->orderBy('start', 'asc')->get();

        return $conferences;
    }

    /**
     * @return bool
     */
    public function HasFutureAdminConferences()
    {
        return count($this->activeFutureConferences()) > 0 ? true : false;
    }


    /**
     * @return Collection
     */
    public function activeFutureConferences()
    {

        $now = Carbon::now();

        $conferences = $this->conferenceAdmin()->where(function ($query) use ($now) {
            $query->where('room_enabled', 1)->orWhere('start', '>=', $now);
        })->get();

        return $conferences;
    }


    /**Check if user has admin privileges on conference
     * @param $conference
     * @return bool
     */
    public function hasAdminAccessToConference($conference)
    {

        return
            $this->hasRole('SuperAdmin') ||
            ($this->hasRole('InstitutionAdministrator') && $this->institutions()->first()->id === $conference->institution_id) && ($conference->user->hasRole('DepartmentAdministrator') || $this->owns($conference)) ||
            ($this->hasRole('DepartmentAdministrator') && $this->owns($conference)) ? true : false;
    }


    /**
     * @return mixed
     */
    public function futureConferences()
    {
        //Get future conference that this user is participant

        $now = Carbon::now();

        $conferences = Conference::where('start', '>', $now)
            ->where(function ($query) {
                $query->whereHas('participants', function ($query2) {
                    $query2->where('user_id', $this->id);
                });

            })
            ->orderBy('start', 'asc')
            ->get();

        return $conferences;
    }


    /**
     * @return mixed
     */
    public function activeConferences()
    {
        if ($this->hasRole('SuperAdmin')) {
            $conferences = Conference::where('room_enabled', 1)->orderBy('start', 'asc')->get();
        } elseif ($this->hasRole('InstitutionAdministrator')) {
            $conferences = Conference::where('room_enabled', 1)
                ->where(function ($query) {
                    $query->whereHas('participants', function ($query2) {
                        $query2->where('user_id', $this->id);
                    })->orWhere('user_id', $this->id)->orWhere(function($query3){
                        $query3->where('institution_id', Auth::user()->institutions()->first()->id)
                            ->whereHas('user',function($user_query){

                            $user_query->whereHas('roles',function($role_query){
                                $role_query->where('name','DepartmentAdministrator');
                            });
                        });

                    });
                })
                ->orderBy('start', 'asc')
                ->get();
        } elseif ($this->hasRole('DepartmentAdministrator')) {
            $conferences = Conference::where('room_enabled', 1)
                ->where(function ($query) {
                    $query->whereHas('participants', function ($query2) {
                        $query2->where('user_id', $this->id);
                    })->orWhere('user_id', $this->id);
                })
                ->orderBy('start', 'asc')
                ->get();
        } else {
            $conferences = Conference::where('room_enabled', 1)
                ->where(function ($query) {
                    $query->whereHas('participants', function ($query2) {
                        $query2->where('user_id', $this->id);
                    })
                        ->orWhere('user_id', $this->id);
                })
                ->orderBy('start', 'asc')
                ->get();
        }

        return $conferences;
    }

    /**
     * @return mixed
     */
    public function participantInConferences()
    {
        $conferences = Conference::where(function ($query) {
            $query->whereHas('participants', function ($query2) {
                $query2->where('user_id', $this->id);
            });
        })
            ->get();

        return $conferences;
    }

    /**
     * @return mixed
     */
    public function hasParticipatedInConferences()
    {
        $conferences = Conference::where(function ($query) {
            $query->whereHas('participants', function ($query2) {
                $query2->where('user_id', $this->id)
                    ->where('joined_once', 1);
            });
        })
            ->get();

        return $conferences;
    }

    /**
     * @param $relate
     * @return bool
     */
    public function owns($relate)
    {
        return $this->id == $relate->user_id;
    }

    /**
     * @param $relate
     * @return bool
     */
    public function is_user($relate)
    {
        return $this->id == $relate->id;
    }

    /**
     * @param $users
     * @param $input
     * @return mixed
     */
    public static function advancedSearch($users, $input)
    {
        $user_advanced_search = ['firstname', 'lastname', 'state', 'status', 'multi_mails', 'confirmed', 'accepted_terms', 'telephone'];
        if (Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('InstitutionAdministrator')) {
            $user_advanced_search = ['firstname', 'lastname', 'email', 'state', 'status', 'application', 'multi_mails', 'confirmed', 'accepted_terms', 'telephone'];
        }
        $relation_advanced_search = ['institution', 'department', 'role'];
        $sorting = 0;
        foreach ($input as $k => $v) {

            if ($v === null || $v === "")
                unset($input[$k]);

        }
        //Ignore institution id since department is filled

        if (isset($input['department']) && isset($input['institution']))
            unset($input['institution']);


        foreach ($input as $k => $v) {
            if (in_array($k, $user_advanced_search)) {
                if ($k == 'email' && !empty($v)) {
                    $users = $users->where(function ($query) use ($v) {
                        $query->orwhere('email', 'like', '%' . escape_like($v) . '%')
                            ->orwhereHas(
                                'extra_emails', function ($query) use ($v) {
                                $query->where('email', 'like', '%' . escape_like($v) . '%');
                            });
                    });
                } elseif ($k == 'status') {
                    //Handle status filter

                    $users = $users->where('status', $v);
                } elseif ($k == 'confirmed') {
                    //Handle confirmed filter
                    $users = $users->where('confirmed', $v);
                } elseif ($k == 'accepted_terms') {
                    //Handle accepted terms filter

                    if ($v == 1)
                        $users = $users->whereNotNull('accepted_terms');
                    else
                        $users = $users->whereNull('accepted_terms');

                } elseif ($k == 'telephone') {

                    //Handle telephone filter
                    //  $users = $users->where('telephone', $v);

                    $users = $users->where('telephone', 'like', '%' . escape_like($v) . '%');


                } elseif ($k == 'multi_mails') {

                    //Handle multiple emails filter

                    if ($v == 0)
                        $users = $users->whereDoesntHave('extra_emails');
                    else
                        $users = $users->has('extra_emails');


                } elseif (!empty($v) && $k !== 'status' && $k !== 'email' && $k !== 'multi_mails') {
                    $users = $users->where($k, 'like', '%' . escape_like($v) . '%');
                }

            } elseif ($k == 'created_at_from' && !empty($v)) {

                $from = Carbon::now()->subYears(50);
                $to = Carbon::now()->addYear();
                if (!empty($input['created_at_from'])) {
                    $from = Carbon::createFromFormat('d-m-Y', $input['created_at_from'])->startOfDay()->toDateTimeString();
                }
                if (!empty($input['created_at_to'])) {
                    $to = Carbon::createFromFormat('d-m-Y', $input['created_at_to'])->endOfDay()->toDateTimeString();
                }

                $users = $users->whereBetween('created_at', [$from, $to]);
            } elseif (in_array($k, $relation_advanced_search) && !empty($v)) {
                $users = $users->whereHas(
                    $k . 's', function ($query) use ($v) {
                    $query->where('id', intval($v));
                }
                );

            } elseif (str_contains($k, 'sort_') && !empty($v)) {

                $sort = substr($k, 5);
                if ($sort == 'createdAt') {
                    $sort = snake_case('createdAt');
                }
                $users = $users->orderBy($sort, $v);
                $sorting++;
            }
        }

        if ($sorting == 0) {
            $users = $users->orderBy('lastname', 'asc');
        }

        return $users;
    }

    /**
     * @param $mails
     */
    public function manage_sso_extra_emails($mails)
    {

        foreach ($mails as $k => $mail) {
            $email = new ExtraEmail;
            $email->email = $mail;
            $email->type = 'sso';
            $email->confirmed = 1;
            $email->user_id = $this->id;
            $email->save();
        }
    }


    /**
     * @param $input
     */
    public function update_institution_department($input)
    {
        // Update institution and department ids

        $user = User::findOrFail($this->id);
        if (isset($input['new_institution'])) {
            $input['new_institution'] = htmlspecialchars($input['new_institution'], ENT_QUOTES);
        }

        if (isset($input['new_department'])) {
            $input['new_department'] = htmlspecialchars($input['new_department'], ENT_QUOTES);
        }

        if ($user->hasRole('SuperAdmin')) {
            $institution = Institution::where('slug', 'grnet')->first();
            $institution_id = $institution->id;
            $department_id = $institution->adminDepartment()->id;
            $custom_values = '';
        } else {
            switch (true) {
                /* EndUser */
                case ($input['institution_id'] == 'other' && $user->hasRole('EndUser')):
                    $institution = Institution::where('slug', 'other')->first();
                    $department_id = $institution->otherDepartment()->id;
                    $custom_values = '{"institution": "' . $input['new_institution'] . '", "department": "' . $input['new_department'] . '"}';
                    break;
                case ($input['institution_id'] != 'other' && isset($input['department_id']) && $input['department_id'] == 'other' && $user->hasRole('EndUser'));
                    $institution = Institution::findOrFail($input['institution_id']);
                    $department_id = $institution->otherDepartment()->id;
                    $custom_values = '{"institution": null, "department": "' . $input['new_department'] . '"}';
                    break;
                case ($input['institution_id'] != 'other' && isset($input['department_id']) && $input['department_id'] != 'other' && $user->hasRole('EndUser'));
                    $institution = Institution::findOrFail($input['institution_id']);
                    if (empty($input['department_id'])) {
                        $department_id = $institution->departments()->first()->id;
                    } else {
                        $department_id = $input['department_id'];
                    }
                    $custom_values = '';
                    break;

                /* DepartmentAdministrator */
                case ($input['institution_id'] == 'other' && $user->hasRole('DepartmentAdministrator') && !isset($input['application'])):
                    $institution = Institution::where('slug', 'other')->first();
                    $department_id = $institution->otherDepartment()->id;
                    $custom_values = '{"institution": "' . $input['new_institution'] . '", "department": "' . $input['new_department'] . '"}';
                    break;
                case ($input['institution_id'] != 'other' && isset($input['department_id']) && $input['department_id'] == 'other' && $user->hasRole('DepartmentAdministrator') && !isset($input['application']) && (Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('InstitutionAdministrator'))):
                    $institution = Institution::findOrFail($input['institution_id']);
                    $new_department = Department::create(['title' => $input['new_department'], 'slug' => 'noID', 'institution_id' => $institution->id]);
                    $department_id = $new_department->id;
                    $custom_values = '';
                    break;
                case ($input['institution_id'] != 'other' && isset($input['department_id']) && $input['department_id'] == 'other' && $user->hasRole('DepartmentAdministrator') && !isset($input['application']) && (!Auth::user()->hasRole('SuperAdmin') && !Auth::user()->hasRole('InstitutionAdministrator'))):
                    $institution = Institution::findOrFail($input['institution_id']);
                    $department_id = $institution->otherDepartment()->id;
                    $custom_values = '{"institution": null, "department": "' . $input['new_department'] . '"}';
                    break;
                case ($input['institution_id'] != 'other' && isset($input['department_id']) && $input['department_id'] != 'other' && $user->hasRole('DepartmentAdministrator') && !isset($input['application'])):
                    $institution = Institution::findOrFail($input['institution_id']);
                    if (empty($input['department_id'])) {
                        $department_id = $input['department_id_current'];
                    } else {
                        $department_id = $input['department_id'];
                    }
                    $custom_values = '';
                    break;
                // Application notAccepted
                case ($input['institution_id'] == 'other' && $user->hasRole('DepartmentAdministrator') && isset($input['application']) && $input['application'] != 'accepted'):
                    $institution = Institution::where('slug', 'other')->first();
                    $department_id = $institution->otherDepartment()->id;
                    $custom_values = '{"institution": "' . $input['new_institution'] . '", "department": "' . $input['new_department'] . '"}';
                    break;
                case ($input['institution_id'] != 'other' && isset($input['department_id']) && $input['department_id'] == 'other' && $user->hasRole('DepartmentAdministrator') && isset($input['application']) && $input['application'] != 'accepted'):
                    $institution = Institution::findOrFail($input['institution_id']);
                    $department_id = $institution->otherDepartment()->id;
                    $custom_values = '{"institution": null, "department": "' . $input['new_department'] . '"}';
                    break;
                case ($input['institution_id'] != 'other' && isset($input['department_id']) && $input['department_id'] != 'other' && $user->hasRole('DepartmentAdministrator') && isset($input['application'])):
                    $institution = Institution::findOrFail($input['institution_id']);
                    if (empty($input['department_id'])) {
                        $department_id = $input['department_id_current'];
                    } else {
                        $department_id = $input['department_id'];
                    }
                    $custom_values = '';
                    break;
                // Application Accepted
                case ($input['institution_id'] == 'other' && $user->hasRole('DepartmentAdministrator') && isset($input['application']) && $input['application'] == 'accepted'):
                    $institution = Institution::create(['title' => $input['new_institution'], 'slug' => 'noID']);
                    // Create admin department
                    Department::create(['title' => 'Διοίκηση', 'slug' => 'admin', 'institution_id' => $institution->id]);
                    // Create other department
                    Department::create(['title' => 'Άλλο', 'slug' => 'other', 'institution_id' => $institution->id]);
                    $new_department = Department::create(['title' => $input['new_department'], 'slug' => 'noID', 'institution_id' => $institution->id]);
                    $department_id = $new_department->id;
                    $custom_values = '';
                    break;
                case ($input['institution_id'] != 'other' && isset($input['department_id']) && $input['department_id'] == 'other' && $user->hasRole('DepartmentAdministrator') && isset($input['application']) && $input['application'] == 'accepted'):
                    $institution = Institution::findOrFail($input['institution_id']);
                    $new_department = Department::create(['title' => $input['new_department'], 'slug' => 'noID', 'institution_id' => $institution->id]);
                    $department_id = $new_department->id;
                    $custom_values = '';
                    break;

                /* InstitutionAdministrators */
                case ($input['institution_id'] == 'other' && $user->hasRole('InstitutionAdministrator') && !isset($input['application'])):
                    $institution = Institution::where('slug', 'other')->first();
                    $department_id = $institution->adminDepartment()->id;
                    $custom_values = '{"institution": "' . $input['new_institution'] . '", "department": "Διοίκηση"}';
                    break;
                case ($input['institution_id'] != 'other' && $user->hasRole('InstitutionAdministrator')):
                    $institution = Institution::findOrFail($input['institution_id']);
                    $department_id = $institution->adminDepartment()->id;
                    $custom_values = '';
                    break;
                // Application Accepted
                case ($input['institution_id'] == 'other' && $user->hasRole('InstitutionAdministrator') && isset($input['application']) && $input['application'] == 'accepted'):
                    $institution = Institution::create(['title' => $input['new_institution'], 'slug' => 'noID']);
                    // Create admin department
                    $new_department = Department::create(['title' => trans('application.administration'), 'slug' => 'admin', 'institution_id' => $institution->id]);
                    // Create other department
                    Department::create(['title' => trans('application.other'), 'slug' => 'other', 'institution_id' => $institution->id]);
                    $department_id = $new_department->id;
                    $custom_values = '';
                    break;
                // Application notAccepted
                case ($input['institution_id'] == 'other' && $user->hasRole('InstitutionAdministrator') && isset($input['application']) && $input['application'] != 'accepted'):
                    $institution = Institution::where('slug', 'other')->first();
                    $department_id = $institution->adminDepartment()->id;
                    $custom_values = '{"institution": "' . $input['new_institution'] . '", "department": "' . trans('application.administration') . '"}';
                    break;
            }
        }

        // Attach new institution and department
        if (!isset($input['institution_id_current']) && !isset($input['institution_id_current'])) {
            $user->institutions()->attach($institution->id);
            $user->departments()->attach($department_id);
        } elseif ($user->institutions->count() == 0 && $user->departments->count() == 0) {
            $user->institutions()->attach($institution->id);
            $user->departments()->attach($department_id);
        } elseif (!empty($input['institution_id_current']) && empty($input['department_id_current'])) {
            $user->institutions()->updateExistingPivot($input['institution_id_current'], ['institution_id' => $institution->id], true);
            $user->departments()->attach($department_id);
        } else {
            $user->institutions()->updateExistingPivot($input['institution_id_current'], ['institution_id' => $institution->id], true);
            $user->departments()->updateExistingPivot($input['department_id_current'], ['department_id' => $department_id], true);
        }

        $user->update(['custom_values' => $custom_values]);

        // End attach institution and departmant to user

        // return 'OK';

    }


    /**
     * @param $exceptRoles
     * @param $id
     * @return array
     */
    public static function role_dropdown($exceptRoles, $id)
    {
        $role_dropdown=[];
        $roles = Role::whereNotIn('name', $exceptRoles)->orderBy('id')->get();

        foreach ($roles as $role) {
            if ($id == 'id') {
                $role_dropdown [$role->id] = trans($role['label']);
            } elseif ($id == 'name') {
                $role_dropdown [$role->name] = trans($role['label']);
            }
        }

        return $role_dropdown;
    }


    /**
     * @param $conference_id
     * @return mixed
     */
    public function participantValues($conference_id)
    {
        return DB::table('conference_user')
            ->where('conference_id', $conference_id)
            ->where('user_id', $this->id)
            ->first();
    }


    /**
     * @param $date
     * @return string
     */
    public function getDate($date)
    {
        return Carbon::parse($date)->format('d-m-Y');
    }


    public function is_mobile()
    {
        $agent = new Agent();

        return $agent->isMobile() || $agent->isTablet()  ? false : true;
    }


    public static function getUserOS()
    {
        $os = '';
        $UserAgent = $_SERVER['HTTP_USER_AGENT'];

        // Mobile OS
        if (strpos($UserAgent, 'iPhone')) {
            $os = 'iPhone';
        } elseif (strpos($UserAgent, 'iPod')) {
            $os = 'iPod';
        } elseif (strpos($UserAgent, 'iPad')) {
            $os = 'iPad';
        } elseif (strpos($UserAgent, 'Android')) {
            $os = 'Android';
        } elseif (strpos($UserAgent, 'Windows Phone')) {
            $os = 'Windows Phone';
        } elseif (strpos($UserAgent, 'BlackBerry')) {
            $os = 'BlackBerry';
        } // Desktop OS
        elseif (strpos($UserAgent, 'Windows') || strpos($UserAgent, 'WINDOWS')) {
            $os = 'Windows';
        } elseif (strpos($UserAgent, 'Mac') || strpos($UserAgent, 'MAC')) {
            $os = 'Mac';
        } elseif (strpos($UserAgent, 'Linux') || strpos($UserAgent, 'LINUX')) {
            $os = 'Linux';
        } else {
            $os = "Unidentified";
        }
        return $os;
    }


    /**
     * @return string
     */
    public static function getUserAgent()
    {
        $UserAgent = $_SERVER['HTTP_USER_AGENT'];
        $user_agent_code = 'Not found';
        if ((strpos($UserAgent, 'Windows NT 6.1') || strpos($UserAgent, 'Windows NT 6.2') || strpos($UserAgent, 'Windows NT 6.3') || strpos($UserAgent, 'Windows NT 10.0')) && strpos($UserAgent, 'Chrome')) {
            //Windows 7 and later with chrome 48 and later
            $user_agent_code = 'windows7_chrome';
        } elseif (strpos($UserAgent, 'Windows') && strpos($UserAgent, 'Firefox')) {
            //Any windows version with firefox 45 and later
            $user_agent_code = 'windows_firefox';
//            $user_agent_code = 'not_supported';
        } elseif (strpos($UserAgent, 'Safari')) {
            // MAC OS x 10.8 - 10.11.4  with safari 9.0.3 and later
            $user_agent_code = 'mac_safari';

        } elseif (strpos($UserAgent, 'Trident') && strpos($UserAgent, 'rv:11')) {
            // IE
            $user_agent_code = 'windows_ie';
        } elseif (strpos($UserAgent, 'Edge')) {
            $user_agent_code = 'not_supported';
        } else {
            $user_agent_code = 'not_supported';
        }

        return $user_agent_code;

    }

    /**
     * @return bool
     */
    public static function ChromeOrNot()
    {
        $UserAgent = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($UserAgent, 'Chrome')) {

            //Windows 7 and later with chrome 48 and later
            $user_agent_code = true;

        } else {
            $user_agent_code = false;
        }

        return $user_agent_code;
    }

    /**
     * Create Join Urls
     */
    public function create_join_urls(){
        foreach($this->conferences()->get() as $conference){
            if(empty($conference->pivot->registrant_id) && empty($conference->pivot->join_url)){
                AddRegistrant::dispatch($conference,$this)->onQueue('high');
            }
        }
    }


    /**
     * @param $registrants_response
     * @param $registrant_id
     * @return |null
     */
    public function match_with_registrant($registrants_response,$registrant_id){
        if(isset($registrants_response->registrants) && count($registrants_response->registrants) > 0){
            foreach($registrants_response->registrants as $registrant){
                if(!empty($registrant_id) && isset($registrant->id) && !empty($registrant->id) &&  $registrant_id == $registrant->id){
                    return $registrant->join_url;
                }else{
                    $exploded_last_name = explode("|",$registrant->last_name);
                    if(count($exploded_last_name) == 2){
                        $user_id = $exploded_last_name[1];
                        if($user_id == $this->id){
                            return $registrant->join_url;
                        }
                    }
                }
            }
        }
        return null;
    }


    /** Merges two users
     * @param $user_id_to_delete
     * @param bool $create_extra_email
     * @return bool
     */
    public function merge_user($user_id_to_delete,$create_extra_email = true){
        $userToDelete = User::findOrFail($user_id_to_delete);

        /**
         * UPDATE conferences created by user to be deleted
         */

        $institution = $this->institutions()->first();
        $department = $this->departments()->first();
        Conference::where('user_id', $userToDelete->id)->update(["user_id"=>$this->id,'institution_id'=>$institution->id,'department_id'=>$department->id]);

        //UPDATE users created by user to be deleted

        User::where('creator_id', $userToDelete->id)->update(["creator_id"=>$this->id]);

        //Update conferences_user table where user to be deleted is invited

        $this->merge_conference_user_records($userToDelete);

        //Create deleted users primary email as extra email on the kept user

        if($create_extra_email){
            $existing_user_emails = $this->emails();
            if(!in_array($userToDelete->email,$existing_user_emails)){
                ExtraEmail::create(["user_id"=>$this->id,"email"=>$userToDelete->email,"type"=>"custom","confirmed"=>true]);
            }
        }

        Cdr::where("user_id",$userToDelete->id)->update(["user_id"=>$this->id]);
        //   ExtraEmail::where("user_id",$userToDelete->id)->update(["user_id"=>$userToKeep->id]);
        Application::where("user_id",$userToDelete->id)->update(["user_id"=>$this->id]);
        DemoRoomCdr::where("user_id",$userToDelete->id)->update(["user_id"=>$this->id]);

        if(DB::table("demo_room_connections")->where("user_id",$userToDelete->id)->exists()){
            $demo_room_connections =  DB::table("demo_room_connections")->where("user_id",$userToDelete->id)->value("total_connections");

            if(DB::table("demo_room_connections")->where("user_id",$this->id)->exists())
                DB::table("demo_room_connections")->where("user_id",$this->id)->increment("total_connections",$demo_room_connections);
            else
                DB::table("demo_room_connections")->insert(["total_connections"=>$demo_room_connections,"user_id"=>$this->id,"last_month_connections"=>0]);

        }

        $userToDelete->delete();
        return true;
    }

    /**
     * @param $userToDelete
     */
    private function merge_conference_user_records($userToDelete){
        $zoom_client = new ZoomClient();
        $conferences_invited = Db::table('conference_user')->where('user_id', $userToDelete->id)->get();
        foreach ($conferences_invited as $row) {
            $conference = Conference::find($row->conference_id);
            if(!$conference->isParticipant($this)){
                $update_parameters = ['user_id' => $this->id];
                if($conference->end->gt(Carbon::now())){
                    $cancel_registrant_parameters = [
                        "action" => "cancel",
                        "registrants" => [
                            [
                                "id" => $row->registrant_id,
                                "email" => "user" . $userToDelete->id . "@" . env("APP_ALIAS")
                            ]
                        ]
                    ];
                    $zoom_client->update_participant_status($cancel_registrant_parameters, $conference->zoom_meeting_id);
                    $add_participant_response = $conference->assignParticipant($this->id);
                    $update_parameters['join_url'] = isset($add_participant_response->join_url) ? $add_participant_response->join_url : null;
                    $update_parameters['registrant_id']  = isset($add_participant_response->registrant_id) ? $add_participant_response->registrant_id : null;
                }
                DB::table('conference_user')
                    ->where('user_id', $row->user_id)
                    ->where('conference_id', $row->conference_id)
                    ->update($update_parameters);
            }
        }
    }

    /**
     *Checks if user has entered a email address on his account
     *
     * @return bool
     */
    public function hasEmailAddress(){
        return strpos($this->email,'@') !== false && !empty($this->email);
    }


    /**
     * @return bool
     */
    public function canRequestRoleUpgrade(){
      return  $this->civil_servant && Application::where('user_id', $this->id)->where('app_state', 'new')->count() == 0 && ($this->hasRole('DepartmentAdministrator') || $this->hasRole('EndUser'));
    }
}
