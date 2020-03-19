<?php

namespace App\Http\Controllers;

use App\Institution;
use App\Role;
use App\Http\Requests;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Email;
use App\Department;
use Carbon\Carbon;
use App\ExtraEmail;
use App\Application;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;


class AccountController extends Controller
{

    /**
     * AccountController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['accountActivation', 'localAccountActivation', 'ssoAccountActivation']]);
    }


    /**
     * @return Factory|View
     */
    public function showAccount()
    {
        $user = Auth::user();
        $extra_emails['sso'] = $user->extra_emails_sso()->toArray();
        $extra_emails['custom'] = $user->extra_emails_custom()->toArray();
        $institution = $user->institutions()->first();
        $department = $user->departments()->first();
        $role = $user->roles()->first();
        $canBeDeleted = $user->HasFutureAdminConferences();
        $canRequestRoleChange = Application::where('user_id', $user->id)->where('app_state', 'new')->count() > 0 || (!$user->hasRole('DepartmentAdministrator') && !$user->hasRole('EndUser')) ? false : true;
        if(session()->has("pop_role_change")){
            $pop_role_change = true;
            session()->forget("pop_role_change");
        }
        else{
            $pop_role_change = false;
        }
        return view('account',
            [
                'user' => $user,
                'canBeDeleted' => !$canBeDeleted,
                'extra_emails' => $extra_emails,
                'institution' => $institution,
                'department' => $department,
                'role' => $role,
                'canRequestRoleChange' => $canRequestRoleChange,
                'pop_role_change'=>$pop_role_change,
            ]);
    }


    /**
     * @return Factory|View
     */
    public function showManageEmails(){
       $user = Auth::user();
       if($user->state=="sso") {
           $primary_email = $user->email;
           $extra_emails['sso'] = $user->extra_emails_sso()->toArray();
           $extra_emails['custom'] = $user->extra_emails_custom()->toArray();
           $slots_remaining = 3 - (count($extra_emails['sso']) + count($extra_emails['custom']));
           return view('manage_account_emails',
               [
                   'user' => $user,
                   'extra_emails' => $extra_emails,
                   'primary_email' => $primary_email,
                   'slots_remaining' => $slots_remaining
               ]);
       }else{
           abort(403);
       }
    }


    /**
     * @param Requests\DeleteMyAccountRequest $request
     * @return RedirectResponse|Redirector
     */
    public function delete_anonymize(Requests\DeleteMyAccountRequest $request)
    {
        $input = $request->all();
        $user = Auth::user();
        if ($input['delete_account_confirmation_email'] !== $user->email) {
            $errors['confirmation_email_not_matched'] = trans('requests.confirmationMailNotMatched');
            return back()->withErrors($errors);
        }
        session()->flash('account_deleted_error',trans('account.account_deleted'));
        Auth::logout();
        $deleted_email = $user->email;
        $future_conferences = $user->futureConferences();
        foreach ($future_conferences as $conf) {
            $coordinator = User::find($conf->user_id);
            $parameters['conference'] = $conf;
            $parameters['deleted_user'] = $user;
            $email = Email::where('name', 'participantDeletedCoordinatorsSelf')->first();

            if($coordinator->status == 1){

                Mail::send('emails.conference_participantDeletedCoordinatorsSelf', $parameters, function ($message) use ($coordinator, $email) {
                    $message->from($email->sender_email, config('mail.from.name'))
                        ->to($coordinator->email)
                        ->replyTo(env('SUPPORT_MAIL'), config('mail.from.name'))
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }

            $conf->detachParticipant($user->id);
        }

        if ($user->participantInConferences()->count() > 0 || $user->conferenceAdmin()->count() > 0) {
            $user->email = "Deleted-" . $user->id . "@example.org";
            $user->password = str_random(15);
            $user->firstname = "Deleted";
            $user->lastname = "Deleted";
            $user->telephone = null;
            $user->status = 0;
            $user->thumbnail = null;
            $user->tax_id = null;
            $user->activation_token = null;
            $user->confirmation_code = null;
            $user->remember_token = null;
            $user->deleted = true;
            $user->update();
            ExtraEmail::where("user_id",$user->id)->delete();

            //Remove current role & attach end user role

            $current_role = $user->roles()->first();

            $end_user_role = Role::where('name', 'EndUser')->first();

            $user->roles()->detach($current_role->id);
            $user->roles()->attach($end_user_role->id);
        } else {
            $user->delete();
        }

//        $email = Email::where('name', 'userDeleted')->first();
//        $parameters = array('deletedEmail' => $deleted_email);
//
//        Mail::send('emails.user_deleted', $parameters, function ($message) use ($email) {
//            $message->from($email->sender_email, config('mail.from.name'))
//                ->to(env('SUPPORT_MAIL'))
//                ->returnPath(env('RETURN_PATH_MAIL'))
//                ->subject($email->title);
//        });

        return redirect('/');
    }

    /**
     * @param Requests\UpdateLocalAccountRequest $request
     * @return RedirectResponse
     */
    public function UpdateLocalAccount(Requests\UpdateLocalAccountRequest $request)
    {
        //Update account details method called by from account page by the user himself
        // State input values

        $input = $request->all();
        $user = Auth::user();

        // Handle user image (thumbnail)
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $filename = time() . '-' . $thumbnail->getClientOriginalName();

            // Delete previous file
            if (!empty($user->thumbnail) && File::exists(public_path() . '/images/user_images/' . $user->thumbnail)) {
                File::delete(public_path() . '/images/user_images/' . $user->thumbnail);
            }

            $thumbnail->move(public_path() . '/images/user_images', $filename);
            $input['thumbnail'] = $filename;
        }

        //Handle password update

        if (!empty($input['password']) && !empty($input['current_password'])) {
            if (!Hash::check($input['current_password'], $user->password)) {
                $errors [] = trans('controllers.currentPasswordWrong');
                return back()->withErrors($errors);
            } else {
                $user->createNewPassword($input['password']);
            }
        }

        $user->update(array_except($input, ['password']));
        $message = trans('controllers.changesSaved');
        return back()->with('message', $message);
    }



    /**
     * @param Requests\UpdateSsoAccountRequest $request
     * @return RedirectResponse
     */
    public function UpdateSsoAccount(Requests\UpdateSsoAccountRequest $request)
    {
        //Update account details method called by from account page by the user himself
        // State input values
        $input = $request->all();
        $user = Auth::user();
        $institution = $user->institutions()->first();
        // Handle user image (thumbnail)
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $filename = time() . '-' . $thumbnail->getClientOriginalName();

            // Delete previous file
            if (!empty($user->thumbnail) && File::exists(public_path() . '/images/user_images/' . $user->thumbnail)) {
                File::delete(public_path() . '/images/user_images/' . $user->thumbnail);
            }

            $thumbnail->move(public_path() . '/images/user_images', $filename);
            $input['thumbnail'] = $filename;
        }

        //every sso user can change department except SuperAdmins

        if (isset($input['department_id']) && $user->hasRole('InstitutionAdministrator')) {
            if (!empty($input['new_department']) && $input['department_id'] == "other") {
                $new_department = Department::create(['title' => $input['new_department'], 'institution_id' => $institution->id]);
                $input['department_id'] = $new_department->id;
            }
            $user->departments()->sync([$input['department_id']]);
        }
        $user->update(array_except($input, ['password','fistname','lastname']));
        $message = trans('controllers.changesSaved');
        return back()->with('message', $message);
    }


