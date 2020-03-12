<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\ExtraEmail;
use App\User;
use App\Conference;
use App\Institution;
use App\Department;
use App\Email;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use App\Http\Requests;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['accountActivation', 'new_sso_account', 'store_new_sso_user', 'sendConfirmationEmailSSO', 'set_cookie']]);
    }

    /**
     * @param $id
     * @return RedirectResponse|Redirector
     */
    public function loginAs($id)
    {

        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        } else {
            $user = User::findOrFail($id);
            Auth::login($user);

            return redirect('/');
        }
    }

    /**
     * @param Request $request
     * @return Factory|RedirectResponse|Redirector|View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index(Request $request)
    {
        if (!is_null(Session::get('previous_url'))) {
            Session::forget('previous_url');
        }

        // Limit
        $limit = Input::get('limit') ?: 10;

        $users_default = User::where('deleted', false)
            ->whereHas(
                'roles', function ($query) {
                $query->where('name', '!=', 'SuperAdmin');
            }
            );

        $input = $request->all();

        if (Gate::denies('view_users_menu')) {
            abort(403);
        } elseif (Gate::allows('view_users_menu') && Gate::denies('view_users') && Gate::allows('view_admins_menu')) {
            return redirect('/administrators');
        }


        $users_default = User::advancedSearch($users_default, $input);

        if (!isset($input['export'])) {

            $users = $users_default->paginate($limit);

            return view('users.index', compact('users'));

        } else {
            //Handle export

            return Excel::download(new UsersExport($users_default), 'users-export-' . Carbon::now()->toDateString() . '.xlsx', 'Xlsx');
        }
    }


    /**
     * @param Request $request
     * @return Factory|View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function administrators(Request $request)
    {
        if (!is_null(Session::get('previous_url'))) {
            Session::forget('previous_url');
        }

        // Limit
        $limit = Input::get('limit') ?: 10;

        if (Gate::denies('view_admins_menu')) {
            abort(403);
        }

        $roles = array();

        if (Gate::allows('view_admins')) {
            $roles [] = 'SuperAdmin';
        }

        if (Gate::allows('view_org_admins')) {
            $roles [] = 'InstitutionAdministrator';
        }

        if (Gate::allows('view_dep_admins')) {
            $roles [] = 'DepartmentAdministrator';
        }

        if (Auth::user()->hasRole('InstitutionAdministrator') && (Gate::allows('view_org_admins') || Gate::allows('view_dep_admins'))) {
            $users_default = User::where('deleted', false)->whereHas(
                'roles', function ($query) use ($roles) {
                $query->whereIn('name', $roles);
            }
            )
                ->whereHas(
                    'institutions', function ($query) {
                    $query->where('id', Auth::user()->institutions()->first()->id);
                }
                );

        } else {
            $users_default = User::where('deleted', false)->whereHas(
                'roles', function ($query) use ($roles) {
                $query->whereIn('name', $roles);
            }
            );

        }

        $input = $request->all();

        //Handle search

        $users_default = User::advancedSearch($users_default, $input);

        if (!isset($input['export'])) {

            $users = $users_default->paginate($limit);

            return view('users.administrators', compact('users'));

        } else {
            //Handle export
            return Excel::download(new UsersExport($users_default), 'moderators-export-' . Carbon::now()->toDateString() . '.xlsx', 'Xlsx');
        }
    }


    /**
     * @return Factory|View
     */
    public function create()
    {
        if (Gate::denies('view_users')) {
            abort(403);
        }
        return view('users.create');
    }

    /**
     * @param Requests\CreateUserRequest $request
     * @return RedirectResponse
     */
    public function store(Requests\CreateUserRequest $request)
    {
        $password = str_random(15);
        $input = $request->all();
        $input['created_at'] = Carbon::now();
        $input['updated_at'] = Carbon::now();
        $input['password'] = bcrypt($password);
        $input['status'] = 1;
        $input['state'] = 'sso';
        $input['creator_id'] = request()->user()->id;
        $input['activation_token'] = str_random(15);
        $user = User::create($input);

        $user->institutions()->attach(1);
        $user->departments()->attach(1);

        // Assign role to user
        $user->assignRole('EndUser');

        // Send email to user for the new account
        $user->email_for_new_account($password);

        // Assign conferenceUser to conference

        if (isset($input['specialUser']) && $input['specialUser'] == 'conferenceUser') {
            $conference = Conference::findOrFail($input['conference_id']);
            $conference->participants()->save($user);
            DB::table('conference_user')->where('conference_id', $conference->id)->where('user_id', $user->id)->update(['device' => 'Desktop-Mobile']);
        }
        return redirect($input['from'])->with('message', trans('controllers.userAddedActivationEmailSent'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store_new_department_admin(Request $request)
    {
        //Validation logic

        $input = $request->all();
        $validator = Validator::make($input, [
            'dept_admin_email' => 'required|email',
            'dept_admin_telephone' => 'required',
            'dept_admin_institution_id' => 'required',
            'dept_admin_new_institution' => 'required_if:dept_admin_institution_id,other',
            'dept_admin_department_id' => 'required_unless:dept_admin_institution_id,other',
            'dept_admin_new_department' => 'required_if:dept_admin_department_id,other',
        ], [
            'dept_admin_state.required' => trans('requests.localSelectRequired'),
            'dept_admin_email.required' => trans('requests.emailRequired'),
            'dept_admin_email.email' => trans('requests.emailInvalid'),
            'dept_admin_telephone.required' => trans('requests.phoneRequired'),
            'dept_admin_institution_id.required' => trans('requests.institutionRequired'),
            'dept_admin_new_institution.required_if' => trans('requests.newInstitutionRequired'),
            'dept_admin_department_id.required_unless' => trans('requests.departmentRequired'),
            'dept_admin_new_department.required_if' => trans('requests.newDepartmentRequired'),
        ]);

        $validator->after(function ($validator) use ($input) {

            //Manually check if there is a user using this email already to form the invitation link

            $auth_user_institution = Auth::user()->institutions()->first();

            $user_using_this_email = User::where('email', $input['dept_admin_email'])->first();
            $user_using_this_email_as_confirmed_extra = ExtraEmail::where('email', $input['dept_admin_email'])->where('confirmed', 1)->first();
            if (isset($user_using_this_email) || isset($user_using_this_email_as_confirmed_extra)) {
                if ($user_using_this_email) {
                    $user_inst = $user_using_this_email->institutions()->first();
                    if ($user_inst && $user_inst->id == $auth_user_institution->id) {
                        if (!$user_using_this_email->hasRole("DepartmentAdministrator") && !$user_using_this_email->hasRole("InstitutionAdministrator"))
                            $validator->errors()->add('email', trans('requests.emailNotUniqueForInstitutionAdmin', ['user_id' => $user_using_this_email->id]));
                        else
                        {
                            if($user_using_this_email->hasRole("DepartmentAdministrator"))
                            $validator->errors()->add('email', trans('requests.emailNotUniqueAlreadyDepartmentAdmin', ['user_id' => $user_using_this_email->id]));
                            else
                            $validator->errors()->add('email', trans('requests.emailNotUniqueAlreadyInstitutionAdmin', ['user_id' => $user_using_this_email->id]));
                        }
                    } else {
                        $validator->errors()->add('email', trans('requests.emailNotUniqueForInstitutionOtherInstitution'));
                    }
                }
                if ($user_using_this_email_as_confirmed_extra) {
                    $user_inst = $user_using_this_email_as_confirmed_extra->user->institutions()->first();
                    if ($user_inst && $user_inst->id == $auth_user_institution->id) {
                        if (!$user_using_this_email_as_confirmed_extra->user->hasRole("DepartmentAdministrator") && !$user_using_this_email_as_confirmed_extra->user->hasRole("InstitutionAdministrator"))
                            $validator->errors()->add('email', trans('requests.emailNotUniqueForInstitutionAdmin', ['user_id' => $user_using_this_email_as_confirmed_extra->user_id]));
                        else
                        {
                            if($user_using_this_email_as_confirmed_extra->user->hasRole("DepartmentAdministrator"))
                            $validator->errors()->add('email', trans('requests.emailNotUniqueAlreadyDepartmentAdmin', ['user_id' => $user_using_this_email_as_confirmed_extra->user_id]));
                            else
                            $validator->errors()->add('email', trans('requests.emailNotUniqueAlreadyInstitutionAdmin', ['user_id' => $user_using_this_email_as_confirmed_extra->user_id]));
                        }
                     } else {
                        $validator->errors()->add('email', trans('requests.emailNotUniqueForInstitutionOtherInstitution'));
                    }
                }
            } else {
                $user_using_this_email_as_unconfirmed_extra = ExtraEmail::where('email', $input['dept_admin_email'])->where('confirmed', 0)->first();
                if ($user_using_this_email_as_unconfirmed_extra) {
                    $validator->errors()->add('email', trans('requests.emailInUseByUnconfirmedExtra'));
                }
            }
        });


        if ($validator->fails()) {
            return back()->withErrors($validator,'new_dep_admin')->withInput();
        }

        //Validation end

        $password = str_random(15);
        $input['created_at'] = Carbon::now();
        $input['updated_at'] = Carbon::now();
        $input['password'] = bcrypt($password);
        $input['name'] = $input['dept_admin_email'];
        $input['firstname'] = $input['dept_admin_firstname'];
        $input['lastname'] = $input['dept_admin_lastname'];
        $input['email'] = $input['dept_admin_email'];
        $input['telephone'] = $input['dept_admin_telephone'];
        $input['state'] = 'sso';
        $input['institution_id'] = $input['dept_admin_institution_id'];
        $input['new_institution'] = isset($input['dept_admin_new_institution']) ? $input['dept_admin_new_institution'] : null ;
        $input['department_id'] = $input['dept_admin_department_id'];
        $input['new_department'] = isset($input['dept_admin_new_department']) ? $input['dept_admin_new_department'] : null ;
        $input['status'] = 1;
        $input['creator_id'] = Auth::user()->id;
        if ($input['state'] == 'sso') {
            $input['activation_token'] = str_random(15);
        }

        $user = User::create($input);

        // Assign role to user

        $user->assignRole("DepartmentAdministrator");

        //Handle institution as InstitutionAdministrator

        if (Auth::user()->hasRole('InstitutionAdministrator')) {
            //Only department administrations are created here
            $input['institution_id'] = Auth::user()->institutions()->first()->id;
        }

        if (!empty($input['new_department']) && $input['department_id'] == "other") {
            $new_department = Department::create(['title' => $input['new_department'], 'institution_id' => $input['institution_id']]);
            $input['department_id'] = $new_department->id;
        }

        $user->institutions()->attach($input['institution_id']);
        $user->departments()->attach($input['department_id']);

        // Send email to user for the new account

        $user->email_for_new_account($password);

        // Send email to the Super Admins if an InstitutionAdministrator create a DepartmentAdministrator
        if ($user->hasRole('DepartmentAdministrator') && isset($input['creator_id']) && $user->creator->hasRole('InstitutionAdministrator') && $user->status == 1) {
            $email = Email::where('name', 'newDepartmentAdministrator')->first();
            $parameters = array('user' => $user);

            Mail::send('emails.newDepartmentAdministrator', $parameters, function ($message) use ($email, $user) {
                $message->from($email->sender_email, 'e:Presence')
                    ->to(env('SUPPORT_MAIL'))
                    ->replyTo($user->email, $user->firstname . ' ' . $user->lastname)
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });
        }
        return back()->with('message', trans('controllers.userAddedActivationEmailSent'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store_new_institution_admin(Request $request)
    {
        //Validation logic

        $input = $request->all();

        $validator = Validator::make($input, [
//            'inst_admin_lastname' => 'required',
//            'inst_admin_firstname' => 'required',
            'inst_admin_email' => 'required|email',
            'inst_admin_telephone' => 'required',
            'inst_admin_institution_id' => 'required',
            'inst_admin_new_institution' => 'required_if:inst_admin_institution_id,other',
            'inst_admin_department_id' => 'required_unless:inst_admin_institution_id,other',
            'inst_admin_new_department' => 'required_if:inst_admin_department_id,other',
        ], [
//            'inst_admin_lastname.required' => trans('requests.lastnameRequired'),
//            'inst_admin_firstname.required' => trans('requests.firstnameRequired'),
            'inst_admin_state.required' => trans('requests.localSelectRequired'),
            'inst_admin_email.required' => trans('requests.emailRequired'),
            'inst_admin_email.email' => trans('requests.emailInvalid'),
            'inst_admin_telephone.required' => trans('requests.phoneRequired'),
            'inst_admin_institution_id.required' => trans('requests.institutionRequired'),
            'inst_admin_new_institution.required_if' => trans('requests.newInstitutionRequired'),
            'inst_admin_department_id.required_unless' => trans('requests.departmentRequired'),
            'inst_admin_new_department.required_if' => trans('requests.newDepartmentRequired'),
        ]);

        $validator->after(function ($validator) use ($input) {
            //Manually check if there is a user using this email already to form the invitation link
           // $auth_user_institution = Auth::user()->institutions()->first();

            $user_using_this_email = User::where('email', $input['inst_admin_email'])->first();
            $user_using_this_email_as_confirmed_extra = ExtraEmail::where('email', $input['inst_admin_email'])->where('confirmed', 1)->first();
            if (isset($user_using_this_email) || isset($user_using_this_email_as_confirmed_extra)) {
                    $validator->errors()->add('email', trans('controllers.emailInUse'));
              } else {
                $user_using_this_email_as_unconfirmed_extra = ExtraEmail::where('email', $input['inst_admin_email'])->where('confirmed', 0)->first();
                if ($user_using_this_email_as_unconfirmed_extra) {
                    $validator->errors()->add('email', trans('requests.emailInUseByUnconfirmedExtra'));
                }
            }
        });


        if ($validator->fails()) {
            return back()->withErrors($validator,'new_inst_admin')->withInput();
        }

        //Validation end

        $password = str_random(15);

        $input['created_at'] = Carbon::now();
        $input['updated_at'] = Carbon::now();
        $input['password'] = Hash::make($password);
        $input['name'] = $input['inst_admin_email'];
        $input['firstname'] = $input['inst_admin_firstname'];
        $input['lastname'] = $input['inst_admin_lastname'];
        $input['email'] = $input['inst_admin_email'];
        $input['telephone'] = $input['inst_admin_telephone'];
        $input['state'] = "sso";
        $input['institution_id'] = $input['inst_admin_institution_id'];
        $input['department_id'] = $input['inst_admin_department_id'];
        $input['new_department'] = $input['inst_admin_new_department'];
        $input['status'] = 1;
        $input['creator_id'] = Auth::user()->id;
        $input['activation_token'] = str_random(15);
        $user = User::create($input);

        // Assign role to user

        $user->assignRole("InstitutionAdministrator");

        //Institution administrators created here

          if(!empty($input['new_institution']) && $input['institution_id'] == "other") {

                $input['department_id'] = "other";

                $new_institution = Institution::create(['title' => $input['new_institution'], 'slug' => 'noID']);
                $input['institution_id'] = $new_institution->id;

                // Create admin department
                Department::create(['title' => 'Διοίκηση', 'slug' => 'admin', 'institution_id' => $new_institution->id]);
                // Create other department
                Department::create(['title' => 'Άλλο', 'slug' => 'other', 'institution_id' => $new_institution->id]);
         }

        if (!empty($input['new_department'])) {
            $new_department = Department::create(['title' => $input['new_department'],'institution_id' => $input['institution_id']]);
            $input['department_id'] = $new_department->id;
        }

        $user->institutions()->attach($input['institution_id']);
        $user->departments()->attach($input['department_id']);

        // Send email to user for the new account

        $user->email_for_new_account($password);

        return back()->with('message', trans('controllers.userAddedActivationEmailSent'));
    }

    //This executes on clicking the link shown in the error / when trying to add a department admin with an email that already exists in your institution

    public function invite_user_to_become_department_admin($user_id)
    {

        $authUser = Auth::user();
        $userToInvite = User::find($user_id);
        $validate = false;

        if ($userToInvite && isset($userToInvite->institutions()->first()->id) && $userToInvite->status == 1) {

            if (($authUser->hasRole('InstitutionAdministrator') || $authUser->hasRole('SuperAdmin')) && $authUser->institutions()->first()->id == $userToInvite->institutions()->first()->id) {

                $email = Email::where('name', 'departmentAdministratorInvitation')->first();
                $user_department_title = $userToInvite->departments()->first()->title;

                // Send message

                $parameters = array('body' => $email->body, 'user' => $userToInvite, 'inviting_user' => $authUser, 'department' => $user_department_title);
                Mail::send('emails.departmentAdministratorInvitation', $parameters, function ($message) use ($userToInvite, $email) {
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($userToInvite->email)
                        ->replyTo(env('SUPPORT_MAIL'))
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });

                $validate = true;
            }
        }


        if ($validate)
            return back()->with('message', trans('controllers.emailSent'));
        else
            return back()->withErrors('permission_denied', trans('errors.noAccessPage'));
    }


    public function sendConfirmationEmailSSO(Request $request)
    {

        $password = str_random(15);
        $input = $request->all();
        $email = $input['email'];

        $user = User::where('email', $email)->first();

        $user->createNewPassword($password);

        switch ($user->state) {
            case 'local':
                $login_url = URL::to("auth/login");
                $email = Email::where('name', 'userAccountEnable')->first();
                $email_view = 'emails.enable_account_local';
                break;
            case 'sso':
                $login_url = URL::to("login/" . $user->activation_token);
                $email = Email::where('name', 'userAccountEnableSso')->first();
                $email_view = 'emails.enable_account_sso';
                break;
        }

        // Send message

        $parameters = array('body' => $email->body, 'user' => $user, 'password' => $password, 'login_url' => $login_url, 'account_url' => URL::to("account"));
        Mail::send($email_view, $parameters, function ($message) use ($user, $email) {
            $message->from($email->sender_email, 'e:Presence')
                ->to($user->email)
                ->replyTo(env('SUPPORT_MAIL'))
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        echo "Email sent!";
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $authenticated_user = Auth::user();

        $extra_emails['sso'] = $user->extra_emails_sso()->toArray();
        $extra_emails['custom'] = $user->extra_emails_custom()->toArray();

        $institution = $user->institutions()->first();
        $department = $user->departments()->first();
        $role = $user->roles()->first();


        if (($user->hasRole('SuperAdmin')) && Gate::denies('edit_admin_account') && $authenticated_user->is_user($user) == false) {
            abort(403);
        } elseif ($user->hasRole('InstitutionAdministrator') && Gate::denies('edit_org_admin') && $authenticated_user->is_user($user) == false) {
            abort(403);
        } elseif ($user->hasRole('DepartmentAdministrator') && $user->hasRole('DepartmentAdministrator') && $authenticated_user->is_user($user) == false && Gate::denies('edit_dep_admin')) {
            abort(403);
        } elseif ($user->hasRole('EndUser') && Gate::denies('edit_user') && Auth::user()->is_user($user) == false) {
            abort(403);
        }

        $user['from_page'] = class_basename(URL::previous());

        return view('users.edit', [
            'user' => $user,
            'auth_user' => $authenticated_user,
            'extra_emails' => $extra_emails,
            'institution' => $institution,
            'department' => $department,
            'role' => $role
        ]);

    }

    public function delete($id)
    {
        if (Gate::denies('view_users')) {
            abort(403);
        }

        $user = User::findOrFail($id);

        if ($user->hasParticipatedInConferences()->count() > 0 || $user->conferenceAdmin()->count() > 0) {
            abort(403);
        } else {
            if (Gate::denies('delete_user') && $user->hasRole('EndUser')) {
                abort(403);
            } elseif (Gate::denies('delete_admin') && ($user->hasRole('SuperAdmin'))) {
                abort(403);
            } elseif (Gate::denies('delete_org_admin') && $user->hasRole('InstitutionAdministrator')) {
                abort(403);
            } elseif ((Gate::denies('delete_dep_admin') && $user->hasRole('DepartmentAdministrator')) || (Auth::user()->hasRole('InstitutionAdministrator') && ($user->institutions()->first()->id != Auth::user()->institutions()->first()->id))) {
                abort(403);
            } else {
                // Delete user role
                DB::table('role_user')->where('user_id', $user->id)->delete();

                // Delete user department
                DB::table('department_user')->where('user_id', $user->id)->delete();

                // Delete user institution
                DB::table('institution_user')->where('user_id', $user->id)->delete();

                $user->delete();
            }
        }

        if (!empty(Session::get('previous_url'))) {
            return redirect(Session::get('previous_url'))->with('message', trans('controllers.userDeleted'));
        } else {
            return redirect('/users')->with('message', trans('controllers.userDeleted'));
        }
    }

    public function delete_user(Request $request)
    {
        if (Gate::denies('view_users')) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotDeleteUserRights')
            );
        }
        $input = $request->all();

        $user_id = intval($input['user_id']);
        $user = User::findOrFail($user_id);

        if ($user->hasParticipatedInConferences()->count() > 0 || $user->conferenceAdmin()->count() > 0) {

            if (($user->hasRole('SuperAdmin')) && Gate::denies('edit_admin_account')) {
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotEditUserRights')
                );
            } elseif ($user->hasRole('InstitutionAdministrator') && Gate::denies('edit_org_admin')) {
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotEditUserRights')
                );
            } elseif ($user->hasRole('DepartmentAdministrator') && Gate::denies('edit_dep_admin')) {
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotEditUserRights')
                );
            } elseif ($user->hasRole('EndUser') && Gate::denies('edit_user')) {
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotEditUserRights')
                );
            } else {
                switch ($user->status) {
                    case 1:
                        if ($input['sure'] == '') {
                            $results = array(
                                'status' => 'are_you_sure',
                                'data' => trans('controllers.qDeactivateUser')
                            );
                        } else {
                            $results = array(
                                'status' => 'success',
                                'data' => trans('controllers.userDeactivated'),
                                'action' => 'disableUser'

                            );
                            $user->update(['status' => 0]);
                        }
                        break;
                    case 0:
                        if ($input['sure'] == '') {
                            $results = array(
                                'status' => 'are_you_sure',
                                'data' => trans('controllers.qActivateUser')
                            );
                        } else {
                            $results = array(
                                'status' => 'success',
                                'data' => trans('controllers.userActivated'),
                                'action' => 'enableUser'
                            );
                            $user->update(['status' => 1]);
                        }
                        break;
                }
            }
        } else {
            if (Gate::denies('delete_user') && $user->hasRole('EndUser')) {
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotDeleteUserRights')
                );
            } elseif (Gate::denies('delete_admin') && ($user->hasRole('SuperAdmin'))) {
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotDeleteUserRights')
                );
            } elseif (Gate::denies('delete_org_admin') && $user->hasRole('InstitutionAdministrator')) {
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotDeleteUserRights')
                );
            } elseif (Gate::denies('delete_dep_admin') && $user->hasRole('DepartmentAdministrator') || (Auth::user()->hasRole('InstitutionAdministrator') && ($user->institutions()->first()->id != Auth::user()->institutions()->first()->id))) {
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotDeleteUserRights')
                );
            } elseif (!Gate::denies('delete_user') && empty($input['sure'])) {
                $results = array(
                    'status' => 'are_you_sure',
                    'data' => trans('controllers.qDeleteuser')
                );
            } elseif(!Gate::denies('delete_user') && $input['sure'] == "yes") {
                $user->delete();
                $results = array(
                    'status' => 'success',
                    'data' => trans('controllers.userDeleted'),
                    'action' => 'deleteUser'
                );
            }else{
                $results = array(
                    'status' => 'error',
                    'data' => trans('controllers.cannotDeleteUserRights')
                );
            }
        }

        echo json_encode($results);
    }

    public function disable_user(Request $request)
    {
        if (Gate::denies('view_admins')) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotEditUserRights')
            );
        }
        $input = $request->all();

        $user_id = intval($input['user_id']);
        $user = User::findOrFail($user_id);

        if (($user->hasRole('SuperAdmin')) && Gate::denies('edit_admin_account')) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotEditUserRights')
            );
        } elseif ($user->hasRole('InstitutionAdministrator') && Gate::denies('edit_org_admin')) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotEditUserRights')
            );
        } elseif ($user->hasRole('DepartmentAdministrator') && $user->hasRole('DepartmentAdministrator') && Gate::denies('edit_dep_admin')) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotEditUserRights')
            );
        } elseif ($user->hasRole('EndUser') && Gate::denies('edit_user')) {
            $results = array(
                'status' => 'error',
                'data' => trans('controllers.cannotEditUserRights')
            );
        } else {
            switch ($user->status) {
                case 1:
                    if ($input['sure'] == '') {
                        $results = array(
                            'status' => 'are_you_sure',
                            'data' => trans('controllers.qDeactivateUser')
                        );
                    } else {
                        $results = array(
                            'status' => 'success',
                            'data' => trans('controllers.userDeactivated'),
                            'action' => 'disableUser'
                        );
                        $user->update(['status' => 0]);
                    }
                    break;
                case 0:
                    if ($input['sure'] == '') {
                        $results = array(
                            'status' => 'are_you_sure',
                            'data' => trans('controllers.qActivateUser')
                        );
                    } else {
                        $results = array(
                            'status' => 'success',
                            'data' => trans('controllers.userActivated'),
                            'action' => 'enableUser'
                        );
                        $user->update(['status' => 1]);
                    }
                    break;
            }
        }
        echo json_encode($results);
    }


    public function set_cookie(Request $request)
    {
        $input = $request->all();
        Cookie::queue($input['cookie_name'], $input['cookie_value'], 2628000);
    }

    public function delete_user_image(Request $request)
    {
        $input = $request->all();
        $user = User::find($input['id']);

        if ($user->id != Auth::user()->id) {
            abort(403);
        }

        // Delete file
        if (!empty($user->thumbnail) && File::exists(public_path() . '/images/user_images/' . $user->thumbnail)) {
            File::delete(public_path() . '/images/user_images/' . $user->thumbnail);
            $results = array(
                'status' => 'success'
            );
            $user->update(['thumbnail' => null]);
        } else {
            $results = array(
                'status' => 'error'
            );
        }

        echo json_encode($results);
    }





}
