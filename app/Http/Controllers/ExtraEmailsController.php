<?php

namespace App\Http\Controllers;

use App\Email;
use App\ExtraEmail;
use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Auth;
use App\Institution;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Log;
use App\Domain;
use DB;
use App\Conference;
use Validator;

class ExtraEmailsController extends Controller
{


    public function addExtraMail(Request $request)
    {
        $newMail = $request->new_extra_email;
        $input['email'] = $newMail;

        $validator = Validator::make($input, [
            'email' => "required|email",

        ], [
            'email.email' => trans('requests.emailInvalid'),
            'email.required' => trans('requests.emailRequired'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }


        if (ExtraEmail::where('email', $newMail)->where('user_id', '!=', Auth::user()->id)->where('confirmed', 1)->count() > 0 || User::where('id', '!=', Auth::user()->id)->where('email', $newMail)->where('state','sso')->where('confirmed', 1)->count() > 0) {
            return back()->with('error', trans('requests.emailNotUnique'));

        } else if (ExtraEmail::where('user_id', Auth::user()->id)->where('email', $newMail)->where('confirmed', 1)->count() > 0 || User::where('id', Auth::user()->id)->where('email', $newMail)->where('confirmed', 1)->count() > 0) {
            return back()->with('error', trans('requests.emailOwnUsed'));

        } else if (ExtraEmail::where('email', $newMail)->where('user_id', Auth::user()->id)->where('confirmed', 0)->count() > 0) {
            return back()->with('error', trans('requests.emailNotUniquePending'));

        } else {
            Auth::user()->CreateNewExtraMail_sendConfirmationLink($newMail);
            return back()->with('status','success');
        }

    }

    public function ConfirmExtraEmail($token)
    {
        $mail = ExtraEmail::where('activation_token', $token)->first();
        if($mail) {
            $mail_already_confirmed_by_other_extra_Mail = ExtraEmail::where('email', $mail->email)->where('confirmed', 1)->count();
            $mail_already_confirmed_by_other_user = User::where('email', $mail->email)->where('confirmed', 1)->where('state', 'sso')->count();
            $ExistingUser = User::find($mail->user_id);

            if ($mail_already_confirmed_by_other_extra_Mail == 0 && $mail_already_confirmed_by_other_user == 0 && isset($ExistingUser->id)) {

                $mail->confirmed = 1;
                $mail->update();

                ExtraEmail::where('email', $mail->email)->where('confirmed', 0)->delete();
                $accounts_to_be_merged = User::where('email', $mail->email)->where('state', 'local')->get();

                foreach ($accounts_to_be_merged as $account) {
                    $ExistingUser->merge_user($account->id,false);
                }

                return redirect('message')->with('message', trans('users.emailConfirmed'));
            } else {
                return redirect('message')->with('error', trans('users.emailAlreadyConfirmed'));
            }
        }else{
            return redirect('message')->with('error', trans('users.emailAlreadyConfirmed'));
        }
    }



    public function deleteExtraMail(Request $request)
    {
        $extraMailId = $request->id;
        $mail = ExtraEmail::find($extraMailId);
        $response = new Response;

        if ($mail) {
            $mail->delete();
            $response->status = 'success';
            $response->message = trans('users.extraEmailDeleted');
        } else {

            $response->status = 'error';
            $response->message = 'email_not_found';
        }
        return json_encode($response);
    }

    public function resend_extra_email_confirmation(Request $request){
        $extraMailId = $request->id;
        $extra_mail = ExtraEmail::find($extraMailId);
        $response = new Response;

        if ($extra_mail && !$extra_mail->confirmed && $extra_mail->user->status == 1) {

            //Generate new token

            $new_activation_token = str_random(16);
            $extra_mail->activation_token = $new_activation_token;
            $extra_mail->update();

            $email = Email::where("name", "extraEmailConfirmation")->first();

            $parameters['activation_link'] = URL::to("email_activation") . '/' . $new_activation_token;

            $email_address = $extra_mail->email;

            Mail::send('emails.ExtraEmailConfirmation', $parameters, function ($message) use ($email_address, $email) {
                $message->from($email->sender_email, env('MAIL_FROM_NAME'))
                    ->to($email_address)
                    ->replyTo(env('MAIL_FROM_ADDRESS'))
                    ->returnPath(env('RETURN_PATH_MAIL'))
                    ->subject($email->title);
            });


            $response->status = 'success';
            $response->message = trans('account.extra_email_confirmation_email_resent');
        } else {

            $response->status = 'error';
            $response->message = 'email_not_found';
        }

        return json_encode($response);
    }


    public function makePrimary(Request $request)
    {

        $extraMailId = $request->id;
        $mail = ExtraEmail::find($extraMailId);
        $response = new Response;

        if ($mail) {

            $user = $mail->user;

            $current_primary = $user->email;

            $user->email = $mail->email;
            $user->update();

            $mail->email = $current_primary;
            $mail->type = 'sso';
            $mail->update();

            $response->status = 'success';
            $response->message = 'email converted to primary';

        } else {

            $response->status = 'error';
            $response->message = 'email_not_found';
        }


        return json_encode($response);
    }

    public function SyncDomains(Request $request)
    {
        $domainsList = Institution::all()->pluck('url', 'id')->toArray();
        foreach ($domainsList as $key => $domain) {
            if ($domain != null) {
                $newdomain = new Domain;
                $newdomain->institution_id = $key;
                $newdomain->name = $domain;
                $newdomain->save();
            }
        }
    }

    //Extra emails management

    public function showManageEmailsFromAdmin($id){

        $auth_user = Auth::user();

        $user = User::find($id);

        if (!$auth_user->hasRole('SuperAdmin') || $user->state=='local') {
            abort(403);
        }

        $primary_email = $user->email;
        $extra_emails['sso'] = $user->extra_emails_sso()->toArray();
        $extra_emails['custom'] = $user->extra_emails_custom()->toArray();

        $slots_remaining =  3-(count($extra_emails['sso'])+count($extra_emails['custom']));

        return view('users.manage_sso_extra_emails',
            [
                'user' => $user,
                'extra_emails' => $extra_emails,
                'primary_email'=>$primary_email,
                'slots_remaining'=>$slots_remaining
            ]);
    }


    public function addExtraMailFromAdmin($id,Request $request)
    {
        $user = User::findOrFail($id);

        $newMail = $request->new_extra_email;
        $input['email'] = $newMail;

        $validator = Validator::make($input, [
            'email' => "required|email",
        ], [
            'email.email' => trans('requests.emailInvalid'),
            'email.required' => trans('requests.emailRequired'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (ExtraEmail::where('email', $newMail)->where('user_id', '!=', $user->id)->where('confirmed', 1)->count() > 0 || User::where('id', '!=', $user->id)->where('email', $newMail)->where('state','sso')->where('confirmed', 1)->count() > 0) {
            return back()->with('error', trans('requests.emailNotUnique'));

        } else if (ExtraEmail::where('user_id', $user->id)->where('email', $newMail)->where('confirmed', 1)->count() > 0 || User::where('id', $user->id)->where('email', $newMail)->where('confirmed', 1)->count() > 0) {
            return back()->with('error', trans('requests.emailOwnUsed'));

        } else if (ExtraEmail::where('email', $newMail)->where('user_id', $user->id)->where('confirmed', 0)->count() > 0) {
            return back()->with('error', trans('requests.emailNotUniquePending'));

        } else {
            $user->CreateNewExtraMail_sendConfirmationLink($newMail);
            return back()->with('message',trans('admin.extraEmailConfirmationMailSent'));
        }
    }
}