    /**
     * @return Factory|RedirectResponse|Redirector|View
     */
    public function accountActivation()
    {
        if (!Auth::check()) {
            abort(403);
        }
        $user = Auth::user();
        if ($user->confirmed) {
            return redirect("/");
        }
        $institution = $user->institutions()->first();
        $department = $user->departments()->first();

        if(session()->has('matched_institution_ids')){
            $matchedInstitutionIds = explode(",",session()->get("matched_institution_ids"));
            $institutionsOptions = Institution::whereIn("id",$matchedInstitutionIds)->pluck('title', 'id')->toArray();


        }



        return view('account_activation',
            [
                'user' => $user,
                'role' => $user->roles()->first(),
                'institution' => $institution,
                'department' => $department
            ]);
    }

    /**
     * @param Requests\ActivateSsoAccountRequest $request
     * @return RedirectResponse
     */
    public function ssoAccountActivation(Requests\ActivateSsoAccountRequest $request)
    {
        $input = $request->except(['accept_terms_input','privacy_policy_input']);
        $user = Auth::user();
        $input['activation_token'] = null;
        $input['accepted_terms'] = Carbon::now()->toDateTimeString();
        $confirmation_code = str_random(15);
        $user->update(['email'=>$input['email'],'confirmation_code'=>$confirmation_code]);
        $email = Email::where('name', 'ssoUserEmailConfirm')->first();
        $login_url = URL::to("confirm_sso_email/" . $confirmation_code);
        $parameters = array('user' => $user,'login_url' => $login_url, 'account_url' => URL::to("account"));
        Mail::send('emails.confirm_sso_email', $parameters, function ($message) use ($user, $email) {
            $message->from($email->sender_email, config('mail.from.name'))
                ->to($user->email)
                ->replyTo($email->sender_email, config('mail.from.name'))
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });
        return back()->with('message', trans('account.confirmation_email_sent'));
    }

    /**
     * @param $confirmation_code
     * @return RedirectResponse
     */
    public function confirm_sso_email($confirmation_code){
        $user = User::where('confirmation_code',$confirmation_code)->first();
        if($user){
            $user->update(['confirmation_code'=>null,'confirmed'=>true]);
            Auth::login($user);
            return redirect()->route('account-activation')->with('message', trans('users.emailConfirmed'));
        }else{
            abort(403);
        }
    }

    /**
     * @return RedirectResponse|Redirector
     */
    public function redirect_to_request_role_change()
    {
        session()->put("pop_role_change", 1);
        return redirect('/account');
    }


    /**
     * @return JsonResponse
     */
    public function accept_terms_ajax(){
        Auth::user()->update(['accepted_terms'=>Carbon::now()]);
        $response['status']='success';
        $response['message']='terms_accepted';
        return response()->json($response);
    }

}
