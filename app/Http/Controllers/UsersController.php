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

        if (Gate::denies('view_users')) {
            abort(403);
        }

        if(Auth::user()->hasRole("SuperAdmin")){
            // Limit
            $limit = Input::get('limit') ?: 10;
            $users_default = User::where('deleted', false)
                ->whereHas(
                    'roles', function ($query) {
                    $query->where('name', 'EndUser');
                }
             );
            $input = $request->all();
            $users_default = User::advancedSearch($users_default, $input);
            if (!isset($input['export'])) {
                $users = $users_default->paginate($limit);
                return view('users.index', compact('users'));
            } else {
                //Handle export
                return Excel::download(new UsersExport($users_default), 'users-export-' . Carbon::now()->toDateString() . '.xlsx', 'Xlsx');
            }
        }else{
            return view('users.index');
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

        if (Auth::user()->hasRole('SuperAdmin')) {
            $roles [] = 'SuperAdmin';
            $roles [] = 'InstitutionAdministrator';
            $roles [] = 'DepartmentAdministrator';
        }else{
            $roles [] = 'InstitutionAdministrator';
            $roles [] = 'DepartmentAdministrator';
        }

        $users_default = User::where('deleted', false)->whereHas(
            'roles', function ($query) use ($roles) {
            $query->whereIn('name', $roles);
        });
        if (Auth::user()->hasRole('InstitutionAdministrator')) {
            $users_default->whereHas(
                    'institutions', function ($query) {
                    $query->where('id', Auth::user()->institutions()->first()->id);
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
     * @param Requests\CreateUserRequest $request
     * @return RedirectResponse
     */
    public function store(Requests\CreateUserRequest $request)
    {

        $authenticatedUser = request()->user();
        if (!$authenticatedUser->canCreateEndUser()) {
            abort(403);
        }

        $password = str_random(9);
        $input = $request->all();
        $input['password'] = bcrypt($password);
        $input['status'] = 1;
        $input['state'] = 'sso';
        $input['creator_id'] = $authenticatedUser->id;
        $input['activation_token'] = str_random(15);
        $user = User::create($input);

        $user->institutions()->attach($authenticatedUser->institutions()->first()->id);
        $user->departments()->attach($authenticatedUser->departments()->first()->id);

        // Assign role to user
        $user->assignRole('EndUser');

        // Send email to user for the new account
        $user->email_for_new_account();

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

        $authenticatedUser = request()->user();
        if (!$authenticatedUser->canCreateDepartmentAdmin()) {
            abort(403);
        }

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

        $createUserParameters['password'] = Hash::make(str_random(15));
        $createUserParameters['firstname'] = $input['dept_admin_firstname'];
        $createUserParameters['lastname'] = $input['dept_admin_lastname'];
        $createUserParameters['email'] = $input['dept_admin_email'];
        $createUserParameters['telephone'] = $input['dept_admin_telephone'];
        $createUserParameters['state'] = 'sso';
        $createUserParameters['status'] = 1;
        $createUserParameters['creator_id'] = $authenticatedUser->id;
        $createUserParameters['activation_token'] = str_random(15);
        $user = User::create($createUserParameters);

        $input['institution_id'] = $input['dept_admin_institution_id'];
        $input['new_institution'] = isset($input['dept_admin_new_institution']) ? $input['dept_admin_new_institution'] : null ;
        $input['department_id'] = $input['dept_admin_department_id'];
        $input['new_department'] = isset($input['dept_admin_new_department']) ? $input['dept_admin_new_department'] : null ;



        // Assign role to user

        $user->assignRole("DepartmentAdministrator");

        //Handle institution as InstitutionAdministrator

        if ($authenticatedUser->hasRole('InstitutionAdministrator')) {
            //Only department administrations are created here
            $input['institution_id'] = Auth::user()->institutions()->first()->id;
        }

        if (!empty($input['new_department']) && $input['department_id'] == "other") {
            $new_department = Department::create(['title' => $input['new_department'], 'institution_id' => $input['institution_id']]);
            $input['department_id'] = $new_department->id;
        }

        $user->institutions()->sync($input['institution_id']);
        $user->departments()->sync($input['department_id']);

        // Send email to user for the new account

        $user->email_for_new_account();

        // Send email to the Super Admins if an InstitutionAdministrator create a DepartmentAdministrator
//        if ($user->hasRole('DepartmentAdministrator') && isset($input['creator_id']) && $user->creator->hasRole('InstitutionAdministrator') && $user->status == 1) {
//            $email = Email::where('name', 'newDepartmentAdministrator')->first();
//            $parameters = array('user' => $user);
//
//            Mail::send('emails.newDepartmentAdministrator', $parameters, function ($message) use ($email, $user) {
//                $message->from($email->sender_email, config('mail.from.name'))
//                    ->to(env('SUPPORT_MAIL'))
//                    ->replyTo($user->email, $user->firstname . ' ' . $user->lastname)
//                    ->returnPath(env('RETURN_PATH_MAIL'))
//                    ->subject($email->title);
//            });
//        }
        return back()->with('message', trans('controllers.userAddedActivationEmailSent'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store_new_institution_admin(Request $request)
    {
        //Validation logic
        $authenticatedUser = request()->user();
        if (!$authenticatedUser->canCreateInstitutionAdmin()) {
            abort(403);
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'inst_admin_email' => 'required|email',
            'inst_admin_telephone' => 'required',
            'inst_admin_institution_id' => 'required',
            'inst_admin_department_id' => 'required',
            'inst_admin_new_department' => 'required_if:inst_admin_department_id,other',
        ], [
            'inst_admin_email.required' => trans('requests.emailRequired'),
            'inst_admin_email.email' => trans('requests.emailInvalid'),
            'inst_admin_telephone.required' => trans('requests.phoneRequired'),
            'inst_admin_institution_id.required' => trans('requests.institutionRequired'),
            'inst_admin_department_id.required' => trans('requests.departmentRequired'),
            'inst_admin_new_department.required_if' => trans('requests.newDepartmentRequired'),
        ]);

        $validator->after(function ($validator) use ($input) {
            //Manually check if there is a user using this email already to form the invitation link
           // $auth_user_institution = Auth::user()->institutions()->first();

            $user_using_this_email = User::where('email', $input['inst_admin_email'])->first();
            $user_using_this_email_as_confirmed_extra = ExtraEmail::where('email', $input['inst_admin_email'])->where('confirmed', 1)->first();
            if (isset($user_using_this_email) || isset($user_using_this_email_as_confirmed_extra)) {
                    $validator->errors()->add('inst_admin_email', trans('controllers.emailInUse'));
              } else {
                $user_using_this_email_as_unconfirmed_extra = ExtraEmail::where('email', $input['inst_admin_email'])->where('confirmed', 0)->first();
                if ($user_using_this_email_as_unconfirmed_extra) {
                    $validator->errors()->add('inst_admin_email', trans('requests.emailInUseByUnconfirmedExtra'));
                }
            }
        });


        if ($validator->fails()) {
            return redirect('/administrators')->withErrors($validator,'new_inst_admin')->withInput();
        }

        //Validation end

        $createUserParameters['password'] = Hash::make(str_random(15));
        $createUserParameters['firstname'] = $input['inst_admin_firstname'];
        $createUserParameters['lastname'] = $input['inst_admin_lastname'];
        $createUserParameters['email'] = $input['inst_admin_email'];
        $createUserParameters['telephone'] = $input['inst_admin_telephone'];
        $createUserParameters['state'] = "sso";
        $createUserParameters['status'] = 1;
        $createUserParameters['creator_id'] = $authenticatedUser->id;
        $createUserParameters['activation_token'] = str_random(15);
        $user = User::create($createUserParameters);

        $input['institution_id'] = $input['inst_admin_institution_id'];
        $input['department_id'] = $input['inst_admin_department_id'];
        $input['new_department'] = $input['inst_admin_new_department'];

        // Assign role to user

        $user->assignRole("InstitutionAdministrator");

        if ($input['department_id'] == "other" && !empty($input['new_department'])) {
            $new_department = Department::create(['title' => $input['new_department'],'institution_id' => $input['institution_id']]);
            $input['department_id'] = $new_department->id;
        }

        $institution = Institution::find($input['institution_id']);
        $department = Department::find($input['department_id']);

        $user->institutions()->attach($institution->id);
        $user->departments()->attach($department->id);

        // Send email to user for the new account

       $user->email_for_new_account();

        return redirect('/administrators')->with('message', trans('controllers.userAddedActivationEmailSent'));
    }

    //This executes on clicking the link shown in the error / when trying to add a department admin with an email that already exists in your institution

    /**
     * @param $user_id
     * @return RedirectResponse
     */
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
                    $message->from($email->sender_email, config('mail.from.name'))
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


    /**
     * @param Request $request
     */
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
            $message->from($email->sender_email, config('mail.from.name'))
                ->to($user->email)
                ->replyTo(env('SUPPORT_MAIL'))
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        echo "Email sent!";
    }


    /**
     * @param $id
     * @return RedirectResponse
     */
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

    /**
     * @param Request $request
     */
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

    /**
     * @param Request $request
     */
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


    /**
     * @param Request $request
     */
    public function set_cookie(Request $request)
    {
        $input = $request->all();
        Cookie::queue($input['cookie_name'], $input['cookie_value'], 2628000);
    }

    /**
     * @param Request $request
     */
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
