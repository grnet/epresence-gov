<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Institution;
use App\User;
use App\Department;
use App\Email;
use App\ExtraEmail;
use App\Role;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\View\View;


class UsersExtraController extends Controller
{

    /**
     * UsersExtraController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['confirm_sso_email']]);
    }


    /**
     * @param $id
     * @return Factory|View
     */
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


    /**
     * @param Requests\UpdateSsoAccountRequest $request
     * @param $id
     * @return RedirectResponse
     */
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
        $message = trans('controllers.changesSaved');

        //Role assignment
        $current_role = $user->roles()->first();
        if ($auth_user->hasRole('SuperAdmin') ) {
            $update_role = Role::where('name', $input['role'])->first();
            if ($current_role->name != $update_role->name) {
                //Notify admins about the role change
                $email = Email::where('name', 'userRoleUpdated')->first();
                $parameters = array('contact_url' => URL::to("support"),'new_role' =>  $update_role->label,'user'=>$user);
                if($user->status == 1){
                    Mail::send('emails.user_role_updated', $parameters, function ($message) use ($user, $email) {
                        $message->from($email->sender_email, config('mail.from.name'))
                            ->to($user->email, $user->firstname . ' ' . $user->lastname)
                            ->replyTo(env('RETURN_PATH_MAIL'))
                            ->returnPath(env('RETURN_PATH_MAIL'))
                            ->subject($email->title);
                    });
                }
                $user->roles()->sync([$update_role->id]);
            }
        }

        //Assign status
        if (!isset($input['status']))
            $input['status'] = 0;

        //Notify user that his account has been disabled

        if ($user->status == 1 && $input['status'] == 0) {

            $email = Email::where('name', 'userDisabled')->first();
            $parameters = ['contact_url' => URL::to("contact")];

            Mail::send('emails.account_disabled', $parameters, function ($message) use ($user, $email) {
                $message->from($email->sender_email, config('mail.from.name'))
                    ->to($user->email)
                    ->cc(env('SUPPORT_MAIL'), config('mail.from.name'))
                    ->replyTo(env('SUPPORT_MAIL'), config('mail.from.name'))
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });
        }

        if($auth_user->hasRole('SuperAdmin')){
            $user->institutions()->sync([$input['institution_id']]);
        }else{
            $input['institution_id'] = $user->institutions()->first()->id;
        }

       if ($input['department_id'] == "other") {
           $new_department = Department::create(['title' => $input['new_department'],'institution_id' => $input['institution_id']]);
           $input['department_id'] = $new_department->id;
        }
        $user->departments()->sync([$input['department_id']]);
        $user->update(array_except($input,['password']));
        return back()->with('message', $message);
    }



    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
            $reply_to['full_name'] = config('mail.from.name');
        }

        $parameters = array('body' => $email->body, 'user' => $user, 'password' => $password, 'login_url' => $login_url, 'account_url' => URL::to("account"));
        Mail::send($view, $parameters, function ($message) use ($user, $email, $reply_to) {
            $message->from($email->sender_email, config('mail.from.name'))
                ->to($user->email)
                ->replyTo($reply_to['email'],$reply_to['full_name'])
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        $message = trans('controllers.connectionEmailSent');
        $status = "success";


        return response()->json(['status' => $status, 'message' => $message]);
    }
}
