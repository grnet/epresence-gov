<?php

namespace App\Http\Controllers;

use App\Application;
use App\Department;
use App\Institution;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;
use App\User;
use Illuminate\Validation\Validator;
use App\Role;
use Illuminate\Support\Facades\Mail;
use App\Email;

use App\Http\Requests;

class ApplicationController extends Controller
{

    /**
     * ApplicationController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['store_admin_application', 'redirect_sso_login_to_account_application', 'redirect_local_login_to_account_application']]);
    }

    /**
     * @return Factory|View
     */
    public function index()
    {
        if (!is_null(Session::get('previous_url'))) {
            Session::forget('previous_url');
        }

        // Limit
        $limit = Input::get('limit') ?: 10;

        if (Gate::denies('view_applications')) {
            abort(403);
        } else {

            $applications = Application::whereIn('app_state', ['notVerified', 'new'])->orderBy('created_at', 'desc')->paginate($limit);

            return view('users.applications',
                [
                    'applications' => $applications,
                ]
            );
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function accept_application(Request $request)
    {
        $application = Application::find($request->application_id);
        //Check if the application is already processed by another admin
        if ($application->app_state == "new") {

            $user = $application->user;
            $current_role = $user->roles()->first();
            $attached_role = Role::find($application->role_id);
            $user->roles()->detach($current_role->id);
            $user->roles()->attach($application->role_id);
            $user->update(['comment' => $application->comment]);
            $institution = $user->institutions()->first();
            $department = $user->departments()->first();

            //If institution is other create institution & department (if role requested is department administrator)

            $user->institutions()->sync([$application->institution_id]);
            $user->departments()->sync([$application->department_id]);

            //Get institution admins emails / departments admins email to notify them also about the new application

            $admins_to_notify = $institution->institutionAdmins()->pluck('email')->toArray();

            if ($attached_role->name == "DepartmentAdministrator") {

                $dep_admins_to_notify = $department->departmentAdmins()->pluck('email')->toArray();
                $admins_to_notify = array_merge($dep_admins_to_notify, $admins_to_notify);
            }


            $email = Email::where('name', 'applicationAccepted')->first();
            $parameters = array('contact_url' => URL::to("contact"));
            if ($user->status == 1) {

                Mail::send('emails.application_accepted', $parameters, function ($message) use ($user, $email) {
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($user->email)
                        ->replyTo($email->sender_email, 'e:Presence')
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }

            //Send notification email to department/institution admin about the acceptance of the application

            if (count($admins_to_notify) > 0) {

                $email_position = array_search($user->email, $admins_to_notify);

                if ($email_position !== false) {
                    unset($admins_to_notify[$email_position]);
                    $admins_to_notify = array_values($admins_to_notify);
                }

                $institution = $user->institutions()->first();
                $department = $user->departments()->first();

                $user['institution_title'] = $institution->title;
                $user['department_title'] = $department->title;


                $email_for_admins = Email::where('name', 'applicationAcceptedAdmins')->first();

                $application_parameters = array('body' => $email_for_admins->body, 'user' => $user, 'role_requested' => $attached_role->label);

                Mail::send('emails.application_accepted_for_admins', $application_parameters, function ($message) use ($email_for_admins, $admins_to_notify) {
                    $message->from($email_for_admins->sender_email, 'e:Presence')
                        ->to($admins_to_notify)
                        ->replyTo($email_for_admins->sender_email)
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email_for_admins->title);
                });
            }
            $application->app_state = 'finalised';
            $application->update();
        }
        return response()->json(['status' => 'success']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function decline_application(Request $request)
    {
        $application = Application::find($request->application_id);

        $application->app_state = "notVerified";
        $application->update();

        return response()->json(['status' => 'success']);
    }

    /**
     * @param Requests\RequestRoleChangeRequest $request
     * @return RedirectResponse
     */
    public function requestRoleChange(Requests\RequestRoleChangeRequest $request)
    {

        $input = $request->all();
        $user = Auth::user();

        //If there is already a pending application from this user deny the application

        if ($user->applications()->where('app_state', 'new')->exists()) {
            return back()->with('error', trans('site.roleChangeRequestDenied'));
        }

        // Set default institution id value for department administrators requesting role upgrade

        if($user->hasRole("DepartmentAdministrator")){
            $input['institution_id'] = $user->institutions()->first()->id;
        }

        $institution = $user->institutions()->first();
        $department = $user->departments()->first();

        $new_application = new Application;

        $attached_role = Role::where('name', $input['application_role'])->first();

        //Get institution admins emails / departments admins email to notify them also about the new application

        $admins_to_notify = $institution->institutionAdmins()->pluck('email')->toArray();

        if ($attached_role->name == "DepartmentAdministrator") {
            $dep_admins_to_notify = $department->departmentAdmins()->pluck('email')->toArray();
            $admins_to_notify = array_merge($dep_admins_to_notify, $admins_to_notify);
        }

        $new_application->user_id = $user->id;
        $new_application->role_id = $attached_role->id;
        $new_application->user_state = 'existing';
        $new_application->app_state = 'new';
        $new_application->comment = $input['application_comment'];
        $new_application->institution_id = $input['institution_id'];
        $new_application->department_id = $input['department_id'];
        $new_application->save();

        $user->telephone = $input['application_telephone'];
        $user->update();

        $email = Email::where('name', 'adminApplicationExisting')->first();

        $user['lastname'] = $user->lastname;
        $user['firstname'] = $user->firstname;
        $user['state'] = $user->state;
        $user['email'] = $user->email;
        $user['telephone'] = $user->telephone;
        $user['comment'] = $input['application_comment'];


        $user['institution_title'] = $institution->title;
        $user['department_title'] = $department->title;

        $parameters = array('body' => $email->body, 'user' => $user, 'role_requested' => $attached_role->label);

        Mail::send('emails.admin_application', $parameters, function ($message) use ($email, $user) {
            $message->from($email->sender_email, 'e:Presence')
                ->to(env('RETURN_PATH_MAIL'))
                ->replyTo($user->email, $user->firstname . ' ' . $user->lastname)
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        if (count($admins_to_notify) > 0) {

            $email_for_admins = Email::where('name', 'adminApplicationForAdmins')->first();

            Mail::send('emails.admin_application_for_other_admins', $parameters, function ($message) use ($email_for_admins, $user, $admins_to_notify) {
                $message->from($email_for_admins->sender_email, 'e:Presence')
                    ->to($admins_to_notify)
                    ->replyTo($email_for_admins->sender_email)
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email_for_admins->title);
            });
        }


        return back()->with('message', trans('requests.applicationSaved'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store_admin_application(Request $request)
    {
        //This method is used only by local-users that don't have an account yet
        //no need to check user state


        $input = $request->all();

        $validator = Validator::make($input, [
            'lastname' => 'required',
            'firstname' => 'required',
            'department_id' => 'required_if:role,DepartmentAdministrator',
            'new_department' => 'required_if:department_id,other',
            'institution_id' => 'required',
            'new_institution' => 'required_if:institution_id,other',
            'telephone' => 'required',
            'role' => 'required',
            'email' => "required|email|unique:applications,email,null,id,app_state,new|unique:users,email|unique:users_extra_emails,email,NULL,id,confirmed,1",
            'comment' => 'required',
            'accept_terms' => 'required'
        ], ['lastname.required' => trans('requests.lastnameRequired'),
            'firstname.required' => trans('requests.firstnameRequired'),
            'institution_id.required' => trans('requests.institutionRequired'),
            'department_id.required_if' => trans('requests.departmentRequired'),
            'new_department.required_if' => trans('requests.newDepartmentRequired'),
            'new_institution.required_if' => trans('requests.newInstitutionRequired'),
            'role.required' => trans('requests.roleRequired'),
            'telephone.required' => trans('users.telephoneRequired'),
            'email.required' => trans('requests.emailRequired'),
            'email.unique' => trans('requests.emailNotUniqueAccess'),
            'email.email' => trans('requests.emailInvalid'),
            'comment.required' => trans('application.appComment'),
        ]);

        if ($validator->fails()) {

            return back()->withErrors($validator)->withInput();
        } else {

            $new_application = new Application;

            $new_application->lastname = $input['lastname'];
            $new_application->firstname = $input['firstname'];
            $new_application->email = $input['email'];
            $new_application->telephone = $input['telephone'];

            $new_application->user_state = "new";
            $new_application->app_state = "new";


            $new_application->comment = $input['comment'];
            $role = Role::where('name', $input['role'])->first();
            $new_application->role_id = $role->id;
            $custom_values = ["institution" => "", "department" => ""];
            $admins_to_notify = array();


            if ($input['institution_id'] !== "other") {
                $institution = Institution::find($input['institution_id']);
                $user['institution_title'] = $institution->title;
                $admins_to_notify = $institution->institutionAdmins()->pluck('email')->toArray();
            } else {
                $institution = Institution::where('slug', 'other')->first();
                $user['institution_title'] = $input['new_institution'];
                $input['institution_id'] = $institution->id;
                $custom_values['institution'] = $input['new_institution'];
            }

            $new_application->institution_id = $input['institution_id'];


            if ($role->name == "DepartmentAdministrator") {

                if ($input['department_id'] !== "other") {
                    $department = Department::find($input['department_id']);
                    $user['department_title'] = $department->title;
                    $dep_admins_to_notify = $institution->institutionAdmins()->pluck('email')->toArray();
                    $admins_to_notify = $department->departmentAdmins()->pluck('email')->toArray();
                    $admins_to_notify = array_merge($dep_admins_to_notify, $admins_to_notify);

                } else {
                    $input['department_id'] = $institution->otherDepartment()->id;
                    $user['department_title'] = $input['new_department'];
                    $custom_values['department'] = $input['new_department'];
                }

                $new_application->department_id = $input['department_id'];

            } else {

                $admin_department = $institution->adminDepartment();
                $user['department_title'] = $admin_department->title;
                $new_application->department_id = $admin_department->id;
            }

            $new_application->custom_values = json_encode($custom_values);

            $user['lastname'] = $input['lastname'];
            $user['firstname'] = $input['firstname'];
            $user['state'] = 'local';
            $user['email'] = $input['email'];
            $user['telephone'] = $input['telephone'];
            $user['comment'] = $input['comment'];


            $new_application->save();

            $email = Email::where('name', 'adminApplication')->first();

            $parameters = array('body' => $email->body, 'user' => $user, 'role_requested' => $role->label);

            Mail::send('emails.admin_application', $parameters, function ($message) use ($email, $user) {
                $message->from($email->sender_email, 'e:Presence')
                    ->to(env('RETURN_PATH_MAIL'))
                    ->replyTo($user['email'], $user['firstname'] . ' ' . $user['lastname'])
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });


            if (count($admins_to_notify) > 0) {

                $email_for_admins = Email::where('name', 'adminApplicationForAdmins')->first();

                Mail::send('emails.admin_application_for_other_admins', $parameters, function ($message) use ($email_for_admins, $admins_to_notify) {
                    $message->from($email_for_admins->sender_email, 'e:Presence')
                        ->to($admins_to_notify)
                        ->replyTo($email_for_admins->sender_email)
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email_for_admins->title);
                });
            }

            return back()->with('message', trans('controllers.applicationSaved'));
        }

    }

    public function redirect_sso_login_to_account_application()
    {
        if (!Auth::check()) {
            session()->put('redirect_to_account_to_apply', 1);
            return redirect('/login');
        } else {
            session()->put("pop_role_change", 1);
            return redirect('/account');
        }
    }


    public function redirect_local_login_to_account_application()
    {
        if (!Auth::check()) {
            session()->put('redirect_to_account_to_apply', 1);
            return redirect('/');
        } else {
            session()->put("pop_role_change", 1);
            return redirect('/account');
        }

    }
}
