<?php

namespace App\Http\Controllers;

use App\Role;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Hash;
use File;
use Log;
use App\Email;
use Validator;
use App\Conference;
use App\Institution;
use App\Department;
use Carbon\Carbon;
use App\ExtraEmail;
use App\Application;
use App\User;
use DB;
use Mail;


class AccountController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['accountActivation', 'localAccountActivation', 'ssoAccountActivation']]);
    }


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
                    $message->from($email->sender_email, 'e:Presence')
                        ->to($coordinator->email)
                        ->replyTo(env('SUPPORT_MAIL'), 'e:Presence')
                        ->returnPath(env('RETURN_PATH_MAIL'))
                        ->subject($email->title);
                });
            }

            $conf->detachParticipant($user->id);
        }

        if ($user->participantInConferences()->count() > 0 || $user->conferenceAdmin()->count() > 0) {

            $user->name = "Deleted";
            $user->email = "Deleted-" . $user->id . "@example.org";
            $user->password = str_random(15);
            $user->firstname = "Deleted";
            $user->lastname = "Deleted";
            $user->telephone = null;
            $user->status = 0;
            $user->thumbnail = null;
            $user->persistent_id = null;
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

        $email = Email::where('name', 'userDeleted')->first();

        $parameters = array('deletedEmail' => $deleted_email);

        Mail::send('emails.user_deleted', $parameters, function ($message) use ($email) {
            $message->from($email->sender_email, 'e:Presence')
                ->to(env('SUPPORT_MAIL'))
                ->returnPath(env('RETURN_PATH_MAIL'))
                ->subject($email->title);
        });

        return redirect('/');
    }


    public function UpdateLocalAccount(Requests\UpdateLocalAccountRequest $request)
    {

        //Update account details method called by from account page by the user himself

        // State input values

        $input = $request->all();
        $user = Auth::user();
        $role = $user->roles()->first();

        $institution = $user->institutions()->first();
        $department = $user->departments()->first();

        $custom_values['institution'] = "";
        $custom_values['department'] = "";


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

        //Only EndUsers can change institution or department if they are local

        if ($role->name == "EndUser") {

            //Detaching current institution/department

            $user->institutions()->detach($institution->id);
            $user->departments()->detach($department->id);


            //Getting real id of other institution or department


            if ($input['institution_id'] == "other") {
                $institution = Institution::where('slug', 'other')->first();
                $input['institution_id'] = $institution->id;
            }


            if ((isset($input['department_id']) && ($input['department_id'] == "other" || $input['institution_id'] == "other")) || !isset($input['department_id']))
                $input['department_id'] = $institution->otherDepartment()->id;


            //Update Custom Values

            $custom_values = ["institution" => "", "department" => ""];

            if ($input['new_institution'])
                $custom_values['institution'] = $input['new_institution'];

            if ($input['new_department'])
                $custom_values['department'] = $input['new_department'];


            $input['custom_values'] = json_encode($custom_values);

            $user->institutions()->attach($input['institution_id']);
            $user->departments()->attach($input['department_id']);
        }

        $user->update(array_except($input, ['password']));

        $message = trans('controllers.changesSaved');

        return back()->with('message', $message);
    }


    public function UpdateSsoAccount(Requests\UpdateSsoAccountRequest $request)
    {


        //Update account details method called by from account page by the user himself

        // State input values

        $input = $request->all();
        $user = Auth::user();

    
        Log::info("User:");
        Log::info(json_encode($user));
        Log::info(json_encode($user->institutions));
        Log::info(json_encode($user->departments));
        Log::info("Update sso account request (looking for missing department bug):");
        Log::info(json_encode($request->all()));


        $institution = $user->institutions()->first();
        $department = $user->departments()->first();

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

        if (isset($input['department_id'])) {

            $user->departments()->detach($department->id);

            if (!empty($input['new_department']) && $input['department_id'] == "other") {
                $new_department = Department::create(['title' => $input['new_department'], 'slug' => 'noID', 'institution_id' => $institution->id]);
                $input['department_id'] = $new_department->id;
            }

            $user->departments()->attach($input['department_id']);
        }

        $user->update(array_except($input, ['password']));

        $message = trans('controllers.changesSaved');

        return back()->with('message', $message);
    }


    public function accountActivation()
    {
        if (!Auth::check()) {
            abort(403);
        }

        $user = Auth::user();

        if ($user->confirmed == 1) {
            return redirect("/");
        }

        $institution = $user->institutions()->first();
        $department = $user->departments()->first();

        if ($user->state == "sso" && session()->has('emails')) {
            $emails = explode(';', session()->get('emails'));
            $invited_email_key = session()->get('invited_email_key');
        } elseif ($user->state == "sso" && session()->has('confirmed_sso_email')) {
            $emails [] = session()->get('confirmed_sso_email');
            $invited_email_key = false;
        } else {
            $emails = false;
            $invited_email_key = false;
        }


        return view('account_activation',

            [
                'user' => $user,
                'role' => $user->roles()->first(),
                'emails' => $emails,
                'invited_email_key' => $invited_email_key,
                'institution' => $institution,
                'department' => $department
            ]);
    }


    public function localAccountActivation(Requests\ActivateLocalAccountRequest $request)
    {

        //Update account details method called by from account page by the user himself

        // State input values

        $input = $request->all();

        $user = Auth::user();
        $role = $user->roles()->first();

        unset($input['accept_terms_input']);
        unset($input['privacy_policy_input']);


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

        //Only EndUsers can change institution or department if they are local

        if ($role->name == "EndUser") {

            //Getting real id of other institution or department

            $current_institution = $user->institutions()->first();

            $current_department = $user->departments()->first();

            if ($current_institution)
                $user->institutions()->detach($current_institution->id);

            if ($current_department)
                $user->departments()->detach($current_department->id);


            if ($input['institution_id'] == "other") {
                $institution = Institution::where('slug', 'other')->first();
                $input['institution_id'] = $institution->id;
            } else {
                $institution = Institution::find($input['institution_id']);
            }


            if ((isset($input['department_id']) && ($input['department_id'] == "other" || $input['institution_id'] == "other")) || !isset($input['department_id']))
                $input['department_id'] = $institution->otherDepartment()->id;


            //Update Custom Values

            $custom_values = ["institution" => "", "department" => ""];

            if ($input['new_institution'])
                $custom_values['institution'] = $input['new_institution'];

            if ($input['new_department'])
                $custom_values['department'] = $input['new_department'];


            $input['custom_values'] = json_encode($custom_values);


            $user->institutions()->attach($input['institution_id']);
            $user->departments()->attach($input['department_id']);
        }


        if(empty($user->accepted_terms))
            $input['accepted_terms'] = Carbon::now()->toDateTimeString();


        $input['confirmed'] = true;

        $user->update(array_except($input, ['password']));

        $user->create_join_urls();

        return redirect("account")->with('message', trans('controllers.epresenceAccountActivated'));
    }


    public function ssoAccountActivation(Requests\ActivateSsoAccountRequest $request)
    {

        //Update account details method called by from account page by the user himself

        // State input values

        $input = $request->all();

        unset($input['accept_terms_input']);
        unset($input['privacy_policy_input']);


        $user = Auth::user();


        $emails_array[] = $input['email'];


        if ($request->exists('extra_sso_email_1') && !empty($input['extra_sso_email_1']))
            $emails_array[] = $input['extra_sso_email_1'];

        if ($request->exists('extra_sso_email_2') && !empty($input['extra_sso_email_2']))
            $emails_array[] = $input['extra_sso_email_2'];


        //Delete extra emails using on of these emails

        ExtraEmail::whereIn('email', $emails_array)->delete();

        //Only local users is possible to be here since sso activation
        // blocks activating account with emails used as primary from an sso account

        $users_using_emails = User::whereIn('email', $emails_array)->where('id', '!=', $user->id)->get();

        foreach ($users_using_emails as $user_to_merge) {
            $user->merge_user($user_to_merge);
        }

        $institution = $user->institutions()->first();
        $department = $user->departments()->first();


        if ($department)
            $user->departments()->detach($department->id);

        // Handle user image (thumbnail)

        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $filename = time() . '-' . $thumbnail->getClientOriginalName();
            $thumbnail->move(public_path() . '/images/user_images', $filename);
            $input['thumbnail'] = $filename;
        }

        //Handle extra emails

        if ($request->exists('extra_sso_email_1') && !empty($input['extra_sso_email_1'])) {
            $extraMail = new ExtraEmail;
            $extraMail->user_id = $user->id;
            $extraMail->email = $input['extra_sso_email_1'];
            $extraMail->confirmed = 1;

            if ($request->exists('invited_email_key') && $input['invited_email_key'] == 1)
                $extraMail->type = 'custom';
            else
                $extraMail->type = 'sso';

            $extraMail->created_at = Carbon::now();
            $extraMail->updated_at = Carbon::now();
            $extraMail->save();
        }

        if ($request->exists('extra_sso_email_2') && !empty($input['extra_sso_email_2'])) {
            $extraMail = new ExtraEmail;
            $extraMail->user_id = $user->id;
            $extraMail->email = $input['extra_sso_email_2'];
            $extraMail->confirmed = 1;

            if ($request->exists('invited_email_key') && $input['invited_email_key'] == 2)
                $extraMail->type = 'custom';
            else
                $extraMail->type = 'sso';

            $extraMail->created_at = Carbon::now();
            $extraMail->updated_at = Carbon::now();
            $extraMail->save();
        }

        //Handle department

        if (!empty($input['new_department']) && $input['department_id'] == "other") {

            $new_department = Department::create(['title' => $input['new_department'], 'slug' => 'noID', 'institution_id' => $institution->id]);
            $input['department_id'] = $new_department->id;
        }

        $user->departments()->sync($input['department_id']);

        $input['confirmed'] = true;
        $input['activation_token'] = null;

        if(empty($user->accepted_terms))
            $input['accepted_terms'] = Carbon::now()->toDateTimeString();

        $user->update(array_except($input, ['password']));

        $user->create_join_urls();

        return redirect("account")->with('message', trans('controllers.epresenceAccountActivated'));
    }

    public function redirect_to_request_role_change()
    {

        session()->put("pop_role_change", 1);
        return redirect('/account');
    }


    public function accept_terms_ajax(){

        Auth::user()->update(['accepted_terms'=>Carbon::now()]);

        $response['status']='success';
        $response['message']='terms_accepted';

        return response()->json($response);
    }

}
