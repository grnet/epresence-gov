<?php

namespace App\Http\Controllers;

use App\Application;
use App\Department;
use App\Institution;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
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
        $this->middleware('auth', ['except' => ['store_admin_application', 'redirect_sso_login_to_account_application']]);
    }

    /**
     * @return Factory|View
     */
    public function index()
    {
        if (!is_null(Session::get('previous_url'))) {
            Session::forget('previous_url');
        }
        if (Gate::denies('view_applications')) {
            abort(403);
        } else {
            $applications = Application::selectRaw("applications.*")->whereIn('app_state', ['notVerified', 'new'])
                ->with('user')
                ->join('users','users.id','=','applications.user_id');

            $limit = Input::get('limit') ?: 10;
            $appliedSort = false;
            if(Input::get('sort_createdAt')){
             $appliedSort = true;
             $applications->orderBy("created_at",Input::get('sort_createdAt'));
            }
            if(Input::get('sort_status')){
                $appliedSort = true;
                $applications->orderBy("app_state",Input::get('sort_status'));
            }
            if(Input::get('sort_lastname')){
                $appliedSort = true;
                $applications->orderBy("users.lastname",Input::get('sort_lastname'));
            }
            if(!$appliedSort){
                $applications->orderBy("applications.created_at","desc");
            }
            return view('users.applications',
                [
                    'applications' => $applications->paginate($limit),
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
        if (Gate::denies('view_applications')) {
            abort(403);
        }
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
                    $message->from($email->sender_email, config('mail.from.name'))
                        ->to($user->email)
                        ->replyTo($email->sender_email, config('mail.from.name'))
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
                    $message->from($email_for_admins->sender_email, config('mail.from.name'))
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
        if (Gate::denies('view_applications')) {
            abort(403);
        }
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
        Log::info("Request role change request: ".json_encode($input));
        $user = Auth::user();

        //If there is already a pending application from this user deny the application

        if (!$user->canRequestRoleUpgrade()) {
            return back()->with('error', trans('site.roleChangeRequestDenied'));
        }

        // Set default institution id value for department administrators requesting role upgrade

        if($user->hasRole("DepartmentAdministrator")){
            $institutionToApply = $user->institutions()->first();
            $departmentToApply = $institutionToApply->departments()->first();
        }else{
            $institutionToApply = Institution::findOrFail($input['institution_id']);

            if($input['application_role'] == "DepartmentAdministrator"){
                if(!empty($input['new_department']) && $input['department_id'] == "other") {
                    $departmentToApply = Department::create(['title' => $input['new_department'], 'institution_id' => $institutionToApply->id]);
                }else{
                    $departmentToApply = $institutionToApply->departments()->where("departments.id",$input['department_id'])->firstOrFail();
                }

            }else{
                $departmentToApply = $institutionToApply->departments()->first();
            }
        }


        $requestedRole = Role::where('name', $input['application_role'])->first();

        //Get institution admins emails / departments admins email to notify them also about the new application

        $admins_to_notify = $institutionToApply->institutionAdmins()->pluck('email')->toArray();

        if ($requestedRole->name == "DepartmentAdministrator") {
            $dep_admins_to_notify = $departmentToApply->departmentAdmins()->pluck('email')->toArray();
            $admins_to_notify = array_merge($dep_admins_to_notify, $admins_to_notify);
        }

        $new_application = new Application;
        $new_application->user_id = $user->id;
        $new_application->role_id = $requestedRole->id;
        $new_application->user_state = 'existing';
        $new_application->app_state = 'new';
        $new_application->comment = $input['application_comment'];
        $new_application->institution_id = $institutionToApply->id;
        $new_application->department_id = $departmentToApply->id;
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
        $user['institution_title'] = $institutionToApply->title;
        $user['department_title'] = $departmentToApply->title;

        $parameters = array('body' => $email->body, 'user' => $user, 'role_requested' => $requestedRole->label);

        Mail::send('emails.admin_application', $parameters, function ($message) use ($email, $user) {
            $message->from($email->sender_email, config('mail.from.name'))
                ->to(env('RETURN_PATH_MAIL'))
                ->replyTo($user->email, $user->firstname . ' ' . $user->lastname)
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        if (count($admins_to_notify) > 0) {
            $email_for_admins = Email::where('name', 'adminApplicationForAdmins')->first();
            Mail::send('emails.admin_application_for_other_admins', $parameters, function ($message) use ($email_for_admins, $user, $admins_to_notify) {
                $message->from($email_for_admins->sender_email, config('mail.from.name'))
                    ->to($admins_to_notify)
                    ->replyTo($email_for_admins->sender_email)
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email_for_admins->title);
            });
        }
        return redirect('account')->with('message', trans('requests.applicationSaved'));
    }

    /**
     * @return RedirectResponse|Redirector
     */
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
}
