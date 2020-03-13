<?php

namespace App\Http\Controllers;

use Auth;
use Gate;
use Mail;
use URL;
use App\Conference;
use Carbon\Carbon;
use App\Statistics;
use App\User;
use App\Email;
use SoapClient;
use Log;
use App\Http\Requests;
use Input;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class EmailsController extends Controller
{
    public function contact_email(Requests\CreateContactMailRequest $request)
	{

		$input = $request->all();
		$text = htmlentities($input['text'], ENT_QUOTES, 'UTF-8');
		
	    Mail::send('emails.contact_form', ['fullname' => $input['fullname'], 'email' => $input['email'], 'text' => $text,'user_agent'=>$request->header('User-Agent')], function ($message) use ($input){
			$message->from($input['email'], $input['fullname'])
					->sender(env('SUPPORT_MAIL'), config('mail.from.name'))
					->replyTo($input['email'], $input['fullname'])
					->to(env('SUPPORT_MAIL'))
					->subject('Contact Form Submission');
		});

		$response = trans('controllers.yourMessageSent');

		return back()->with('status',$response);
	}
	
	public function sendEmailToCoordinators(Request $request)
	{
		$input = $request->all();
		
		$email_errors = array();
		
		if($input['title'] == null){
			$email_errors [] = trans('controllers.titleRequired');
		}if($input['text'] == '<p><br></p>'){
			$email_errors [] = trans('controllers.messageBodyRequired');
		}
		
		if(!empty($email_errors)){
				return redirect('/administrators')->with('email_errors', $email_errors);
		}

		$roles_check = ['DepartmentAdministrator', 'InstitutionAdministrator'];

		if(Auth::user()->hasRole('SuperAdmin')){
			$coordinators = User::whereHas('roles', function($q) use($roles_check) {$q->whereIn('name',$roles_check); })->whereDoesntHave('applications',function($query) {
                $query->where('app_state','new');
            })->where('status',1)->get();

//            ->whereIn('application', ['none','accepted'])

		}
		elseif(Auth::user()->hasRole('InstitutionAdministrator')) {

			$inst = Auth::user()->institutions()->first();
			$coordinators = User::whereHas('roles', function($q) {$q->where('name','DepartmentAdministrator');})-> whereHas('institutions', function($query) use($inst) {$query->where('id', $inst->id);})
                ->whereDoesntHave('applications',function($query) {
                    $query->where('app_state','new');
                })->where('status',1)->get();
		}
		else
		{
			$coordinators= null;
		}

			$parameters = ['title' => $input['title'], 'body' => $input['text']];
			$email = Email::where('name', 'toAllCoordinators')->first();
		
		foreach($coordinators as $coordinator) {
			$parameters['coordinator'] = $coordinator;
			$parameters['sender'] = Auth::user();
			Mail::send('emails.coordinators_mail', $parameters, function ($message) use ($coordinator, $email, $input){ 
				$message->from($email->sender_email, config('mail.from.name'))
						->replyTo(Auth::user()->email, Auth::user()->firstname.' '.Auth::user()->lastname)
						->returnPath(env('RETURN_PATH_MAIL'))
						->to($coordinator->email)
						->subject($input['title']);
			});

		}
		
		$response = trans('controllers.yourMessageSent');

		return redirect('administrators')->with('message',$response);
	}
	
	public function conference_settings(){
		
		$email = Email::where('name', 'conferenceInvitation')->first();
		
		return view('conferences.settings', compact('email'));
		
	}

}
