<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Institution;
use App\User;
use App\Department;
use App\Email;
use App\ExtraEmail;
use App\Role;
use Mail;
use URL;
use Gate;
use Auth;
use Log;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Response;


class UsersExtraController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['confirm_sso_email','resend_local_user_activation_email_token','store_new_sso_user','send_email_confirmation_link_create_user']]);
    }


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $auth_user = Auth::user();

        //Check if user has correct role to edit a user

        if (!$auth_user->hasRole('SuperAdmin')  && !$auth_user->hasRole('InstitutionAdministrator')) {
            abort(403);
        }

        if ($auth_user->hasRole('InstitutionAdministrator') && $user->institutions()->first()->id != $auth_user->institutions()->first()->id) {
            abort(403);
        }

        if (!$user->confirmed) {
            abort(403);
        }


        $extra_emails['sso'] = $user->extra_emails_sso()->toArray();
        $extra_emails['custom'] = $user->extra_emails_custom()->toArray();

        $institution = $user->institutions()->first();
        $department = $user->departments()->first();
        $role = $user->roles()->first();


        $user['from_page'] = class_basename(URL::previous());

        return view('users.edit', [
            'user' => $user,
            'auth_user' => $auth_user,
            'extra_emails' => $extra_emails,
            'institution' => $institution,
            'department' => $department,
            'role' => $role
        ]);

    }

    public function updateLocalUser(Requests\UpdateLocalAccountRequest $request, $id)
    {
        // State input values

        $user = User::find($id);
        $auth_user = Auth::user();

        //Check if user that is editing has the correct permissions to do that

        if (!$auth_user->hasRole('SuperAdmin')  && !$auth_user->hasRole('InstitutionAdministrator')) {
            abort(403);
        }

        if ($auth_user->hasRole('InstitutionAdministrator') && $user->institutions()->first()->id != $auth_user->institutions()->first()->id) {
            abort(403);
        }


        $input = $request->all();

        $input['name'] = $user->email;

        $institution = $user->institutions()->first();
        $department = $user->departments()->first();

        $message = trans('controllers.changesSaved');

        $custom_values['institution'] = "";
        $custom_values['department'] = "";

        //Role management

        $current_role = $user->roles()->first();


        if ($auth_user->hasRole('SuperAdmin') && !empty($input['role'])) {

            $update_role = Role::where('name', $input['role'])->first();

            if ($current_role->name != $update_role->name) {

                //Notify admins about the role change

                $email = Email::where('name', 'userRoleUpdated')->first();

                $parameters = array('contact_url' => URL::to("support"),'new_role' =>  $update_role->label,'user'=>$user);

                if($user->status == 1){

                    Mail::send('emails.user_role_updated', $parameters, function ($message) use ($user, $email) {
                        $message->from($email->sender_email, 'e:Presence')
                            ->to($user->email, $user->firstname . ' ' . $user->lastname)
                            ->replyTo(env('RETURN_PATH_MAIL'))
                            ->returnPath(env('RETURN_PATH_MAIL'))
                            ->subject($email->title);
                    });
                }



                $user->roles()->detach($current_role->id);
                $user->roles()->attach($update_role->id);
            }
        }

        if (!isset($input['status']))
            $input['status'] = 0;


        //Notify user that his account has been disabled

        if ($user->status == 1 && $input['status'] == 0) {

            $email = Email::where('name', 'userDisabled')->first();
            $parameters = ['contact_url' => URL::to("contact")];

            Mail::send('emails.account_disabled', $parameters, function ($message) use ($user, $email) {
                $message->from($email->sender_email, 'e:Presence')
                    ->to($user->email)
                    ->cc(env('SUPPORT_MAIL'), 'e:Presence')
                    ->replyTo(env('SUPPORT_MAIL'), 'e:Presence')
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });
        }


        //End role management


        // Create new password

        if ((isset($input['new_password']) && intval($input['new_password']) == 1) || (isset($input['SendUserEmail']) && intval($input['SendUserEmail']) == 1)) {
            $password = str_random(15);
            $user->createNewPassword($password);

            $email = Email::where('name', 'userAccountEnable')->first();
            $parameters = array('body' => $email->body, 'user' => $user, 'password' => $password, 'login_url' => URL::to("auth/login"), 'account_url' => URL::to("account"));


            Mail::send('emails.enable_account_local', $parameters, function ($message) use ($user, $email) {
                $message->from($email->sender_email, 'e:Presence')
                    ->to($user->email)
                    ->replyTo(Auth::user()->email, Auth::user()->firstname . ' ' . Auth::user()->lastname)
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });

            $message = trans('controllers.connectionEmailSent');
        }


        //Handle status

        if (!isset($input['status']))
            $input['status'] = 0;


        if (isset($input['institution_id'])) {
            $user->institutions()->detach($institution->id);

            if ($input['institution_id'] == "other") {
                $institution = Institution::where('slug', 'other')->first();
                $input['institution_id'] = $institution->id;
            }

            if ($input['new_institution'])
                $custom_values['institution'] = $input['new_institution'];

            $user->institutions()->attach($input['institution_id']);
        }

        if (isset($input['department_id'])) {

            //Detaching current institution/department

            $user->departments()->detach($department->id);

            //Getting real id of other institution or department

            if ($input['department_id'] == "other" || empty($input['department_id']) || !isset($input['department_id']))
                $input['department_id'] = $institution->otherDepartment()->id;

            //Update Custom Values

            if ($input['new_department'])
                $custom_values['department'] = $input['new_department'];

            $user->departments()->attach($input['department_id']);
        }


        $input['custom_values'] = json_encode($custom_values);

        $user->update(array_except($input, ['password']));

        return back()->with('message', $message);
    }

    public function updateSsoUser(Requests\UpdateSsoAccountRequest $request, $id)
    {
        // State input values+


        $user = User::find($id);
        $auth_user = Auth::user();


        //Check if user that is editing has the correct permissions to do that

        if (!$auth_user->hasRole('SuperAdmin') && !$auth_user->hasRole('InstitutionAdministrator')) {
            abort(403);
        }

        if ($auth_user->hasRole('InstitutionAdministrator') && $user->institutions()->first()->id != $auth_user->institutions()->first()->id) {
            abort(403);
        }

        $input = $request->all();

        $input['name'] = $user->email;

        $institution = $user->institutions()->first();
        $department = $user->departments()->first();

        $message = trans('controllers.changesSaved');


        $custom_values['department'] = "";
        $custom_values['institution'] = "";

        //Role management

        $current_role = $user->roles()->first();


        if ($auth_user->hasRole('SuperAdmin') ) {

            $update_role = Role::where('name', $input['role'])->first();

            if ($current_role->name != $update_role->name) {

                //Notify admins about the role change

                $email = Email::where('name', 'userRoleUpdated')->first();

                $parameters = array('contact_url' => URL::to("support"),'new_role' =>  $update_role->label,'user'=>$user);


                if($user->status == 1){

                    Mail::send('emails.user_role_updated', $parameters, function ($message) use ($user, $email) {
                        $message->from($email->sender_email, 'e:Presence')
                            ->to($user->email, $user->firstname . ' ' . $user->lastname)
                            ->replyTo(env('RETURN_PATH_MAIL'))
                            ->returnPath(env('RETURN_PATH_MAIL'))
                            ->subject($email->title);
                    });
                }


                $user->roles()->detach($current_role->id);
                $user->roles()->attach($update_role->id);
            }
        }

        //End role management

        //Handle status

        if (!isset($input['status']))
            $input['status'] = 0;


        //Notify user that his account has been disabled

        if ($user->status == 1 && $input['status'] == 0) {

            $email = Email::where('name', 'userDisabled')->first();
            $parameters = ['contact_url' => URL::to("contact")];

            Mail::send('emails.account_disabled', $parameters, function ($message) use ($user, $email) {
                $message->from($email->sender_email, 'e:Presence')
                    ->to($user->email)
                    ->cc(env('SUPPORT_MAIL'), 'e:Presence')
                    ->replyTo(env('SUPPORT_MAIL'), 'e:Presence')
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });
        }


        if (isset($input['department_id'])) {

            //Detaching current institution/department

            $user->departments()->detach($department->id);

            //Update Custom Values

            if ($input['new_department'])
                $custom_values['department'] = $input['new_department'];

            $input['custom_values'] = json_encode($custom_values);

            if ($input['department_id'] == "other") {

                $new_department = Department::create(['title' => $input['new_department'], 'slug' => 'noID', 'institution_id' => $institution->id]);
                $input['department_id'] = $new_department->id;
                $custom_values['department'] = "";
            }

            $user->departments()->attach($input['department_id']);
        }

        $user->update(array_except($input, ['password']));

        return back()->with('message', $message);
    }


    public function changeStateToSso(Request $request)
    {

        $input = $request->all();

        // State input values+

        $user = User::find($input['user_id']);
        $auth_user = Auth::user();

        //Check if user that is editing has the correct permissions to do this

        if (!$auth_user->hasRole('SuperAdmin')  && !$auth_user->hasRole('InstitutionAdministrator')) {
            abort(403);
            return response()->json(['status' => 'error']);
        }

        if ($auth_user->hasRole('InstitutionAdministrator') && $user->institutions()->first()->id != $auth_user->institutions()->first()->id) {
            abort(403);
            return response()->json(['status' => 'error']);
        }

        if($user->state=="local") {

            $user->update(['state' => 'sso', 'activation_token' => str_random(15), 'confirmed' => 0]);

            $new_password = '';
            $login_url = URL::to("login/" . $user->activation_token);


            $email = Email::where('name', 'userChangeState')->first();
            $parameters = array('body' => $email->body, 'user' => $user, 'password' => $new_password, 'login_url' => $login_url, 'account_url' => URL::to("account"));

            if($user->status == 1){

                Mail::send('emails.changeStateToSso', $parameters, function ($message) use ($user, $email) {
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($user->email)
                        ->replyTo(Auth::user()->email, Auth::user()->firstname . ' ' . Auth::user()->lastname)
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });

            }


            $response['message'] = 'Ο χρήστης μετατράπηκε επιτυχώς σε SSO!';
            $response['status'] = 'success';
            $response['email'] = $user->email;

        }else{
            $response['message'] = 'Ο χρήστης είναι ήδη SSO!';
            $response['status'] = 'error';
            }


        return response()->json($response);
    }



    public function changeStateToLocal(Request $request)
    {


        $input = $request->all();

        // State input values+

        $user = User::find($input['user_id']);
        $auth_user = Auth::user();

        //Check if user that is editing has the correct permissions to do this

        if (!$auth_user->hasRole('SuperAdmin')  && !$auth_user->hasRole('InstitutionAdministrator')) {
            abort(403);
            return response()->json(['status' => 'error']);
        }

        if ($auth_user->hasRole('InstitutionAdministrator') && $user->institutions()->first()->id != $auth_user->institutions()->first()->id) {
            abort(403);
            return response()->json(['status' => 'error']);
        }


        if($user->state=="sso") {

            $user->update(['state' => 'local', 'activation_token' => str_random(15), 'confirmed' => 0]);
            $password = str_random(15);
            $user->createNewPassword($password);
            $email = Email::where('name', 'userAccountEnable')->first();
            $parameters = array('body' => $email->body, 'user' => $user, 'password' => $password, 'login_url' => URL::to("auth/login"), 'account_url' => URL::to("account"));

            if($user->status == 1){

                Mail::send('emails.enable_account_local', $parameters, function ($message) use ($user, $email) {
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($user->email)
                        ->replyTo(Auth::user()->email, Auth::user()->firstname . ' ' . Auth::user()->lastname)
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }


            $message = 'Ο χρήστης μετατράπηκε επιτυχώς σε Local!';
            $status = 'success';

        }else{
            $status = 'error';
            $message = 'Ο χρήστης είναι ήδη Local!';
        }


        return response()->json(['status' => $status, 'message' => $message]);
    }


    public function store_new_sso_user(Request $request)
    {
        $password = str_random(15);
        $input = $request->all();

        $input['password'] = bcrypt($password);
        $input['name'] = $input['email'];
        $input['status'] = 1;
        $input['confirmed'] = 1;
        $input['state'] = 'sso';
        $input['role'] = 'EndUser';
        $input['accepted_terms'] = Carbon::now()->toDateTimeString();

        $mergedAccount = false;

        if (!isset($input['extra_sso_email_1'])) {
            $input['extra_sso_email_1'] = null;
        }

        if (!isset($input['extra_sso_email_2'])) {
            $input['extra_sso_email_2'] = null;
        }


        $validator = Validator::make($input, [
            'lastname' => 'required',
            'firstname' => 'required',
            'email' => "required|email|unique:users,email,NULL,id,users.state,sso,users.confirmed,1",
            'extra_sso_email_1' => "nullable|email|unique:users,email,NULL,id,users.state,sso,users.confirmed,1",
            'extra_sso_email_2' => "nullable|email|unique:users,email,NULL,id,users.state,sso,users.confirmed,1",
            'institution_id' => 'required',
            'department_id'=>'required',
            'new_department' => 'required_if:department_id,other',
            'new_institution' => 'required_if:institution_id,other',
            'accept_terms_input' => 'required',
            'privacy_policy_input' => 'required',


        ], ['lastname.required' => trans('requests.lastnameRequired'),
            'firstname.required' => trans('requests.firstnameRequired'),
            'email.required' => trans('requests.emailRequired'),
            'email.unique' => trans('requests.emailNotUnique'),
            'email.email' => trans('requests.emailInvalid'),
            'institution_id.required' => trans('requests.institutionRequired'),
            'department_id.required' => trans('requests.departmentRequired'),
            'new_department.required_if' => trans('requests.newDepartmentRequired'),
            'new_institution.required_if' => trans('requests.newInstitutionRequired'),
            'accept_terms_input.required' => trans('site.mustAcceptTermsActivate'),
            'privacy_policy_input.required' => trans('site.acceptPrivacyPolicyActivate'),
       ]);


        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $errors = array();

        if (User::whereIn('email', [$input['email'], $input['extra_sso_email_1'], $input['extra_sso_email_2']])->count() > 1) {
            $error = trans('requests.emailProblem');
            return back()->withErrors($error)->withInput();
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        //Delete all other emails using this email address

        ExtraEmail::whereIn('email', [$input['email'], $input['extra_sso_email_1'], $input['extra_sso_email_2']])->delete();

        //Merge new user that came with unconfirmed

        if (User::whereIn('email', [$input['email'], $input['extra_sso_email_1'], $input['extra_sso_email_2']])->count() == 1) {

            $mergedAccount = true;

            //Update old account's details

            $user = User::whereIn('email', [$input['email'], $input['extra_sso_email_1'], $input['extra_sso_email_2']])->first();
            $user->update($input);
        }

        //Normal creating process of sso user

        if ($mergedAccount == false) {
            $user = User::create($input);

            // Assign role to user
            $user->assignRole("EndUser");
        }

        //Update user institutions & departments

        $institution = Institution::findOrFail($input['institution_id']);
        $user->institutions()->sync([$input['institution_id']]);

        //Handle department

        if (!empty($input['new_department']) && $input['department_id'] == "other") {
            $new_department = Department::create(['title' => $input['new_department'], 'slug' => 'noID', 'institution_id' => $institution->id]);
            $input['department_id'] = $new_department->id;
        }

        $user->departments()->sync($input['department_id']);


        //Add extra emails to account

        if ($input['extra_sso_email_1'] != null) {
            $extraMail = new ExtraEmail;
            $extraMail->user_id = $user->id;
            $extraMail->email = $input['extra_sso_email_1'];
            $extraMail->confirmed = true;
            $extraMail->type = 'sso';
            $extraMail->save();
        }


        if ($input['extra_sso_email_2'] != null) {
            $extraMail = new ExtraEmail;
            $extraMail->user_id = $user->id;
            $extraMail->email = $input['extra_sso_email_2'];
            $extraMail->confirmed = true;
            $extraMail->type = 'sso';
            $extraMail->save();
        }

        //If we merged the account with unconfirmed account we should create the join urls for the conferences

        if($mergedAccount)
            $user->create_join_urls();

        Auth::loginUsingId($user->id);

        return redirect('/');
    }


    public function send_email_confirmation_link(Request $request){

        $input = $request->all();
        $user =  Auth::user();

        $validator = Validator::make($request->all(), [
            'email' => "required|email|unique:users,email,".$user->id.",id|unique:users_extra_emails,email,NULL,id,confirmed,1",
        ], [
            'email.required' => trans('requests.emailRequired'),
            'email.unique' => trans('requests.emailNotUniqueNewSSO'),
            'email.email' => trans('requests.emailInvalid'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->email = $input['email'];
        $user->name = $input['email'];

        $user->activation_token =  str_random(15);
        $user->update();

        $email = Email::where('name', 'ssoUserEmailConfirm')->first();
        $login_url = URL::to("confirm_sso_email/" . $user->activation_token);


        $parameters = array('user' => $user,'login_url' => $login_url, 'account_url' => URL::to("account"));

        Mail::send('emails.confirm_sso_email', $parameters, function ($message) use ($user, $email) {
            $message->from($email->sender_email, 'e:Presence')
                ->to($user->email)
                ->replyTo($email->sender_email, 'e:Presence')
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        return back();
      }


    public function send_email_confirmation_link_create_user(Request $request)
    {

        $input = $request->all();

        $validator = Validator::make($request->all(), [
            'email' => "required|email|unique:users,email,NULL,id|unique:users_extra_emails,email,NULL,id,confirmed,1",
            'persistent_id'=>"required|unique:users,persistent_id",
        ], [
            'email.required' => trans('requests.emailRequired'),
            'email.unique' => trans('requests.emailNotUniqueNewSSO'),
            'email.email' => trans('requests.emailInvalid'),
            'persistent_id.unique' => trans('requests.persistent_id_confirmed'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $user = new User;

        $user->lastname = $input['lastname'];
        $user->firstname = $input['firstname'];
        $user->email = $input['email'];
        $user->name = $input['email'];
        $user->telephone = $input['telephone'];
        $user->password = str_random(15);
        $user->confirmed = false;
        $user->confirmation_state = "pending_email";
        $user->persistent_id = $input['persistent_id'];
        $user->state = 'sso';
        $user->status = true;
        $user->activation_token =  str_random(15);
        $user->save();

        // Assign role to user
        $user->assignRole("EndUser");

        $user->institutions()->attach($input['institution_id']);

        $email = Email::where('name', 'ssoUserEmailConfirm')->first();

        $login_url = URL::to("confirm_sso_email/" . $user->activation_token);

        $user_email = $input['email'];

        $parameters = array('user' => $user,'login_url' => $login_url, 'account_url' => URL::to("account"));

        Mail::send('emails.confirm_sso_email', $parameters, function ($message) use ($user_email, $email) {
            $message->from($email->sender_email, 'e:Presence')
                ->to($user_email)
                ->replyTo($email->sender_email, 'e:Presence')
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

       Auth::login($user);

       session()->forget("emails");

       return redirect("account_activation");
    }

    public function confirm_sso_email($token){

        $user = User::where('activation_token',$token)->first();

        $user->confirmation_state = "custom_email_confirmed";
        $user->update();

        Auth::login($user);

        session()->put('confirmed_sso_email',$user->email);

        ExtraEmail::where('email',$user->email)->where('confirmed', 0)->delete();

        return redirect('account_activation')->with('message', trans('users.emailConfirmed'));;
    }


    public function resend_activation_email(Request $request){

        $input = $request->all();
        $user = User::findOrFail($input['user_id']);

         $password = str_random(15);
          $user->createNewPassword($password);

        $login_url = URL::to("auth/login");
        $email = Email::where('name', 'userAccountEnable')->first();
        $view = 'emails.enable_account_local';

        if ($user->state == 'sso') {
            $login_url = URL::to("login/" . $user->activation_token);
            $email = Email::where('name', 'userAccountEnableSso')->first();
            $view = 'emails.enable_account_sso';
        }

        $creator = $user->creator;

        if($creator){
            $reply_to['email'] = $creator->email;
            $reply_to['full_name'] = $creator->firstname . ' ' . $creator->lastname;
        }else{
            $reply_to['email'] = env('SUPPORT_MAIL');
            $reply_to['full_name'] = 'e:Presence';
        }

        $parameters = array('body' => $email->body, 'user' => $user, 'password' => $password, 'login_url' => $login_url, 'account_url' => URL::to("account"));
        Mail::send($view, $parameters, function ($message) use ($user, $email, $reply_to) {
            $message->from($email->sender_email, 'e:Presence')
                ->to($user->email)
                ->replyTo($reply_to['email'],$reply_to['full_name'])
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        $message = trans('controllers.connectionEmailSent');
        $status = "success";


        return response()->json(['status' => $status, 'message' => $message]);
    }


    public function resend_local_user_activation_email_token($token){

        $user = User::where('activation_token',$token)->where('confirmed',0)->where('state','local')->first();

        if(isset($user->id)){
            $login_url = URL::to("auth/login");
            $email = Email::where('name', 'userAccountEnable')->first();
            $password = str_random(15);
            $user->createNewPassword($password);
            $creator = $user->creator;

            $parameters = array('body' => $email->body, 'user' => $user, 'password' => $password, 'login_url' => $login_url, 'account_url' => URL::to("account"));
            Mail::send('emails.enable_account_local', $parameters, function ($message) use ($user, $email, $creator) {
                $message->from($email->sender_email, 'e:Presence')
                    ->to($user->email)
                    ->replyTo($creator->email, $creator->firstname . ' ' . $creator->lastname)
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });

            $user->update(['activation_token'=>'']);

            return redirect('/message')->with('message',trans('admin.emailSent'));

        }else{
            return redirect('/message')->with('error',trans('requests.tokenInvalidOrUsed'));
        }
    }

}
